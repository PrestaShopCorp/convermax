<?php


class SearchController extends SearchControllerCore
{
    public function initContent()
    {
        $facets = Tools::getValue('cm_select');

        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
        $original_query = Tools::getValue('q');
        if ($this->ajax_search)
        {
            $searchResults = Search::find((int)(Tools::getValue('id_lang')), $query, 1, 10, 'position', 'desc', true);
            foreach ($searchResults as &$product)
                $product['product_link'] = $this->context->link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite']);
            die($searchResults);
        }

        if ($this->instant_search && !is_array($query))
        {
            $this->productSort();
            $this->n = abs((int)(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));
            $this->p = abs((int)(Tools::getValue('p', 1)));
            $search = Search::find($this->context->language->id, $query, 1, 10, 'position', 'desc');

            Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
            $nbProducts = $search['total'];
            $this->pagination($nbProducts);

            $this->addColorsToProductList($search['result']);

            $this->context->smarty->assign(array(
                'products' => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                'search_products' => $search['result'],
                'nbProducts' => $search['total'],
                'search_query' => $original_query,
                'instant_search' => $this->instant_search,
                'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));
        }
        //added facets check. If facets exists do don display empty query message
        elseif ((($query = Tools::getValue('search_query', Tools::getValue('ref'))) && !is_array($query)) || $facets) {
            $this->productSort();

            $this->n = abs((int)(Tools::getValue('n', (isset($this->context->cookie->nb_item_per_page) ? (int)$this->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE')))));

            $this->p = abs((int)(Tools::getValue('p', 1)));

            $original_query = $query;
            $query = Tools::replaceAccentedChars(urldecode($query));

            $search = Search::find($this->context->language->id, $query, $this->p, $this->n, $this->orderBy, $this->orderWay, false, true, null, $facets);
            foreach ($search['result'] as &$product)
                $product['link'] .= (strpos($product['link'], '?') === false ? '?' : '&') . 'search_query=' . urlencode($query) . '&results=' . (int)$search['total'];

            Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
            $nbProducts = $search['total'];

            $this->pagination($nbProducts);

            $this->addColorsToProductList($search['result']);

            if (stripos($search['cm_result']->State, 'nothing'))
                $cm_message = 'nothing found';
            elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
            {
                $cm_message = 'your request has been corrected to ' . $search['cm_result']->Corrections[0]->Replace;
                $original_query = $search['cm_result']->Corrections[0]->Replace;
            }
            else
                $cm_message = false;

            $this->context->smarty->assign(array(
                'products' => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                'search_products' => $search['result'],
                'nbProducts' => $search['total'],
                'search_query' => $original_query,
                'cm_message' => $cm_message,
                'related_searches' => $search['cm_result']->SeeAlsoQueries,
                'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));
        }
        elseif (($tag = urldecode(Tools::getValue('tag'))) && !is_array($tag))
        {
            $nbProducts = (int)(Search::searchTag($this->context->language->id, $tag, true));
            $this->pagination($nbProducts);
            $result = Search::searchTag($this->context->language->id, $tag, false, $this->p, $this->n, $this->orderBy, $this->orderWay);
            Hook::exec('actionSearch', array('expr' => $tag, 'total' => count($result)));

            $this->addColorsToProductList($result);

            $this->context->smarty->assign(array(
                'search_tag' => $tag,
                'products' => $result, // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                'search_products' => $result,
                'nbProducts' => $nbProducts,
                'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));
        }
        else
        {
            $this->context->smarty->assign(array(
                'products' => array(),
                'search_products' => array(),
                'pages_nb' => 1,
                'nbProducts' => 0));
        }
        $this->context->smarty->assign(array('add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'), 'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM')));

        $facets_params = '';
        foreach ($search['cm_result']->Facets as $facet)
        {
            //get slider selection from url
            if ($facet->IsRanged)
            {
                if ($facets[$facet->FieldName])
                {
                    $facets_params .= 'cm_params.sliders.' . $facet->FieldName . ' = []' . ";\r\n";
                    $facets_params .= 'cm_params.sliders.' . $facet->FieldName . '[0]' . ' = \'' . $facets[$facet->FieldName][0] . "';\r\n";
                }
                else
                {
                    $facets_params .= 'cm_params.sliders.' . $facet->FieldName . ' = []' . ";\r\n";
                    $facets_params .= 'cm_params.sliders.' . $facet->FieldName . '[0]' . ' = \'' . $facet->Values[0]->Term . "';\r\n";
                }
            }
            else
            {
                for ($i = 0; $i < count($facet->Values); $i++)
                {
                    if ($facet->Values[$i]->Selected == true)
                    {
                        $facets_params .= 'cm_params.facets.' . $facet->FieldName . ' = []' . ";\r\n";
                        $facets_params .= 'cm_params.facets.' . $facet->FieldName . '[' . $i . ']' . ' = \'' . $facet->Values[$i]->Term . "';\r\n";
                        $facets_params .= 'cm_params.facets_display.' . $facet->FieldName . '[' . $i . ']' . ' = \'' . $facet->Values[$i]->Value . "';\r\n";
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'facets' => $search['cm_result']->Facets,
            'query' => $search['cm_result']->Query,
            'pagenumber' => $this->p,
            'pagesize' => $this->n,
            'facets_params' => isset($facets_params) ? $facets_params : false,
            'redirect_url' => isset($search['cm_result']->Actions[0]->RedirectUrl) ? $search['cm_result']->Actions[0]->RedirectUrl : false,
        ));
        $this->setTemplate(_PS_MODULE_DIR_.'convermax/search.tpl');
        $front_controller = get_parent_class(get_parent_class($this));
        $front_controller::initContent();
    }
}