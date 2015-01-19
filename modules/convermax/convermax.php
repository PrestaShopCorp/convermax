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
                //'myvar' => 'blabla2',
                'pagesize' => abs((int)(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))))
            ));
            return $this->display(__FILE__, 'facets.tpl');
        }
        return '';
    }

    public function hookHeader($params)
    {
        //if (get_class($this->context->controller) == 'SearchController')
        //{
            $this->context->controller->addJS(($this->_path).'convermax.js');
            $this->context->controller->addCSS(($this->_path).'convermax.css');
        $this->context->controller->addJQueryUI('ui.slider');
            //$this->context->controller->addJqueryUI('ui.autocomplete');
            //$url = Configuration::get('CONVERMAX_URL') . Configuration::get('CONVERMAX_HASH') . '/autocomplete/json';
            //Media::addJsDef(array('cm_autocomplete_url' => $url));
        //}
    }

    public function hkookTop($params)
    {

    }





    public function ajaxCall()
    {
        //return '<h4>AJAX CALL</h4>';
        global $smarty;
        /*//$search = Search::find($this->context->language->id, $query, $this->p, $this->n, $this->orderBy, $this->orderWay);
        $search = Search::find(1, 'print', 1, 1, 'position',
            'desc', false, true, null, true);

        $smarty->assign(array(
            'search_products' => $search['result'],
            'nbProducts' => $search['total'],
            'search_query' => '$original_query',
            'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));

        $product_list = $smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');

        $vars = array(
            //'filtersBlock' => utf8_encode($this->generateFiltersBlock($selected_filters)),
            'productList' => utf8_encode($product_list),
            //'pagination' => $smarty->fetch(_PS_THEME_DIR_.'pagination.tpl'),
            //'categoryCount' => $category_count,
            //'meta_title' => $meta_title.' - '.Configuration::get('PS_SHOP_NAME'),
            //'heading' => $meta_title,
            //'meta_keywords' => isset($meta_keywords) ? $meta_keywords : null,
            //'meta_description' => $meta_description,
            //'current_friendly_url' => ((int)$n == (int)$nb_products) ? '#/show-all': '#'.$filter_block['current_friendly_url'],
            //'filters' => $filter_block['filters'],
            //'nbRenderedProducts' => (int)$nb_products,
            //'nbAskedProducts' => (int)$n
        );*/


        //Controller::getController('SearchController')->myMethod();
        //$query = 'print';

        //$query = Tools::getValue('cm_query');
        $query = Tools::getValue('search_query');

        $original_query = $query;
        $query = Tools::replaceAccentedChars(urldecode($query));
        //$page_number = Tools::getValue('page');
        //$page_size = Tools::getValue('size');
        //$query = Tools::getValue('search_query');


        //$facets = $_GET['facets'];
        //$facets = Convermax::getFacets();




        $srch_cntrl = Controller::getController('SearchController');

        $srch_cntrl->productSort();
        //$srch_cntrl->n = abs((int)(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));

        $srch_cntrl->n = abs((int)(Tools::getValue('n', (isset($srch_cntrl->context->cookie->nb_item_per_page) ? (int)$srch_cntrl->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE')))));

        $srch_cntrl->p = abs((int)(Tools::getValue('p', 1)));

        //($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
        //$order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null, $facets = false)

        //$srch_cntrl->p, $srch_cntrl->n, $this->orderBy, $this->orderWay

        /*
        foreach ($_GET as $key => $val)
            if (substr($key, 0, 3) == 'cm_')
                $facets[str_replace('cm_', '', $key)] = Tools::getValue($key);
        */
        $facets = Tools::getValue('cm_select');
        //if ($facets && is_array($facets)
            //foreach ($facets as $facet)

        $search = Search::find($this->context->language->id, $query, $srch_cntrl->p, $srch_cntrl->n, $srch_cntrl->orderBy,
            $srch_cntrl->orderWay, false, true, null, $facets);

        Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
        $nbProducts = $search['total'];
        $srch_cntrl->pagination($nbProducts);

        $srch_cntrl->addColorsToProductList($search['result']);

        if (stripos($search['cm_result']->State, 'nothing'))
            $cm_message = 'nothing found';
        elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
        {
            $cm_message = 'your request has been corrected to ' . $search['cm_result']->Corrections[0]->Replace;
            $original_query = $search['cm_result']->Corrections[0]->Replace;
        }
        else
            $cm_message = false;

        $smarty->assign(array(
            'products' => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
            'search_products' => $search['result'],
            'nbProducts' => $search['total'],
            'search_query' => $original_query,
            'instant_search' => $srch_cntrl->instant_search,
            //'errors' => array('custom error 1', 'custom error 2', 'custom error 3'),
            'cm_message' => $cm_message,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));


        //$list = $smarty->fetch(_PS_THEME_DIR_.'search.tpl');
        $list = $smarty->fetch(_PS_MODULE_DIR_.'convermax/search.tpl');;


        $smarty->assign(array(
            'facets' => $search['cm_result']->Facets,
            'query' => $search['cm_result']->Query,
            'pagenumber' => $srch_cntrl->p,
            'pagesize' => $srch_cntrl->n,
        ));

        $facets = $smarty->fetch(_PS_MODULE_DIR_.'convermax/facet.tpl');


        $vars = array(
            //'filtersBlock' => utf8_encode($this->generateFiltersBlock($selected_filters)),
            'productList' => utf8_encode($list),
            //'pagination' => $smarty->fetch(_PS_THEME_DIR_.'pagination.tpl'),
            //'categoryCount' => $category_count,
            //'meta_title' => $meta_title.' - '.Configuration::get('PS_SHOP_NAME'),
            //'heading' => $meta_title,
            //'meta_keywords' => isset($meta_keywords) ? $meta_keywords : null,
            //'meta_description' => $meta_description,
            //'current_friendly_url' => ((int)$n == (int)$nb_products) ? '#/show-all': '#'.$filter_block['current_friendly_url'],
            //'filters' => $filter_block['filters'],
            //'nbRenderedProducts' => (int)$nb_products,
            //'nbAskedProducts' => (int)$n
            'facets' => $facets,
            'redirect_url' => isset($search['cm_result']->Actions[0]->RedirectUrl) ? $search['cm_result']->Actions[0]->RedirectUrl : false
        );

        return Tools::jsonEncode($vars);
        //return $list;
    }

    public static function getFacets()
    {
        $ret = false;
        foreach ($_GET as $key => $val)
            if (substr($key, 0, 3) == 'cm_')
                $ret[$key] = Tools::getValue($key);
                //$ret[] = $val;
        return $ret;
    }
}