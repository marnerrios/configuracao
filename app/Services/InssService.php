<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class InssService 
{
    
    private $tabelaIR = [
        '0.0'	=>1903.98,
        '7.5'	=>2826.65,
        '15.0'	=>3751.05,
        '22.5'	=>4664.68,
        '27.5'	=>999999.00
    ];
    private $salarioMinimo = 1212.00;
    private $nb;
    public function calcMargemCartaoBeneficio($cpf)
    {
       $salarioBase = $this->calcSalarioBase($cpf);
       if($salarioBase <= 0) return false;
       return [
        'limiteCartao'=>number_format($salarioBase * 1.375,2,',','.'), //MR * 5% * 27,5
        '70porcento'=>number_format($salarioBase * 0.9625,2,',','.'), //limite * 70%
        '30porcento'=>number_format($salarioBase * 0.4125,2,',','.'), //limite * 30%
        'limite'=>$salarioBase * 1.375, //não formatado
        '5porcentoSalario'=>$salarioBase * 0.05, //não formatado
        'nb'=>$this->nb //não formatado
       ];
    }
    public function calcSalarioBase($cpf)
    {
        $dadosInss = $this->getDadosInss($cpf);
        if (!$dadosInss) return 0;
        $this->nb = $dadosInss[0]->nb;
        $salario_bruto = $dadosInss[0]->salario;
        $especie = $dadosInss[0]->especie;
        $datanascimento = $dadosInss[0]->datanascimento;
    
        $irpf = $this->calcIR($salario_bruto,$especie,$datanascimento);
        $salario_base = $salario_bruto - $irpf - 50;
        if ($salario_base <= 0) {
            if ($salario_bruto > 0) {
                $salario_base = $salario_bruto;
            } else {
                $salario_bruto = $this->salarioMinimo;
                $salario_base = $this->salarioMinimo;
            }
        }
        return $salario_base;
    }
    public function getDadosInss($cpf)
    {
        return DB::connection('mysql_inss')
        ->select("select p.nb,p.especie,p.datanascimento,s.vlbenef as salario from a_pessoais p inner join a_salarios s on p.nb=s.nb where consignavel = 1 and cpf = ? order by p.id limit 1",[$cpf]);
    }
    private function calcIR($salario,$especie,$datanascimento)
    {
        $idade = $this->calcularIdade($datanascimento);
       if ($idade >= 65 || $this->testaEspecie($especie)) $salario -= floatval ($this->tabelaIR['0.0']);
        $imposto = 0;
        $valor0 = 0;
        foreach ($this->tabelaIR as $percentual=>$valor1)
        {
            if ($salario > $valor1){
                $imposto += ($valor1 - $valor0)*floatval($percentual)/100;
                $valor0 = $valor1;
            } else {
                $imposto += ($salario - $valor0)*floatval($percentual)/100;
                return $imposto;
            }
        }
        return $imposto;
    }
    private function calcularIdade($dn)
    {
        $d1 = new \DateTime($dn);
        $d2 = new \DateTime(date('Y-m-d'));
        return $d2->diff($d1)->y;
    }
    private function testaEspecie($especie)
    {
        $excecoes = [05,06,32,33,34,51,83,92];
        return in_array($especie,$excecoes);
    }

}