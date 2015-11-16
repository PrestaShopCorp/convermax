<?php
/**
 * 2015 CONVERMAX CORP
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@convermax.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    CONVERMAX CORP <info@convermax.com>
 * @copyright 2015 CONVERMAX CORP
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of CONVERMAX CORP
 */

class ConvermaxAPI
{

    private $base_url;
    private $url;
    private $surl;
    private $cert;

    public function __construct()
    {
        $url = Configuration::get('CONVERMAX_URL');
        if (stristr(Tools::substr($url, -1), '/')) {
            $url = Tools::substr($url, 0, -1);
        }
        $this->url = $url;
        if (preg_match('|(.*?://.*?\.convermax\.com/.*?)/.*|', $url, $matches)) {
            $this->base_url = $matches[1];
        } else {
            $this->base_url = $url;
        }

        $surl = Configuration::get('CONVERMAX_SURL');
        if (stristr(Tools::substr($surl, -1), '/')) {
            $surl = Tools::substr($surl, 0, -1);
        }
        $this->surl = $surl;
        if (preg_match('|(.*?://.*?\.convermax\.com/.*?)/.*|', $surl, $matches)) {
            $this->base_surl = $matches[1];
        } else {
            $this->base_surl = $surl;
        }

        $this->cert = $this->createTmpCertFile(Configuration::get('CONVERMAX_CERT'));
    }

    public function __destruct()
    {
        if ($this->cert) {
            unlink($this->cert);
        }
    }

    public function batchStart()
    {
        if ($this->inProgress()) {
            return false;
        }
        $url = $this->surl . '/batchupdate/start?incremental=false';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        $data = curl_exec($ch);
        if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 204) {
            return false;
        }
        unset($data);
        return true;
    }

    public function batchEnd()
    {
        $url = $this->surl . '/batchupdate/end';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);
        return true;
    }

    public function batchAdd($items)
    {
        $url = $this->surl . '/batchupdate/add';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function batchUpdate($items)
    {
        $url = $this->surl . '/batchupdate/update';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function add($items)
    {
        if ($this->inProgress()) {
            return false;
        }
        $url = $this->surl . '/update/add?incremental=true';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function update($items)
    {
        if ($this->inProgress()) {
            return false;
        }
        $url = $this->surl . '/update/update';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function delete($items)
    {
        if ($this->inProgress()) {
            return false;
        }
        $url = $this->surl . '/update/deletebymask';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($items));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function search($query, $page_number = 0, $page_size = 10, $facets = null, $order_by = 'position', $order_desc = false)
    {
        if ($order_by == 'position') {
            $order_by = false;
            $order_desc = false;
        }
        $url = $this->url . '/search/json?query=' . urlencode($query);
        $url .= '&page=' . $page_number . '&pagesize=' . $page_size;
        if ($facets) {
            $i = 0;
            foreach ($facets as $key => $val) {
                $url .= '&facet.' . $i . '.field=' . urlencode($key);
                foreach ($val as $v) {
                    $url .= '&facet.' . $i . '.selection=' . urlencode($v);
                }
                $i++;
            }
        }

        if ($order_by) {
            $url .= '&sort.0.fieldname=' . $order_by . ($order_desc ? '&sort.0.descending=true' : '');
        }
        $url .= '&analytics.userid=' . $this->getCookie('cmuid');
        $url .= '&analytics.sessionid=' . $this->getCookie('cmsid');
        $url .= '&analytics.useragent=' . urlencode($_SERVER['HTTP_USER_AGENT']);
        $url .= '&analytics.userip=' . $_SERVER['REMOTE_ADDR'];
        if (Tools::getValue('searchfeatures')) {
            $url .= '&analytics.eventparams.searchfeatures=' . Tools::getValue('searchfeatures');
        }

        $url = str_replace('https://', 'http://', $url);
        preg_match('|.*?://(.*?\.convermax\.com)/.*?|', $url, $matches);
        $host = $matches[1];
        $request = "GET $url HTTP/1.1\r\n" .
            "Accept:*/*\r\n" .
            "Accept-Encoding:gzip, deflate\r\n" .
            "Host:$host\r\n" .
            "User-Agent:Convermax Prestashop\r\n\r\n";

        if (!$fp = @pfsockopen($host, 80, $errno, $errstr, 3)) {
            return false;
        }
        if (!fwrite($fp, $request)) {
            fclose($fp);
            if (!$fp = @pfsockopen($host, 80, $errno, $errstr, 3)) {
                return false;
            }
            if (!fwrite($fp, $request)) {
                fclose($fp);
                return false;
            }
        }
        $data = '';
        while (true) {
            $buf = '';
            $buf = fgets($fp);
            if (stripos($buf, 'content-length') !== false) {
                $lenght = explode(':', $buf);
                $lenght = trim($lenght[1]);
            }
            if ($buf == "\r\n" || empty($buf)) {
                break;
            }
        }
        if (isset($lenght)) {
            for ($i = 0; $i < $lenght; $i++) {
                $data .= fread($fp, 1);
            }
        }
        $data = Tools::jsonDecode(@gzinflate(Tools::substr($data, 10)));

        if (isset($data->TotalHits)) {
            return $data;
        }
        return false;
    }

    public function autocomplete($query)
    {
        $url = $this->url . '/autocomplete/json?query=' . urlencode($query);
        $url .= '&analytics.userid=' . $this->getCookie('cmuid');
        $url .= '&analytics.sessionid=' . $this->getCookie('cmsid');
        $url .= '&analytics.useragent=' . urlencode($_SERVER['HTTP_USER_AGENT']);
        $url .= '&analytics.userip=' . $_SERVER['REMOTE_ADDR'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch) || empty($data) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            return false;
        }
        return $data;
    }

    public function track($event_type, $event_params)
    {
        if (!$this->cert || !$this->getCookie('cmuid') || !$this->getCookie('cmsid')) {
            return true;
        }
        $params = array(
            'EventType' => $event_type,
            'EventParams' => $event_params,
            'UserID' => $this->getCookie('cmuid'),
            'SessionID' => $this->getCookie('cmsid'),
            'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
            'UserIP' => $_SERVER['REMOTE_ADDR']
        );

        $url = $this->url . '/track';
        $url = str_replace('https://', 'http://', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    private function getCookie($name)
    {
        if (isset($_COOKIE[$name])) {
            return "$_COOKIE[$name]";
        }
        return false;
    }

    private function createTmpCertFile($cert)
    {
        $file = tempnam(sys_get_temp_dir(), 'CM_');
        file_put_contents($file, $cert);
        return $file;
    }

    public function getCertificate($token)
    {
        $url = $this->base_url . '/createcertificate?token=' . $token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if (curl_errno($ch) || empty($data) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 201) {
            return false;
        }
        Configuration::updateValue('CONVERMAX_CERT', $data);
        file_put_contents($this->cert, $data);
        return true;
    }

    public function getHash()
    {
        $name = urlencode(Configuration::get('PS_SHOP_NAME'));
        $url = $this->base_surl . '/scheme/create?name=' . $name . '&template=prestashop';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch) || empty($data) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            return false;
        }
        $this->url = $this->base_url . '/' . str_replace('"', '', $data);
        Configuration::updateValue('CONVERMAX_URL', $this->url);

        $this->surl = $this->base_surl . '/' . str_replace('"', '', $data);
        Configuration::updateValue('CONVERMAX_SURL', $this->surl);
        return true;
    }

    public function createIndexFields()
    {
        $fields = Cmsearch::getFields();

        $s_fields = array();
        foreach ($fields as $key => $val) {
            $s_fields[$this->sanitize(($key))] = '';
        }

        $url = $this->surl . '/scheme/createfields?catalog=products';
        //$url = str_replace('client.', 'api.', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode(array($s_fields)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        unset($data);

        return true;
    }

    public function sanitize($string)
    {
        $chars = array('+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '\\', ',', "'", ' ');
        $string = str_replace($chars, '_', $string);
        $string = preg_replace('|_+|', '_', $string);
        $string = trim($string, '_');
        return $string;
    }

    public function getIndexedProducts()
    {
        $url = $this->url . '/healthcheck/json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        $data = Tools::jsonDecode($data);
        $items_in_index = 'ItemsInIndex';
        return isset($data->{$items_in_index}->Actual) ? (int)$data->{$items_in_index}->Actual : 0;
    }

    public function inProgress()
    {
        $url = $this->url . '/indexing/status/json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        $data = Tools::jsonDecode($data);
        $in_progress = 'InProgress';
        return $data->{$in_progress};
    }
}
