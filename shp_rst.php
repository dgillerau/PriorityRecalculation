<?php
//class to work with all shopify requests
require_once ('Settings.php');

class shp_rst{

    public function getcontent($url)
    {

        $api_key= Settings::$api_key;
        $password=Settings::$password;
        $host=Settings::$host;

        set_time_limit(50);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$api_key.":".$password."@".$host.$url);
        //echo "https://".$this->api_key.":".$this->password."@".$this->host.$url;
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json','Content-type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        //curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        $data = curl_exec($ch);
        //print "<pre>";
        //print_r(curl_getinfo($ch));
        //print "</pre>";

        curl_close($ch);
        return json_decode($data, true);

    }


}



?>