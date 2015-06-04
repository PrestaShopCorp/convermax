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

class ConvermaxAdminController extends ModuleAdminController
{

	private $registered;

	public function __construct()
	{
		parent::__construct();
		$this->registered = Configuration::get('CONVERMAX_CERT') ? true : false;
	}

	public function initContent()
	{
		parent::initContent();

		$this->context->smarty->assign(array(
			'url' => Configuration::get('CONVERMAX_URL'),
			'module_dir' => $this->module->getPath(),
			'registered' => $this->registered
		));

		if ($this->registered)
		{
			$convermax = new ConvermaxAPI();
			$indexed_items = $convermax->getIndexedProducts();
			$total_items = Cmsearch::getProductsCount();

			$this->context->smarty->assign(array(
				'indexed_items' => $indexed_items,
				'total_items' => $total_items,
				'status_url' => Configuration::get('CONVERMAX_URL').'/indexing/status/json'
			));
		}
		$content = $this->postProcess().$this->context->smarty->fetch($this->module->getLocalPath().'/views/templates/admin/configuration.tpl');

		$this->context->smarty->assign(array(
			'content' => $content
		));

	}

	public function postProcess()
	{
		parent::postProcess();

		if (Tools::getValue('reindex'))
		{
			Cmsearch::indexation();
			die();
		}

		if (Tools::getValue('success'))
		{
			if (Tools::getValue('success') === 'true')
				return $this->displayConfirmation($this->l('Configuration updated'));
			else
				return $this->displayError($this->l('An error occurred while attempting to get certificate.'));
		}
		if (Tools::getValue('cm_token'))
		{
			$convermax = new ConvermaxAPI();
			if ($convermax->getCertificate(Tools::getValue('cm_token')))
			{
				if ($convermax->getHash())
					if ($convermax->createIndexFields())
						Tools::redirectAdmin('index.php?controller=ConvermaxAdmin&success=true&configure=convermax&token='.
							md5(pSQL(_COOKIE_KEY_.'ConvermaxAdmin'.(int)Tab::getIdFromClassName('ConvermaxAdmin').(int)$this->context->cookie->id_employee)));
			}
			Tools::redirectAdmin('index.php?controller=ConvermaxAdmin&success=false&configure=convermax&token='.
				md5(pSQL(_COOKIE_KEY_.'ConvermaxAdmin'.(int)Tab::getIdFromClassName('ConvermaxAdmin').(int)$this->context->cookie->id_employee)));
		}
		if (Tools::isSubmit('submitModule'))
		{
			if (isset($_FILES['cert'])
				&& isset($_FILES['cert']['tmp_name'])
				&& !empty($_FILES['cert']['tmp_name']))
			{
				if (!stristr(Tools::substr($_FILES['cert']['name'], -4), '.pem'))
					return $this->displayError($this->l('Invalid file'));
				else
					Configuration::updateValue('CONVERMAX_CERT', Tools::file_get_contents($_FILES['cert']['tmp_name']));
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
	}

	public function displayError($error)
	{
		$output = '
		<div class="bootstrap">
		<div class="module_error alert alert-danger" >
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			'.$error.'
		</div>
		</div>';
		$this->error = true;
		return $output;
	}

	public function displayConfirmation($string)
	{
		$output = '
		<div class="bootstrap">
		<div class="module_confirmation conf confirm alert alert-success">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			'.$string.'
		</div>
		</div>';
		return $output;
	}
}