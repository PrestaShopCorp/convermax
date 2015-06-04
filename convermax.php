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
require_once(_PS_MODULE_DIR_.'convermax/classes/Cmsearch.php');

class Convermax extends Module
{
	public function __construct()
	{
		$this->name = 'convermax';
		$this->tab = 'search_filter';
		$this->version = '1.0.1';
		$this->author = 'CONVERMAX CORP';
		$this->module_key = '0958874296fcb714c52c9a74f5fdb88f';

		parent::__construct();

		$this->displayName = $this->l('Convermax');
		$this->description = $this->l('Convermax search module');

		$this->confirmUninstall = $this->l('Uninstall?');
	}

	public function install()
	{
		if (!parent::install()
		|| !$this->registerHook('leftColumn')
		|| !$this->registerHook('header')
		|| !$this->registerHook('backOfficeHeader')
		|| !$this->registerHook('top')
		|| !$this->registerHook('productTab')
		|| !$this->registerHook('actionCartSave')
		|| !$this->registerHook('actionPaymentConfirmation')
		|| !$this->registerHook('actionProductAdd')
		|| !$this->registerHook('actionProductUpdate')
		|| !$this->registerHook('actionProductDelete')
		|| !function_exists('curl_init')
		|| !Configuration::updateValue('CONVERMAX_URL', 'https://api.convermax.com/v21')
		|| !$this->addTab())
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		Configuration::deleteByName('CONVERMAX_URL');
		Configuration::deleteByName('CONVERMAX_CERT');
		$this->removeTab();
		return true;
	}

	public function addTab()
	{
		$tab = new Tab();

		foreach (Language::getLanguages(false) as $language)
			$tab->name[$language['id_lang']] = 'Convermax search';

		$tab->class_name = 'ConvermaxAdmin';
		$tab->module = $this->name;
		$tab->id_parent = 0;

		return $tab->save();
	}

	public function removeTab()
	{
		$tab = new Tab(Tab::getIdFromClassName('ConvermaxAdmin'));
		return $tab->delete();
	}

	public function getContent()
	{
		Tools::redirectAdmin('index.php?controller=ConvermaxAdmin&token='.
			md5(pSQL(_COOKIE_KEY_.'ConvermaxAdmin'.(int)Tab::getIdFromClassName('ConvermaxAdmin').(int)$this->context->cookie->id_employee)));
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getLocalPath()
	{
		return $this->local_path;
	}

	public function hookHeader()
	{
		$this->context->controller->addJQueryUI('ui.slider');
		$this->context->controller->addJqueryPlugin('cooki-plugin');
		$this->context->controller->addJS($this->_path.'/views/js/convermax.js');
		$this->context->controller->addCSS($this->_path.'/views/css/convermax.css');

		if (get_class($this->context->controller) == 'ConvermaxSearchModuleFrontController')
			$this->context->controller->addJS($this->_path.'/views/js/convermax-search.js');

		$cm_search_url = $this->context->link->getModuleLink('convermax', 'search');
		$this->context->smarty->assign('cm_search_url', $cm_search_url);

		Media::addJsDef(array('cm_url' => Configuration::get('CONVERMAX_URL')));
		Media::addJsDef(array('cm_search_url' => $cm_search_url));
	}

	public function hookTop()
	{
		$this->context->smarty->assign('search_query', (string)Tools::getValue('search_query'));
		return $this->display(__FILE__, 'views/templates/hook/search-block.tpl');
	}

	public function hookLeftColumn()
	{
		if (get_class($this->context->controller) == 'ConvermaxSearchModuleFrontController')
		{
			$this->context->smarty->assign(array(
				'pagesize' => abs((int)Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE')))
			));
			return $this->display(__FILE__, 'views/templates/hook/facets.tpl');
		}
		return '';
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
			$convermax = new ConvermaxAPI();
			$convermax->track('ClickOnSearchResult', $event_params);
		}

		$event_params = array(
			'ProductId' => $params['product']->id
		);
		$convermax = new ConvermaxAPI();
		$convermax->track('ProductView', $event_params);

	}

	public function hookActionCartSave()
	{
		if (Tools::getValue('add') == 1)
		{
			$event_params = array(
				'ProductId' => Tools::getValue('id_product')
			);

		$convermax = new ConvermaxAPI();
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
		$convermax = new ConvermaxAPI();
		$convermax->track('ConfirmOrder', $event_params);
	}

	public function hookBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'/views/css/backoffice.css');
		$this->context->controller->addJS($this->_path.'/views/js/backoffice.js');
	}

	public function hookActionProductAdd($params)
	{
		Cmsearch::indexation($params['id_product'], false);
	}

	public function hookActionProductUpdate($params)
	{
		Cmsearch::indexation($params['id_product'], true);
	}

	public function hookActionProductDelete($params)
	{
		Cmsearch::deleteProduct($params['id_product']);
	}

}