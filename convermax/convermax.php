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

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'convermax/ConvermaxAPI.php');

class Convermax extends Module
{
	public function __construct()
	{
		$this->name = 'convermax';
		$this->tab = 'search_filter';
		$this->version = '1.0.0';
		$this->author = 'CONVERMAX CORP';

		parent::__construct();

		$this->displayName = $this->l('Convermax');
		$this->description = $this->l('Convermax search module');

		$this->confirmUninstall = $this->l('Uninstall?');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('leftColumn') || !$this->registerHook('header') || !$this->registerHook('backOfficeHeader')
		|| !$this->registerHook('productTab')
		|| !$this->registerHook('actionCartSave')
		|| !$this->registerHook('actionPaymentConfirmation')
		|| !function_exists('curl_init'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		Configuration::deleteByName('CONVERMAX_URL');
		Configuration::deleteByName('CONVERMAX_CERT');
		return true;
	}

	public function getContent()
	{
		$this->context->smarty->assign(array(
			'url' => Configuration::get('CONVERMAX_URL'),
			'module_dir' => $this->_path,
		));
		return $this->postProcess().$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitModule'))
		{
			if (isset($_FILES['cert'])
				&& isset($_FILES['cert']['tmp_name'])
				&& !empty($_FILES['cert']['tmp_name']))
			{
				if (!stristr(Tools::substr($_FILES['cert']['name'], -4), '.pem'))
					return $this->displayError($this->l('Invalid file'));
				else
				{
					$key_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'key';
					if (!is_writable($key_dir))
						return $this->displayError($this->l('directory ('.$key_dir.') not writable'));
					$file_path = $key_dir.DIRECTORY_SEPARATOR.'convermax.pem';
					if (!move_uploaded_file($_FILES['cert']['tmp_name'], $file_path))
						return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
					else
						Configuration::updateValue('CONVERMAX_CERT', $file_path);
				}
				if (stristr(Tools::substr(Tools::getvalue('url'), -1), '/'))
					$url = Tools::substr(Tools::getvalue('url'), 0, -1);
				else
					$url = Tools::getvalue('url');
				if (!$url)
					return $this->displayError($this->l('Enter URL'));
				Configuration::updateValue('CONVERMAX_URL', $url);
				return $this->displayConfirmation($this->l('Configuration updated'));
			}
			return $this->displayError($this->l('Choose file'));
		}
	}

	public function hookLeftColumn()
	{
		if (get_class($this->context->controller) == 'SearchController')
		{
			$this->context->smarty->assign(array(
				'pagesize' => abs((int)Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE')))
			));
			return $this->display(__FILE__, 'views/templates/hook/facets.tpl');
		}
		return '';
	}

	public function hookHeader()
	{
		$this->context->controller->addJQueryUI('ui.slider');
		$this->context->controller->addJqueryPlugin('cooki-plugin');
		$this->context->controller->addJS($this->_path.'/views/js/convermax.js');
		$this->context->controller->addCSS($this->_path.'/views/css/convermax.css');

		if (get_class($this->context->controller) == 'SearchController')
			$this->context->controller->addJS($this->_path.'/views/js/convermax-search.js');
		Media::addJsDef(array('cm_url' => Configuration::get('CONVERMAX_URL')));
	}

	public function hookProductTab($params)
	{
		if (Tools::getValue('results') && Tools::getValue('pid') && Tools::getValue('position') && Tools::getValue('page') && Tools::getValue('num'))
		{
			if (Tools::getValue('page') == 1)
				$position = Tools::getValue('position');
			else
				$position = (Tools::getValue('page') - 1) * Tools::getValue('num') + Tools::getValue('position');
			$event_params = array(
				'Position' => $position,
				'Total' => Tools::getValue('results'),
				'ProductId' => Tools::getValue('pid')
			);
			$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'));
			$convermax->track('ClickOnSearchResult', $event_params);
		}

		$event_params = array(
			'ProductId' => $params['product']->id
		);
		$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'));
		$convermax->track('ProductView', $event_params);

	}

	public function hookActionCartSave()
	{
		if (Tools::getValue('add') == 1)
		{
			$event_params = array(
				'ProductId' => Tools::getValue('id_product')
			);

		$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'));
		$convermax->track('AddToCart', $event_params);
		}
	}

	public function hookActionPaymentConfirmation($params)
	{
		$order = new Order($params['id_order']);
		$products = $order->getProducts();
		$ids = array();
		foreach ($products as $product)
			$ids[] = $product['product_id'];
		$event_params = array(
			'ProductId' => Tools::jsonEncode($ids)
		);
		$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'));
		$convermax->track('ConfirmOrder', $event_params);
	}

	public function hookBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'/views/css/backoffice.css');
		$this->context->controller->addJS($this->_path.'/views/js/backoffice.js');
	}

	public function ajaxCall()
	{
		$query = Tools::getValue('search_query');

		$original_query = $query;
		$query = Tools::replaceAccentedChars(urldecode($query));

		$srch_cntrl = Controller::getController('SearchController');
		$srch_cntrl->productSort();
		$srch_cntrl->n = abs((int)Tools::getValue('n', (isset($srch_cntrl->context->cookie->nb_item_per_page) ?
			(int)$srch_cntrl->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE'))));
		$srch_cntrl->p = abs((int)Tools::getValue('p', 1));

		$facets = Tools::getValue('cm_select');

		$search = Search::find($this->context->language->id, $query, $srch_cntrl->p, $srch_cntrl->n, $srch_cntrl->orderBy,
			$srch_cntrl->orderWay, false, true, null, $facets);

		$position = 1;
		foreach ($search['result'] as &$product)
		{
			$product['link'] .= (strpos($product['link'], '?') === false ? '?' : '&').'search_query='.urlencode($query).'&results='.(int)$search['total'].
				'&pid='.(int)$product['id_product'].'&position='.$position.'&page='.$srch_cntrl->p.'&num='.$srch_cntrl->n;
			$position++;
		}

		Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
		$nbProducts = $search['total'];
		$srch_cntrl->pagination($nbProducts);
		$srch_cntrl->addColorsToProductList($search['result']);

		if (stripos($search['cm_result']->State, 'nothing'))
			$cm_message = 'nothing found';
		elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
		{
			$cm_message = 'your request has been corrected to '.$search['cm_result']->Corrections[0]->Replace;
			$original_query = $search['cm_result']->Corrections[0]->Replace;
		}
		else
			$cm_message = false;

		$this->context->smarty->assign(array(
			'products' => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
			'search_products' => $search['result'],
			'nbProducts' => $search['total'],
			'search_query' => $original_query,
			'instant_search' => $srch_cntrl->instant_search,
			'cm_message' => $cm_message,
			'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));

		$list = $this->context->smarty->fetch(_PS_MODULE_DIR_.'convermax/views/templates/hook/search.tpl');

		$this->context->smarty->assign(array(
			'facets' => $search['cm_result']->Facets,
			'query' => $search['cm_result']->Query,
			'pagenumber' => $srch_cntrl->p,
			'pagesize' => $srch_cntrl->n,
		));

		$facets = $this->context->smarty->fetch(_PS_MODULE_DIR_.'convermax/views/templates/hook/facet.tpl');

		$vars = array(
			'productList' => utf8_encode($list),
			'facets' => $facets,
			'redirect_url' => isset($search['cm_result']->Actions[0]->RedirectUrl) ? $search['cm_result']->Actions[0]->RedirectUrl : false
		);

		return Tools::jsonEncode($vars);
	}
}