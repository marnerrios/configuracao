<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappApi extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'whatsapp_api';
    protected $fillable = [
        'phone',
        'chatName',
        'tipoMensagem',
        'campanha',
        'respondido',
        'cpf',
        'dados_conta',
        'salario',
        'email',
        'link_documento',
        'link_documento',
        'link_endereco',
        'link_selfie',
        'link_dados_conta'
    ]; 
}
