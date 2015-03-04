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

	public function __construct($url, $cert = '')
	{
		if (stristr(Tools::substr($url, -1), '/'))
			$url = Tools::substr($url, 0, -1);
		$this->url = $url;
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
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		//curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		$data = curl_exec($ch);
		if (curl_errno($ch))
			die('convermax connection error');
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

	public function getCookie($name)
	{
		return "$_COOKIE[$name]";
	}

}