<?php

namespace App\Traits\Integracoes;


trait ZApi 
{
    private $mainUrl;
    public function setMainUrl($key)
    {
        $urls = [
            'BMG'=>'',
            'PAN'=>'https://api.z-api.io/instances/3AA17AFBF8D9D0CBE6822E7156C53355/token/CAB540BC618E9F02887CA39B/'
        ];
        $this->mainUrl = $urls[$key];
    }
    public function sendButtonList($phone,$message,$buttons)
    {
        $header = [
            'Accept: application/json',
            'Content-type: application/json',
        ];
        $data_string = json_encode([
            'phone'=>$phone,
            'message'=>$message,
            'buttonList'=>[
                'buttons'=>$buttons
            ]
        ]);

        return $this->curlRequest($header,'POST',$data_string,'send-button-list');
    }
    public function sendText($phone,$message)
    {
        $header = [
            'Accept: application/json',
            'Content-type: application/json',
        ];
        $data_string = json_encode([
            'phone'=>$phone,
            'message'=>$message,
            'delayTyping'=>10
        ]);
        return $this->curlRequest($header,'POST',$data_string,'send-text');
    }
    public function sendImage($phone,$image,$message)
    {
        $header = [
            'Accept: application/json',
            'Content-type: application/json',
        ];
        $data_string = json_encode([
            'phone'=>$phone,
            'image'=>$image,
            'caption'=>$message,
            'delayTyping'=>10
        ]);
        return $this->curlRequest($header,'POST',$data_string,'send-image');
    }
    private function curlRequest($header,$method='GET',$postFields=null,$urlRoute='')
    {
      /*  $f = fopen("./log.txt",'a+');
        fwrite($f,date("dd/mm/yyyy H:i:s")."\n");
        fclose($f);*/
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->mainUrl.$urlRoute);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output === FALSE)
            return ["error"=>"erro no request"];
        $output = json_decode($output,true);
        if (array_key_exists('error',$output)) return ['error'=>$output['error']];
        return $output;
    }
}