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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'convermax/ConvermaxAPI.php');
require_once(_PS_MODULE_DIR_ . 'convermax/classes/Cmsearch.php');
require_once(_PS_MODULE_DIR_ . 'convermax/classes/Cmproduct.php');

class Convermax extends Module
{
    public function __construct()
    {
        $this->name = 'convermax';
        $this->tab = 'search_filter';
        $this->version = '1.2.0';
        $this->author = 'CONVERMAX CORP';
        $this->module_key = '0958874296fcb714c52c9a74f5fdb88f';

        parent::__construct();

        $this->displayName = $this->l('Convermax');
        $this->description = $this->l('Convermax search module');
        $this->confirmUninstall = $this->l('Uninstall?');

        $this->registered = Configuration::get('CONVERMAX_CERT') ? true : false;

    }

    public function install()
    {
        if (!function_exists('curl_init')) {
            return $this->_abortInstall($this->l('CURL is not installed'));
        }

        if (!parent::install()
            || !$this->registerHook('leftColumn')
            || !$this->registerHook('header')
            || !$this->registerHook('top')
            || !$this->registerHook('productTab')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('actionPaymentConfirmation')
            || !$this->registerHook('actionProductAdd')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionProductDelete')
            || !Configuration::updateValue('CONVERMAX_URL', 'https://api.convermax.com/v2')
            || !Configuration::updateValue('CONVERMAX_CRON_KEY', Tools::passwdGen(8))
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        Configuration::deleteByName('CONVERMAX_URL');
        Configuration::deleteByName('CONVERMAX_CERT');
        Configuration::deleteByName('CONVERMAX_CRON_KEY');
        return true;
    }

    public function getContent()
    {

        $this->context->controller->addCSS($this->_path . '/views/css/backoffice.css');
        $this->context->controller->addJS($this->_path . '/views/js/backoffice.js');
        Media::addJsDef(array('cm_url' => Configuration::get('CONVERMAX_URL')));

        $this->context->smarty->assign(array(
            'url' => Configuration::get('CONVERMAX_URL'),
            'module_dir' => $this->_path,
            'registered' => $this->registered
        ));

        if ($this->registered) {
            $convermax = new ConvermaxAPI();
            $indexed_items = $convermax->getIndexedProducts();
            $total_items = Cmsearch::getProductsCount();
            $cron_url = $this->context->shop->getBaseURL() . 'modules/convermax/cron.php?key=' . Configuration::get('CONVERMAX_CRON_KEY');

            $this->context->smarty->assign(array(
                'indexed_items' => $indexed_items,
                'total_items' => $total_items,
                'cron_url' => $cron_url
            ));
        }

        return $this->postProcess() . $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/configuration.tpl');
    }

    public function postProcess()
    {
        if (Tools::getValue('reindex') === 'true') {
            Cmsearch::indexation();
            die();
        }

        if (Tools::getValue('state')) {
            switch (Tools::getValue('state')) {
                case 1:
                    return $this->displayConfirmation($this->l('Configuration updated'));
                case 2:
                    return $this->displayError($this->l('An error occurred while attempting to get certificate.'));
                case 3:
                    return $this->displayError($this->l('An error occurred while attempting to get hash.'));
                case 4:
                    return $this->displayError($this->l('An error occurred while attempting to create fields.'));
            }
        }
        if (Tools::getValue('cm_token')) {
            $convermax = new ConvermaxAPI();
            if ($convermax->getCertificate(Tools::getValue('cm_token'))) {
                if ($convermax->getHash()) {
                    if ($convermax->createIndexFields()) {
                        Tools::redirectAdmin($_SERVER['SCRIPT_NAME'] . '?controller=AdminModules&state=1&configure=convermax&token=' . Tools::getValue('token'));
                    }
                    Tools::redirectAdmin($_SERVER['SCRIPT_NAME'] . '?controller=AdminModules&state=4&configure=convermax&token=' . Tools::getValue('token'));
                }
                Tools::redirectAdmin($_SERVER['SCRIPT_NAME'] . '?controller=AdminModules&state=3&configure=convermax&token=' . Tools::getValue('token'));
            }
            Tools::redirectAdmin($_SERVER['SCRIPT_NAME'] . '?controller=AdminModules&state=2&configure=convermax&token=' . Tools::getValue('token'));
        }
        if (Tools::isSubmit('submitModule')) {
            if (isset($_FILES['cert'])
                && isset($_FILES['cert']['tmp_name'])
                && !empty($_FILES['cert']['tmp_name'])
            ) {
                if (!stristr(Tools::substr($_FILES['cert']['name'], -4), '.pem')) {
                    return $this->displayError($this->l('Invalid file'));
                } else {
                    Configuration::updateValue('CONVERMAX_CERT', Tools::file_get_contents($_FILES['cert']['tmp_name']));
                }
            }
            if (stristr(Tools::substr(Tools::getvalue('url'), -1), '/')) {
                $url = Tools::substr(Tools::getvalue('url'), 0, -1);
            } else {
                $url = Tools::getvalue('url');
            }
            if (!$url) {
                return $this->displayError($this->l('Enter URL'));
            }
            Configuration::updateValue('CONVERMAX_URL', $url);
            return $this->displayConfirmation($this->l('Configuration updated'));
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJQueryUI('ui.slider');
        $this->context->controller->addJqueryPlugin('cooki-plugin');
        $this->context->controller->addJS($this->_path . '/views/js/convermax.js');
        $this->context->controller->addCSS($this->_path . '/views/css/convermax.css');

        if (get_class($this->context->controller) == 'ConvermaxSearchModuleFrontController') {
            $this->context->controller->addJS($this->_path . '/views/js/convermax-search.js');
        }

        $cm_search_url = $this->context->link->getModuleLink('convermax', 'search');
        $this->context->smarty->assign('cm_search_url', $cm_search_url);

        Media::addJsDef(array('cm_url' => Configuration::get('CONVERMAX_URL')));
        Media::addJsDef(array('cm_search_url' => $cm_search_url));

        $home_category = Configuration::get('PS_HOME_CATEGORY');
        $id_category = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', $home_category));
        if ($id_category != $home_category) {
            Media::addJsDef(array('cm_category' => true));
            $this->context->controller->addJS($this->_path . '/views/js/convermax-search.js');
        }
    }

    public function hookTop()
    {
        if ($this->registered) {
            $this->context->smarty->assign('search_query_block', (string)Tools::getValue('search_query'));
            return $this->display(__FILE__, 'views/templates/hook/search-block.tpl');
        } else {
            $cookie = new Cookie('psAdmin');

            if ($cookie->id_employee) {
                return 'Convermax search is disabled. Please configure it.';
            }
        }
    }

    public function hookdispalyNav()
    {
        return $this->hookTop();
    }

    public function hookLeftColumn()
    {
        if (get_class($this->context->controller) == 'ConvermaxSearchModuleFrontController') {
            $this->context->smarty->assign(array(
                'pagesize' => abs((int)Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE')))
            ));
            return $this->display(__FILE__, 'views/templates/hook/facets.tpl');
        }

        $home_category = Configuration::get('PS_HOME_CATEGORY');
        $id_category = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', $home_category));
        if ($id_category != $home_category) {
            $category = new Category($id_category);
            $categories = $category->getParentsCategories();
            $category_full = array();
            foreach ($categories as $cat) {
                if ($cat['id_category'] != $home_category) {
                    $category_full[] = $cat['name'];
                }
            }
            if (!empty($category_full)) {
                $category_full = implode('>', array_reverse($category_full));
            }
            $facet = array(
                'category_full' => array($category_full)
            );
            $n = abs((int)Tools::getValue('n', (isset($this->context->cookie->nb_item_per_page) ?
                (int)$this->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE'))));
            $search = Cmsearch::find($this->context->language->id, ' ', 1, $n, 'position', 'desc', false, true, null, $facet);
            if ($search) {
                $facets_params = '';
                $is_ranged = 'IsRanged';
                $field_name = 'FieldName';
                $values = 'Values';
                $display_name = 'DisplayName';
                foreach ($search['cm_result']->Facets as $facet) {
                    if ($facet->{$is_ranged}) {
                        $rangemin = preg_replace('|TO .*\]|', '', $facet->{$values}[0]->Term);
                        $rangemax = preg_replace('|\[.*? |', '', $facet->{$values}[count($facet->{$values}) - 1]->Term);
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . ' = []' . ";\r\n";
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . '[0] = "' . $rangemin . $rangemax . "\";\r\n";
                    } else {
                        $values_count = count($facet->{$values});
                        for ($i = 0; $i < $values_count; $i++) {
                            if ($facet->{$values}[$i]->Selected == true) {
                                $facets_params .= 'cm_params.facets.' . $facet->{$field_name} . ' = []' . ";\r\n";
                                $facets_params .= 'cm_params.facets.' . $facet->{$field_name} . '[' . $i . '] = "' . $facet->{$values}[$i]->Term . "\";\r\n";
                                $facets_params .= 'cm_params.facets_display.' . $facet->{$field_name} . ' = "' . $facet->{$display_name} . "\";\r\n";
                            }
                        }
                    }
                }
                $this->context->smarty->assign(array(
                    'facets' => $search['cm_result']->Facets,
                    'query' => ' ',
                    'pagenumber' => 1,
                    'pagesize' => $n,
                    'facets_params' => isset($facets_params) ? $facets_params : false
                ));
                return $this->display(__FILE__, 'views/templates/hook/facets.tpl');
            }
        }
        return '';
    }

    public function hookProductTab($params)
    {
        if (Tools::getValue('results') && Tools::getValue('pid') && Tools::getValue('position') && Tools::getValue('page') && Tools::getValue('num')) {
            if (Tools::getValue('page') == 1) {
                $position = Tools::getValue('position');
            } else {
                $position = (Tools::getValue('page') - 1) * Tools::getValue('num') + Tools::getValue('position');
            }
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
        if (Tools::getValue('add') == 1) {
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
        foreach ($products as $product) {
            $ids[] = $product['product_id'];
        }
        $event_params = array(
            'ProductId' => Tools::jsonEncode($ids)
        );
        $convermax = new ConvermaxAPI();
        $convermax->track('ConfirmOrder', $event_params);
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

    public function cron()
    {
        $context = Context::getContext();
        //need for Product::getPriceStatic() method
        $context->employee = true;

        Cmsearch::indexation();
    }
}
