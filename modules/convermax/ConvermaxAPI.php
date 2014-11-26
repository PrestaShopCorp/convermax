<?php

Class ConvermaxAPI
{

    private $base_url;
    private $hash;
    private $cert;

    public function  __construct($base_url, $hash, $cert)
    {
        $this->base_url = $base_url;
        $this->hash = $hash;
        $this->cert = $cert;
    }

    public function batchStart()
    {
        //$header = array('Content-Type: application/json; charset=utf-8');
        $url = $this->base_url.$this->hash.'/batchupdate/start?incremental=false';
        $ch = curl_init($url);
//$ch = curl_init('http://120.0.0.1/v2dev/fabbdc26/update/add');
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//curl_setopt($ch, CURLOPT_SSLCERT, @'C:\mydir\WORK\Server\www\asd.pem');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function batchEnd()
    {
        $url = $this->base_url.$this->hash.'/batchupdate/end';
        $ch = curl_init($url);
//$ch = curl_init('http://120.0.0.1/v2dev/fabbdc26/update/add');
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//curl_setopt($ch, CURLOPT_SSLCERT, @'C:\mydir\WORK\Server\www\asd.pem');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function batchAdd($items)
    {
        $url = $this->base_url.$this->hash.'/batchupdate/add';
        //$header = array('Content-Type: application/json; charset=utf-8');
        $ch = curl_init($url);
//$ch = curl_init('http://120.0.0.1/v2dev/fabbdc26/update/add');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//curl_setopt($ch, CURLOPT_SSLCERT, @'C:\mydir\WORK\Server\www\asd.pem');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function search($query)
    {
        $url = $this->base_url.$this->hash.'/search/json/'.urlencode($query);
        //$header = array('Content-Type: application/json; charset=utf-8');
        $ch = curl_init($url);
//$ch = curl_init('http://120.0.0.1/v2dev/fabbdc26/update/add');
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//curl_setopt($ch, CURLOPT_SSLCERT, @'C:\mydir\WORK\Server\www\asd.pem');
        //curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip, deflate'));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return json_decode($data);
    }

}