<?php

namespace App\Traits;

use Exception;

trait Webhook {

    public function processaWebhook($dados)
    {
      try {
        if (array_key_exists('text',$dados) && $dados['fromMe'] == false) { //Mensagem Texto
            
            return $this->textResponse($dados);  
        } 
        if (array_key_exists('buttonsResponseMessage',$dados) && $dados['fromMe'] == false){ //Resposta de botão

             return  $this->buttonResponse($dados);

        }
        if (array_key_exists('image',$dados) && $dados['fromMe'] == false){ //Resposta imagem

            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'respostaGenerica'
            ];
        }
        if (array_key_exists('error',$dados)) {
            /*
                Grava na log
                phone, status=error, messageId
            */ 
            return false;
        }
        if (array_key_exists('audio',$dados) && $dados['fromMe'] == false) { //Resposta audio
            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'respostaGenerica'
            ];
        }
      } catch (Exception $e){
          //erro recebimento/formato de webhook
          return false;
      } 
    }
    private function textResponse($dados)
    {
        $text = trim($dados['text']['message']);
        $olyNumbers = preg_replace("/[^0-9]/", "",$text);

        if (preg_match("/^[0-9]{3}.?[0-9]{3}.?[0-9]{3}-?[0-9]{2}/",$olyNumbers)){
            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'cpf',
                'message'=>$olyNumbers
            ];
        }
        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i",$text)){
            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'email',
                'message'=>''
            ];
        }
        if (preg_match("/(quero um[^^]+BMG)|(#)/i",$text)){
            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'primeira',
                'message'=>''
            ];
        }
        if (preg_match("/banco|conta/i",$text)){
            return [
                'chatName'=>$dados['chatName'],
                'phone'=>$dados['phone'],
                'tipoMensagem'=>'dadosbancarios',
                'message'=>''
            ];
        }
        return [
            'chatName'=>$dados['chatName'],
            'phone'=>$dados['phone'],
            'tipoMensagem'=>'respostaGenerica',
            'message'=>''
        ];
    }
    private function buttonResponse($dados)
    {
        return [
            'chatName'=>$dados['chatName'],
            'phone'=>$dados['phone'],
            'tipoMensagem'=>'botao',
            'message'=>$dados['buttonsResponseMessage']['buttonId']
        ];
    }
    private function mensagens($key,$infos=[])
    {
        $msg = [
            'saudacao'=>"Olá, {$infos['chatName']}! Seja bem-vindo (a) ao canal de atendimento do correspondente do banco BMG, para contratação do cartão benefício banco BMG.\n\n"
                    . "*Confira as vantagens do cartão benefício do banco BMG*\n\n"
                    . "+5% de margem *EXCLUSIVA*, saque de até 70% do limite, taxas reduzidas, compras nacionais e internacionais em lojas físicas e online, descontos em farmácias, seguro de vida grátis, assistência residencial com direito a chaveiro, eletricista e encanador, assistência remédio genérico grátis de até R$ 300,00 por mês em caso de atendimento de urgência e emergência, assistência funeral para até 5 familiares, compras nacionais e internacionais e seguro de vida grátis.\n\n"
                    . "Antes de iniciarmos me diga qual opção você deseja:\n\n",
            'semInteresse'=>"Que pena 😢, qualquer coisa estou por aqui, caso mude de ideia é só digitar #. Até logo!",
            'cpf'=>"Poderia me informar o seu CPF para continuarmos o atendimento. Exemplo: 02345378900",
            'cpfIncorreto'=>"Ops. CPF incorreto.\nPoderia me informar o seu CPF para continuarmos o atendimento. Exemplo: 02345378900",
            'cpfOk'=>"Cartão benefício INSS pré-aprovado no limite de R$ {$infos['limiteCartao']} sendo *70% (R$ {$infos['70porcento']})* disponível para saque em 84 meses e *30% (R$ {$infos['30porcento']})* disponível para compras.\n\n" 
                    . "Deseja prosseguir com a contratação e saque dos 70% do limite?",
            'imagemDoc'=>"OK! Agora precisaremos de alguns dados para seguir com a sua solicitação. Por favor, me envie uma foto de um documento de identidade, por exemplo, o RG ou a CNH, lembrando que o documento não pode estar no plástico, tem que ser bem nítido, alinhados, sem cortes e flash.",
            'imagemResidencia'=>"Agora vou precisar que me envie uma foto do comprovante de residência somente da parte que conste o seu nome e endereço ou escreva o endereço completo com o CEP, por favor.",
            'dadosBancarios'=>"Por favor me informe os dados bancários em nome do titular do benefício para depósito do valor, a mesma em que recebe o benefício.",
            'email'=>"Para finalizar informe um e-mail para recebimento da fatura.",
            'finalizar'=>"Perfeito! Sua proposta foi encaminhada para digitação, em breve você recebera um SMS com o link para assinatura do contrato é importante ficar atento no recebimento desse link, pois o contrato só é liberado para averbação após a assinatura.",
            'emailIncorreto'=>"Informe um e-mail válido para recebimento da fatura",
            'respostaGenerica'=>"Em breve você terá um retorno desta solicitação. Caso queira recomeçar o procedimento de contratação, digite #"
        ];

        $button = [
            'saudacao'=>[
                [
                    "id"=>"InteresseSim",
                    "label"=>"Solicitar cartão benefício INSS"
                ],
                [
                    "id"=>"InteresseNao",
                    "label"=>"Não tenho interesse"
                ]
            ],
            'cpfOk'=>[
                [
                    "id"=>"ContratacaoSim",
                    "label"=>"SIM"
                ],
                [
                    "id"=>"ContratacaoNao",
                    "label"=>"NÃO"
                ]
            ]
        ];
        return [
            'mensagem'=> array_key_exists($key,$msg) ? $msg[$key] : '',
            'botao'=> array_key_exists($key,$button) ? $button[$key] : []
        ];
    }
}