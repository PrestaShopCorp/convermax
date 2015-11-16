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

class Cmsearch
{
    public static function indexation($id_product = false, $update = false)
    {
        $id_lang = Cmsearch::getLangId();
        $convermax = new ConvermaxAPI();
        if (!$id_product) {
            if (!$convermax->batchStart()) {
                return false;
            }
        }
        $start = 0;
        while ($products = Cmsearch::getProductsToIndex($id_lang, $start, 200, $id_product)) {
            $start = $start + 200;
            if (count($products) == 0) {
                break;
            }
            $products_array = array();
            $products_count = count($products);
            for ($i = 0; $i < $products_count; $i++) {
                if ($products[$i]['features']) {
                    foreach ($products[$i]['features'] as $feature) {
                        $f_name = 'f_' . $convermax->sanitize($feature['name']);
                        $products[$i][$f_name][] = $feature['value'];
                    }
                    unset($products[$i]['features']);
                }
                foreach ($products[$i] as $key => $val) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            if (is_array($v)) {
                                unset($products[$i][$key]);
                            }
                        }
                    }
                }
                $img_id = Product::getCover($products[$i]['id_product']);
                $link = new Link();
                $img_link = $link->getImageLink($products[$i]['link_rewrite'], $img_id['id_image'], ImageType::getFormatedName('small'));
                $products[$i]['img_link'] = str_replace(Tools::getHttpHost(), '', $img_link);
                $products[$i]['link'] = str_replace(Tools::getHttpHost(true), '', $products[$i]['link']);

                $cat_full = Product::getProductCategoriesFull($products[$i]['id_product']);
                $category_full = array();
                $home_category = Configuration::get('PS_HOME_CATEGORY');
                foreach ($cat_full as $cat) {
                    $category = new Category($cat['id_category']);
                    $categories = $category->getParentsCategories();
                    $c_full = array();
                    foreach ($categories as $cats) {
                        if ($cats['id_category'] != $home_category) {
                            $c_full[] = $cats['name'];
                        }
                    }
                    if (!empty($c_full)) {
                        $category_full[] = implode('>', array_reverse($c_full));
                    }
                }
                $products[$i]['category_full'] = $category_full;

                $products[$i]['ean13'] = array($products[$i]['ean13']);
                $products[$i]['upc'] = array($products[$i]['upc']);
                $products[$i]['reference'] = array($products[$i]['reference']);
                $products[$i]['supplier_reference'] = array($products[$i]['supplier_reference']);
                $product = new Product($products[$i]['id_product']);
                $combinations = Product::getProductAttributesIds($products[$i]['id_product']);
                $attribs = array();
                $ean13 = array();
                $upc = array();
                $reference = array();
                $supplier_reference = array();
                foreach ($combinations as $combination) {
                    $comb = $product->getAttributeCombinationsById($combination['id_product_attribute'], $id_lang);
                    foreach ($comb as $com) {
                        $a_name = 'a_'.$convermax->sanitize($com['group_name']);
                        if ($com['is_color_group'] == 1) {
                            $attribs[$a_name][] = $com['id_attribute'];
                        } else {
                            $attribs[$a_name][] = $com['attribute_name'];
                        }
                        if (!empty($com['ean13'])) {
                            $ean13[] = $com['ean13'];
                        }
                        if (!empty($com['upc'])) {
                            $upc[] = $com['upc'];
                        }
                        if (!empty($com['reference'])) {
                            $reference[] = $com['reference'];
                        }
                        if (!empty($com['supplier_reference'])) {
                            $supplier_reference[] = $com['supplier_reference'];
                        }
                    }
                }
                foreach ($attribs as $k => $v) {
                    $products[$i][$k] = array_values(array_unique($v));
                }
                if (!empty($ean13)) {
                    $products[$i]['ean13'] = array_merge((array)$products[$i]['ean13'], array_unique($ean13));
                }
                if (!empty($upc)) {
                    $products[$i]['upc'] = array_merge((array)$products[$i]['upc'], array_unique($upc));
                }
                if (!empty($reference)) {
                    $products[$i]['reference'] = array_merge((array)$products[$i]['reference'], array_unique($reference));
                }
                if (!empty($supplier_reference)) {
                    $products[$i]['supplier_reference'] = array_merge((array)$products[$i]['supplier_reference'], array_unique($supplier_reference));
                }

                if (!in_array($products[$i]['id_product'], $products_array)) {
                    $products_array[] = (int)$products[$i]['id_product'];
                }
            }
            if ($update) {
                if (!$convermax->update($products)) {
                    return false;
                }
            } elseif ($id_product) {
                if (!$convermax->add($products)) {
                    return false;
                }
            } elseif (!$convermax->batchAdd($products)) {
                return false;
            }
            if ($id_product) {
                break;
            }
        }
        if (!$id_product) {
            if (!$convermax->batchEnd()) {
                return false;
            }
        }
        return true;
    }

    public static function deleteProduct($id_product)
    {
        $convermax = new ConvermaxAPI();
        if (!$convermax->delete($id_product)) {
            return false;
        }
        return true;
    }

    public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position', $order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null, $facets = null)
    {
        $convermax = new ConvermaxAPI();
        if ($ajax) {
            return $convermax->autocomplete($expr);
        }
        unset($use_cookie);
        if (!$context) {
            $context = Context::getContext();
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            return false;
        }

        if ($order_way == 'desc') {
            $order_desc = true;
        } else {
            $order_desc = false;
        }

        $search_results = $convermax->search($expr, $page_number - 1, $page_size, $facets, $order_by, $order_desc);
        if (!$search_results) {
            return false;
        }
        $product_pool = '';
        $items = 'Items';
        foreach ($search_results->{$items} as $item) {
            $product_pool .= (int)$item->id_product . ',';
        }
        $product_order_by = rtrim($product_pool, ',');

        if (empty($product_pool)) {
            return array('total' => 0, 'result' => array(), 'cm_result' => $search_results);
        }
        $product_pool = ((strpos($product_pool, ',') === false) ? (' = ' . (int)$product_pool . ' ') : (' IN (' . rtrim($product_pool, ',') . ') '));


        //sort by convermax result
        $order = 'FIELD(p.`id_product`, ' . $product_order_by . ')';

        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` manufacturer_name ,
			 MAX(product_attribute_shop.`id_product_attribute`) id_product_attribute,
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						NOW(),
						INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
					)
				) > 0 new
				FROM ' . _DB_PREFIX_ . 'product p
				' . Shop::addSqlAssociation('product', 'p') . '
				INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				)
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa	ON (p.`id_product` = pa.`id_product`)
				' . Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1') . '
				' . Product::sqlStock('p', 'product_attribute_shop', false, $context->shop) . '
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
            Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang . ')
				WHERE p.`id_product` ' . $product_pool . '
				GROUP BY product_shop.id_product
				' . ($order ? 'ORDER BY  ' . $order : '');
        $result = $db->executeS($sql);

        $total_hits = 'TotalHits';
        $total = $search_results->{$total_hits};

        if (!$result) {
            $result_properties = false;
        } else {
            $result_properties = Product::getProductsProperties((int)$id_lang, $result);
        }

        $count = count($search_results->Facets);
        for ($i = 0; $i < $count; $i++) {
            if ($search_results->Facets[$i]->FieldName == 'a_Color') {
                $c = count($search_results->Facets[$i]->Values);
                for ($j = 0; $j < $c; $j++) {
                    $colors = Cmsearch::getColor($search_results->Facets[$i]->Values[$j]->Term);
                    $search_results->Facets[$i]->Values[$j]->Value = $colors['name'];
                    $search_results->Facets[$i]->Values[$j]->ColorCode = empty($colors['color']) ? '#FFFFFF' : $colors['color'];
                }
            }
        }

        return array('total' => $total, 'result' => $result_properties, 'cm_result' => $search_results);
    }

    public static function getProductsToIndex($id_lang, $start, $limit, $id_product = false)
    {
        Cmproduct::flushCache();
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)
				WHERE pl.`id_lang` = ' . (int)$id_lang .
            ' AND product_shop.visibility IN ("both", "search")
			' . ($id_product ? 'AND p.id_product = ' . (int)$id_product : '') . '
			AND product_shop.`active` = 1
			ORDER BY p.id_product ASC
			' . ($id_product ? '' : 'LIMIT ' . (int)$start . ',' . (int)$limit);

        $products = Db::getInstance()->executeS($sql);
        return Product::getProductsProperties($id_lang, $products);
    }

    public static function getProductsCount()
    {
        $id_lang = Cmsearch::getLangId();

        $sql = 'SELECT COUNT(*)
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)
				WHERE pl.`id_lang` = ' . (int)$id_lang .
            ' AND product_shop.visibility IN ("both", "search")
			AND product_shop.`active` = 1
			ORDER BY p.id_product ASC';

        $products = Db::getInstance()->executeS($sql);
        return $products[0]['COUNT(*)'];
    }

    public static function getLangId()
    {
        $languages = Language::getLanguages();
        $languages_count = count($languages);
        for ($i = 0; $i < $languages_count; $i++) {
            if ($languages[$i]['iso_code'] == 'en' || $languages[$i]['iso_code'] == 'gb') {
                $id_lang = $languages[$i]['id_lang'];
                break;
            } else {
                $id_lang = 1;
            }
        }
        return $id_lang;
    }

    public static function getFields()
    {
        $fields = array(
            'id_product' => '',
            'id_supplier' => '',
            'id_manufacturer' => '',
            'id_category_default' => '',
            'id_shop_default' => '',
            'id_tax_rules_group' => '',
            'on_sale' => '',
            'online_only' => '',
            'ean13' => '',
            'upc' => '',
            'ecotax' => '',
            'quantity' => '',
            'minimal_quantity' => '',
            'price' => '',
            'wholesale_price' => '',
            'unity' => '',
            'unit_price_ratio' => '',
            'additional_shipping_cost' => '',
            'reference' => '',
            'supplier_reference' => '',
            'location' => '',
            'width' => '',
            'height' => '',
            'depth' => '',
            'weight' => '',
            'out_of_stock' => '',
            'quantity_discount' => '',
            'customizable' => '',
            'uploadable_files' => '',
            'text_fields' => '',
            'active' => '',
            'redirect_type' => '',
            'id_product_redirected' => '',
            'available_for_order' => '',
            'available_date' => '',
            'condition' => '',
            'show_price' => '',
            'indexed' => '',
            'visibility' => '',
            'cache_is_pack' => '',
            'cache_has_attachments' => '',
            'is_virtual' => '',
            'cache_default_attribute' => '',
            'date_add' => '',
            'date_upd' => '',
            'advanced_stock_management' => '',
            'pack_stock_type' => '',
            'id_shop' => '',
            'id_lang' => '',
            'description' => '',
            'description_short' => '',
            'link_rewrite' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'meta_title' => '',
            'name' => '',
            'available_now' => '',
            'available_later' => '',
            'manufacturer_name' => '',
            'supplier_name' => '',
            'allow_oosp' => '',
            'id_product_attribute' => '',
            'category' => '',
            'link' => '',
            'attribute_price' => '',
            'price_tax_exc' => '',
            'price_without_reduction' => '',
            'reduction' => '',
            'specific_prices' => '',
            'quantity_all_versions' => '',
            'id_image' => '',
            'virtual' => '',
            'pack' => '',
            'nopackprice' => '',
            'customization_required' => '',
            'rate' => '',
            'tax_name' => '',
            'img_link' => '',
            'category_full' => ''
        );
        $id_lang = Cmsearch::getLangId();
        $sql = 'SELECT name
				FROM `' . _DB_PREFIX_ . 'feature_lang`
				WHERE `id_lang` = ' . (int)$id_lang;
        $features = Db::getInstance()->executeS($sql);
        foreach ($features as $feature) {
            $fields['f_' . $feature['name']] = '';
        }

        return $fields;
    }

    public static function getColor($id_attribute) {
        $sql = 'SELECT name, color
        FROM ps_attribute, ps_attribute_lang
        WHERE ps_attribute.id_attribute = ps_attribute_lang.id_attribute
        AND id_lang = '.Cmsearch::getLangId().'
        AND ps_attribute.id_attribute = '.$id_attribute;
        $row = Db::getInstance()->executeS($sql);
        return $row[0];
    }
}
