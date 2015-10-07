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

class ConvermaxSearchModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
        if (Tools::getValue('ajax'))
            return;

        $facets = Tools::getValue('cm_select');
        $query = Tools::getValue('search_query') ? Tools::getValue('search_query') : ' ';
        $this->productSort();

        $this->n = abs((int)Tools::getValue('n', (isset($this->context->cookie->nb_item_per_page) ?
            (int)$this->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE'))));

        $this->p = abs((int)Tools::getValue('p', 1));

        $original_query = $query;
        $query = Tools::replaceAccentedChars(urldecode($query));

        $search = Cmsearch::find($this->context->language->id, $query, $this->p, $this->n, $this->orderBy, $this->orderWay, false, true, null, $facets);
        if($search)
        {
			if (isset($search['cm_result']->Actions[0]->RedirectUrl) && !Tools::getValue('ajax'))
                die(header('Location: '.$search['cm_result']->Actions[0]->RedirectUrl));
			$position = 1;
			foreach ($search['result'] as &$product)
			{
				$product['link'] .= (strpos($product['link'], '?') === false ? '?' : '&').'search_query='.urlencode($query).'&results='.(int)$search['total'].
					'&pid='.(int)$product['id_product'].'&position='.$position.'&page='.$this->p.'&num='.$this->n;
				$position++;
			}

			Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
			$nbProducts = $search['total'];

			$this->pagination($nbProducts);

			$this->addColorsToProductList($search['result']);

			if (stripos($search['cm_result']->State, 'nothing'))
				$cm_message = 'nothing found';
			elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
			{
				if (!empty($search['cm_result']->Query))
				{
					$cm_message = 'your request has been corrected to '.$search['cm_result']->Query;
					$original_query = $search['cm_result']->Query;
				}
				else
					$cm_message = 'nothing found';
			}
			else
				$cm_message = false;

			$this->context->smarty->assign(array(
				'search_products' => $search['result'],
				'nbProducts' => $search['total'],
				'search_query' => $original_query,
				'cm_message' => $cm_message,
				'related_searches' => $search['cm_result']->SeeAlsoQueries,
				'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
            ));

            $facets_params = '';
            $is_ranged = 'IsRanged';
            $field_name = 'FieldName';
            $values = 'Values';
            $display_name = 'DisplayName';
            foreach ($search['cm_result']->Facets as $facet)
            {
                //get slider selection from url
                if ($facet->{$is_ranged})
                {
                    if ($facets[$facet->{$field_name}])
                    {
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . ' = []' . ";\r\n";
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . '[0] = "' . $facets[$facet->{$field_name}][0] . "\";\r\n";
                    }
                    else
                    {
                        $rangemin = preg_replace('|TO .*\]|', '', $facet->{$values}[0]->Term);
                        $rangemax = preg_replace('|\[.*? |', '', $facet->{$values}[count($facet->{$values}) - 1]->Term);
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . ' = []' . ";\r\n";
                        $facets_params .= 'cm_params.sliders.' . $facet->{$field_name} . '[0] = "' . $rangemin . $rangemax . "\";\r\n";
                    }
                }
                else
                {
                    $values_count = count($facet->{$values});
                    for ($i = 0; $i < $values_count; $i++)
                    {
                        if ($facet->{$values}[$i]->Selected == true)
                        {
                            $facets_params .= 'cm_params.facets.' . $facet->{$field_name} . ' = []' . ";\r\n";
                            $facets_params .= 'cm_params.facets.' . $facet->{$field_name} . '[' . $i . '] = "' . $facet->{$values}[$i]->Term . "\";\r\n";
                            $facets_params .= 'cm_params.facets_display.' . $facet->{$field_name} . ' = "' . $facet->{$display_name} . "\";\r\n";
                        }
                    }
                }
            }

            $this->context->smarty->assign(array(
                'facets' => $search['cm_result']->Facets,
                'query' => $search['cm_result']->OriginalQuery,
                'pagenumber' => $this->p,
                'pagesize' => $this->n,
                'facets_params' => isset($facets_params) ? $facets_params : false
            ));
		}
		else
		{
			$this->context->smarty->assign(array(
                'search_products' => array(),
                'nbProducts' => 0,
                'search_query' => $original_query,
                'query' => $query,
                'pagenumber' => $this->p,
                'pagesize' => $this->n
            ));
		}
		$this->context->smarty->assign(array(
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM')));

		$this->setTemplate('search.tpl');
		parent::initContent();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(_THEME_CSS_DIR_.'product_list.css');
	}

	public function displayAjax()
	{
		$query = Tools::getValue('search_query');

		$original_query = $query;
		$query = Tools::replaceAccentedChars(urldecode($query));

		$this->productSort();
		$this->n = abs((int)Tools::getValue('n', (isset($this->context->cookie->nb_item_per_page) ?
			(int)$this->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE'))));
		$this->p = abs((int)Tools::getValue('p', 1));

		$facets = Tools::getValue('cm_select');

		$search = Cmsearch::find($this->context->language->id, $query, $this->p, $this->n, $this->orderBy,
			$this->orderWay, false, true, null, $facets);
        if($search)
        {
            $position = 1;
            foreach ($search['result'] as &$product)
            {
                $product['link'] .= (strpos($product['link'], '?') === false ? '?' : '&') . 'search_query=' . urlencode($query) . '&results=' . (int)$search['total'] .
                    '&pid=' . (int)$product['id_product'] . '&position=' . $position . '&page=' . $this->p . '&num=' . $this->n;
                $position++;
            }

            Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
            $nbProducts = $search['total'];
            $this->pagination($nbProducts);
            $this->addColorsToProductList($search['result']);

            if (stripos($search['cm_result']->State, 'nothing'))
                $cm_message = 'nothing found';
            elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
            {
                if (!empty($search['cm_result']->Query))
                    $cm_message = 'your request has been corrected to ' . $search['cm_result']->Query;
                else
                    $cm_message = 'nothing found';
            }
            else
                $cm_message = false;

            $this->context->smarty->assign(array(
                 'search_products' => $search['result'],
                'nbProducts' => $search['total'],
                'search_query' => !empty($search['cm_result']->OriginalQuery) ? $search['cm_result']->OriginalQuery : ' ',
                'cm_message' => $cm_message,
                'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
            ));

            $list = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'convermax/views/templates/front/search.tpl');

            $this->context->smarty->assign(array(
                'facets' => $search['cm_result']->Facets,
                'query' => $search['cm_result']->Query,
                'pagenumber' => $this->p,
                'pagesize' => $this->n,
            ));

            $facets = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'convermax/views/templates/hook/facet.tpl');

            $vars = array(
                'productList' => utf8_encode($list),
                'facets' => $facets,
                'redirect_url' => isset($search['cm_result']->Actions[0]->RedirectUrl) ? $search['cm_result']->Actions[0]->RedirectUrl : false
            );
        }
        else
        {
            $this->context->smarty->assign(array(

                'search_products' => $search['result'],
                'nbProducts' => $search['total'],
                'search_query' => !empty($original_query) ? $original_query : ' ',
                'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
            ));

            $list = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'convermax/views/templates/front/search.tpl');
            $vars = array(
                'productList' => utf8_encode($list),
                'facets' => array(),
                'redirect_url' => false
            );
        }

		echo Tools::jsonEncode($vars);
	}
}