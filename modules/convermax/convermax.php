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
        return parent::install();

        //to delete
        Configuration::updateValue('CONVERMAX_URL', 'https://api.convermax.com/v2test/');
        Configuration::updateValue('CONVERMAX_HASH', '4f199abe');
        Configuration::updateValue('CONVERMAX_CERT', '/home/demo/prestashop/www/modules/convermax/prestashop_key+cert.pem');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {

    }

    private function sendToIndexation()
    {
        //$d
    }
}