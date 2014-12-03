<?php


if (!defined('_PS_VERSION_'))
    exit;

Class Convermax extends Module
{
    public function __construct()
    {
        $this->name = 'convermax';
        $this->tab = 'search_filter';
        $this->version = '0.1';
        $this->author = 'Author';

        parent::__construct();

        $this->displayName = $this->l('Convermax');
        $this->description = $this->l('Convermax search module');

        $this->confirmUninstall = $this->l('Uninstall?');
    }

    public function install()
    {
        //return parent::install();
        parent::install();
        $this->registerHook('leftColumn');
        $this->registerHook('leftColumn');

        //to delete
        Configuration::updateValue('CONVERMAX_URL', 'https://api.convermax.com/v2dev/');
        Configuration::updateValue('CONVERMAX_HASH', '4f199abe');
        Configuration::updateValue('CONVERMAX_CERT', '/home/demo/prestashop/www/modules/convermax/prestashop_key+cert.pem');


        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {

    }

    public function hookLeftColumn($params)
    {
        //return $this->generateFacetsBlock($this->getFacets());
        if (get_class($this->context->controller) == 'SearchController')
        {
            //return '<h4>FACETS BLOCK</h4>';
            //$myvar = 'blabla';
            $this->context->smarty->assign(array(
                'myvar' => 'blabla2',
            ));
            return $this->display(__FILE__, 'facets.tpl');
        }
        return '';
    }

    public function hookHeader($params)
    {
        if (get_class($this->context->controller) == 'SearchController')
        {
            $this->context->controller->addJS(($this->_path).'convermax.js');
            $this->context->controller->addCSS(($this->_path).'convermax.css');
        }
    }

    public function ajaxCall()
    {
        return '<h4>AJAX CALL</h4>';
    }
}