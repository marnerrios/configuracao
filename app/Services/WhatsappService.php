<?php

namespace App\Services;

use App\Models\WhatsappApi;
use App\Traits\Integracoes\ZApi;
use App\Traits\MyHelpers;
use App\Traits\Webhook;
use Illuminate\Foundation\Bus\DispatchesJobs;

class WhatsappService
{
    use DispatchesJobs, MyHelpers, ZApi, Webhook;

    public function respostaClienteBMG($dadosWebhook)
    {
        $mensagemWh = $this->processaWebhook($dadosWebhook);
        if($mensagemWh){
            $mensagemAnterior = WhatsappApi::where('phone',$mensagemWh['phone'])->first();
            if($mensagemAnterior){ //com histórico no BD
                $proximaMensagem = $this->defineProximaMensagem($mensagemWh,$mensagemAnterior->tipoMensagem);
                if ($proximaMensagem != ''){
                    if ($proximaMensagem == 'saudacao'){
                       $mensagem=$this->mensagens('saudacao',['chatName'=>$mensagemWh['chatName']]);
                    } elseif ($proximaMensagem == 'cpfOk'){
                       $mensagem=$this->mensagens('cpfOk',['limiteCartao'=>'1.000,00','70porcento'=>'70,00','30porcento'=>'30,00']);
                    } else {
                        $mensagem=$this->mensagens($proximaMensagem); 
                    }
                }

            } else { // sem histórico no BD
                if ($mensagemWh['tipoMensagem'] == 'primeira'){
                    $proximaMensagem = 'saudacao';
                    $mensagem=$this->mensagens('saudacao',['chatName'=>$mensagemWh['chatName']]);
                }
           }
           $this->envia([
                'phone'=>$mensagemWh['phone'],
                'chatName'=>$mensagemWh['chatName'],
                'tipoMensagem'=>$proximaMensagem,
                'mensagem'=>$mensagem
           ]);
        }
    }
    public function defineProximaMensagem ($mensagem,$mensagemAnterior)
    {
        if ($mensagem['tipoMensagem'] == 'primeira') return 'saudacao';
        if ($mensagem['tipoMensagem'] == 'botao') {
            if ($mensagem['mensagem'] == 'InteresseSim') return 'cpf';
            if ($mensagem['mensagem'] == 'InteresseNao') return 'semInteresse';
            if ($mensagem['mensagem'] == 'ContratacaoSim') return 'finalizar';
            if ($mensagem['mensagem'] == 'ContratacaoNao') return 'semInteresse';
        }
        if ($mensagem['tipoMensagem'] == 'cpf' && $mensagemAnterior == 'cpf') return 'cpfOk';
        if ($mensagem['tipoMensagem'] == 'respostaGenerica'){
            if ($mensagemAnterior == 'cpf') return 'cpfIncorreto';
            if ($mensagemAnterior == 'finalizar') return 'respostaGenerica';
            return '';
        }
        return '';
    }
}