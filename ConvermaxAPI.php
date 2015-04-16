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
 *  @author    CONVERMAX CORP <info@convermax.com>
 *  @copyright 2015 CONVERMAX CORP
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of CONVERMAX CORP
 */

class ConvermaxAPI
{

	private $url;
	private $cert;

	public function __construct()
	{
		$url = Configuration::get('CONVERMAX_URL');
		if (stristr(Tools::substr($url, -1), '/'))
			$url = Tools::substr($url, 0, -1);
		$this->url = $url;
		$this->cert = $this->createTmpCertFile(Configuration::get('CONVERMAX_CERT'));
	}

	public function __destruct()
	{
		unlink($this->cert);
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
		unset($data);
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
		unset($data);
		return true;
	}

	public function batchAdd($items)
	{
		$url = $this->url.'/batchupdate/add';
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
		if (curl_errno($ch))
			return false;
		unset($data);

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
		$url .= '&page='.$page_number.'&pagesize='.$page_size;
		if ($facets)
		{
			$i = 0;
			foreach ($facets as $key => $val)
			{
				$url .= '&facet.'.$i.'.field='.urlencode($key);
				foreach ($val as $v)
					$url .= '&facet.'.$i.'.selection='.urlencode($v);
				$i++;
			}
		}

		if ($order_by)
			$url .= '&sort.0.fieldname='.$order_by.($order_desc ? '&sort.0.descending=true' : '');
		$url .= '&analytics.userid='.$this->getCookie('cmuid');
		$url .= '&analytics.sessionid='.$this->getCookie('cmsid');
		$url .= '&analytics.useragent='.urlencode($_SERVER['HTTP_USER_AGENT']);
		$url .= '&analytics.userip='.$_SERVER['REMOTE_ADDR'];
		if (Tools::getValue('searchfeatures'))
			$url .= '&analytics.eventparams.searchfeatures='.Tools::getValue('searchfeatures');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		//curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		$data = curl_exec($ch);
		//if (curl_errno($ch))
			//$data = 'connection error';
		return Tools::jsonDecode($data);
	}

	public function autocomplete($query)
	{
		$url = $this->url.'/autocomplete/json?query='.urlencode($query);
		$url .= '&analytics.userid='.$this->getCookie('cmuid');
		$url .= '&analytics.sessionid='.$this->getCookie('cmsid');
		$url .= '&analytics.useragent='.urlencode($_SERVER['HTTP_USER_AGENT']);
		$url .= '&analytics.userip='.$_SERVER['REMOTE_ADDR'];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		$data = curl_exec($ch);
		if (curl_errno($ch))
			return false;
		return $data;
	}

	public function track($event_type, $event_params)
	{
		$params = array(
			'EventType' => $event_type,
			'EventParams' => $event_params,
			'UserID' => $this->getCookie('cmuid'),
			'SessionID' => $this->getCookie('cmsid'),
			'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
			'UserIP' => $_SERVER['REMOTE_ADDR']
		);

		$url = $this->url.'/track';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		$data = curl_exec($ch);
		//echo curl_error($ch);
		if (curl_errno($ch))
			return false;
		unset($data);

		return true;
	}

	private function getCookie($name)
	{
		return "$_COOKIE[$name]";
	}

	private function createTmpCertFile($cert)
	{
		$file = tempnam(sys_get_temp_dir(), 'PS_');
		file_put_contents($file, $cert);
		return $file;
	}

	public function getCertificate($token)
	{
		$url = 'https://api.convermax.com/v2dev/createcertificate?token='.$token;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$data = curl_exec($ch);
		if (curl_errno($ch) || empty($data))
			return false;
		Configuration::updateValue('CONVERMAX_CERT', $data);
		file_put_contents($this->cert, $data);
		return true;
	}

	public function getHash()
	{
		$name = urlencode(Configuration::get('PS_SHOP_NAME'));
		$url = 'https://api.convermax.com/v2dev/scheme/create?name='.$name;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		$data = curl_exec($ch);
		if (curl_errno($ch) || empty($data))
			return false;
		$this->url = 'http://api.convermax.com/v2dev/'.str_replace('"', '', $data);
		Configuration::updateValue('CONVERMAX_URL', $this->url);
		return true;
	}

	public function createIndexFields()
	{
		$properties = array();
		$properties[0] = Product::getProductProperties(5, array('id_product'=>1));
		if ($properties[0]['features'])
		{
			foreach ($properties[0]['features'] as $feature)
				$properties[0][$feature['name']] = $feature['value'];
			unset($properties[0]['features']);
		}
		$url = $this->url.'/scheme/createfields?catalog=catalog';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($properties));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->cert);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		$data = curl_exec($ch);
		if (curl_errno($ch))
			return false;
		unset($data);

		return true;
	}

}