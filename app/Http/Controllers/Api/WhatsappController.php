<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InssService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller 
{
    private $service;
    public function __construct(WhatsappService $service)
    {
        $this->service = $service;
    }
    public function recebeMensagemBmg(Request $request)
    {
        $this->service->faz($request->all());
    }
    public function recebeMensagemPan(Request $request)
    {
        $this->service->faz($request->all(),'PAN');
    }
    public function enviaLink(Request $request)
    {
        $this->service->enviaLink($request->all());
    }
    public function enviaMensagem(Request $request)
    {
        //
    }
    public function calcSalarioBase($cpf)
    {
        return (new InssService)->calcMargemCartaoBeneficio($cpf);
    }

}
