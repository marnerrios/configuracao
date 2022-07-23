<?php

namespace App\Services;

use App\Models\WhatsappApi;
use App\Traits\Integracoes\ZApi;
use App\Traits\MyHelpers;
use App\Traits\Integracoes\Webhook;
use Illuminate\Foundation\Bus\DispatchesJobs;

class WhatsappService
{
    use DispatchesJobs, ZApi, Webhook;

    public function fazBmg($dadosWebhook)
    {
        $this->setMainUrl('BMG');
        $resposta = $this->respostaEnviar($dadosWebhook,'BMG');
        if (!$resposta){
            $this->sendText($dadosWebhook['phone'],'Olá! Caso deseje iniciar uma solicitação digite: #');
            return false;
        }
        $dadosGravar = ['chatName'=>$dadosWebhook['chatName'],'tipoMensagem'=>$resposta['proximaMensagem'],'respondido'=>1];
        if ($resposta['proximaMensagem'] == 'respostaGenerica') return false;
        if($resposta['proximaMensagem'] == 'saudacao'){
            $this->sendImage($dadosWebhook['phone'],$this->imagem('BMG'),$resposta['mensagem']['msg']);
            $this->sendButtonList($dadosWebhook['phone'],$this->mensagens('saudacaoBotao')['msg'],$resposta['mensagem']['botao']);
        } elseif($resposta['proximaMensagem'] == 'selfie'){
            $this->sendImage($dadosWebhook['phone'],$this->imagem('selfie'),$resposta['mensagem']['msg']);
        } elseif ($resposta['proximaMensagem'] == 'cpfOk'){
            $this->sendButtonList($dadosWebhook['phone'],$resposta['mensagem']['msg'],$resposta['mensagem']['botao']);
        } else {
            $this->sendText($dadosWebhook['phone'],$resposta['mensagem']['msg']);
        }
        foreach ($resposta['dadosAdicionais'] as $chave=>$valor){
            $dadosGravar[$chave] = $valor;
        }
        print_r($dadosGravar);
        return WhatsappApi::updateOrCreate(
            ['phone'=>$dadosWebhook['phone'],'campanha'=>'BMG'],
            $dadosGravar
        );
    }

    public function respostaEnviar($dadosWebhook,$banco='BMG')
    {
        $mensagemWh = $this->processaWebhook($dadosWebhook);
        if($mensagemWh){
            $dadosAdicionais = [];
            $mensagemAnterior = WhatsappApi::where('phone',$mensagemWh['phone'])->first();
            if($mensagemAnterior){ //com histórico no BD
                $proximaMensagem = $this->defineProximaMensagem($mensagemWh,$mensagemAnterior->tipoMensagem);
                if ($proximaMensagem != ''){
                    if ($proximaMensagem == 'saudacao'){
                       $mensagem=$this->mensagens('saudacao',['chatName'=>$mensagemWh['chatName'],'banco'=>$banco]);
                    } elseif ($proximaMensagem == 'cpfOk'){
                        // Consultar CPF e calcular margem
                        $dadosInss = (new InssService)->calcMargemCartaoBeneficio($mensagemWh['message']);
                        if ($dadosInss == false) $mensagem=$this->mensagens('cpfNaoEncontrado');
                        else {
                            $mensagem=$this->mensagens('cpfOk',['limiteCartao'=>$dadosInss['limiteCartao'],'70porcento'=>$dadosInss['70porcento'],'30porcento'=>$dadosInss['30porcento']]);
                            $dadosAdicionais = [
                                'cpf'=>$mensagemWh['message'],
                                'salario'=>$dadosInss['salario']
                            ];
                        }

                    } else {
                        $mensagem=$this->mensagens($proximaMensagem); 
                    }
                }

            } else { // sem histórico no BD
                if ($mensagemWh['tipoMensagem'] == 'primeira'){
                    $proximaMensagem = 'saudacao';
                    $mensagem=$this->mensagens('saudacao',['chatName'=>$mensagemWh['chatName'],'banco'=>$banco]);
                } else return false;
           }
           return [
            'proximaMensagem'=>$proximaMensagem,
            'mensagem'=>$mensagem,
            'dadosAdicionais'=>$dadosAdicionais
           ];
        }
        return false;
    }
    public function defineProximaMensagem ($mensagem,$mensagemAnterior)
    {
        if ($mensagem['tipoMensagem'] == 'primeira') return 'saudacao';
        if ($mensagem['tipoMensagem'] == 'botao') {
            if ($mensagem['message'] == 'InteresseSim') return 'cpf';
            if ($mensagem['message'] == 'InteresseNao') return 'semInteresse';
            if ($mensagem['message'] == 'ContratacaoSim') return 'imagemDoc';
            if ($mensagem['message'] == 'ContratacaoNao') return 'semInteresse';
        }
        if ($mensagem['tipoMensagem'] == 'imagem'){
            if($mensagemAnterior == 'imagemDoc') return 'imagemResidencia';
            if($mensagemAnterior == 'imagemResidencia') return 'dadosBancarios';
            if($mensagemAnterior == 'dadosBancarios') return 'email';
            //if($mensagemAnterior == 'email') return 'selfie';
        }
        
        if ($mensagem['tipoMensagem'] == 'cpf' && ($mensagemAnterior == 'cpf' || $mensagemAnterior == 'cpfOk' || $mensagemAnterior == 'cpfIncorreto')) return 'cpfOk';
        if ($mensagem['tipoMensagem'] == 'cpfIncorreto' && $mensagemAnterior == 'cpf') return 'cpfIncorreto';
        if ($mensagem['tipoMensagem'] == 'email' && $mensagemAnterior == 'email') return 'selfie';
        if ($mensagem['tipoMensagem'] == 'respostaGenerica'){
            if ($mensagemAnterior == 'cpf') return 'cpfIncorreto';
            if ($mensagemAnterior == 'finalizar') return 'respostaGenerica';
            return 'respostaGenerica';
        }
        return 'respostaGenerica';
    }
}