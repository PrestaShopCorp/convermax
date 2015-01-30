<?php

class ConvermaxAPI
{

    private $url;
    private $cert;

    public function  __construct($url, $cert = '')
    {
        if (stristr(substr($url, -1), '/'))
            $url = substr($url, 0, -1);
        $this->url = $url;
        //$this->url = 'https://api.convermax.com/v2dev/4f199abe';
        $this->cert = $cert;
    }

    public function batchStart()
    {
        $url = $this->url.'/batchupdate/start?incremental=false';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function batchEnd()
    {
        $url = $this->url.'/batchupdate/end';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function batchAdd($items)
    {
        $url = $this->url.'/batchupdate/add';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return true;
    }

    public function search($query, $page_number = 0, $page_size = 10, $facets = null, $order_by = 'position', $order_desc = false)
    {
        if ($order_by == 'position')
        {
            $order_by = false;
            $order_desc = false;
        }
        $url = $this->url.'/search/json?query='.urlencode($query);
        $url .= '&page=' . $page_number . '&pagesize=' . $page_size;
        /*if ($facets)
        {
            $i = 0;
            foreach ($facets as $key => $val)
            {
                $u .= '&facet.'.$i.'.field='.urlencode($key);
                foreach ($val as $v)
                {
                    //facet.0.field=CurrentPrice&facet.0.selection=[100%20TO%20249.99]&facet.0.selection=[250%20TO%20399.99]&facet.0.selection=[400%20TO%20599.99]&
                    $u .= '&facet.'.$i.'.selection='.urlencode($v);
                }
                $i++;
            }
            $url .= $u;
        }*/
        if ($facets)
        {
            $i = 0;
            foreach ($facets as $key => $val)
            {
                $url .= '&' . 'facet.' . $i . '.field=' . urlencode($key);
                foreach ($val as $v)
                {
                    $url .= '&facet.' . $i . '.selection=' . urlencode($v);
                }
                $i++;
            }
        }

        if ($order_by)
        {
            $url .= '&sort.0.fieldname=' . $order_by . ($order_desc ? '&sort.0.descending=true' : '');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch))
            die('convermax connection error');
        return json_decode($data);
    }

    public function autocomplete($query)
    {
        $url = $this->url.'/autocomplete/json?query='.urlencode($query);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch))
            return false;
        return $data;
    }

}