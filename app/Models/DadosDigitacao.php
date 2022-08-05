<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DadosDigitacao extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'dados_digitacao';
    protected $fillable = [
        'phone',
        'cpf',
        'nb',
        'valor_limite',
        'valor_margem',
        'link_assinatura',
        'digitado'
    ]; 
}
