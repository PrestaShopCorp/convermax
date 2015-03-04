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

if (Module::isInstalled('convermax'))
	require_once(_PS_MODULE_DIR_.'convermax/ConvermaxAPI.php');

class Search extends SearchCore
{
	public static function indexation($full = false, $id_product = false)
	{
		$db = Db::getInstance();

		if ($id_product)
			$full = false;

		if ($full)
		{
			$db->execute('TRUNCATE '._DB_PREFIX_.'search_index');
			$db->execute('TRUNCATE '._DB_PREFIX_.'search_word');
			ObjectModel::updateMultishopTable('Product', array('indexed' => 0));
		}
		else
		{
			$products = $db->executeS('
				SELECT p.id_product
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE product_shop.visibility IN ("both", "search")
				AND product_shop.`active` = 1
				AND '.($id_product ? 'p.id_product = '.(int)$id_product : 'product_shop.indexed = 0'));

			$ids = array();
			if ($products)
				foreach ($products as $product)
					$ids[] = (int)$product['id_product'];
			if (count($ids))
			{
				$db->execute('DELETE FROM '._DB_PREFIX_.'search_index WHERE id_product IN ('.implode(',', $ids).')');
				ObjectModel::updateMultishopTable('Product', array('indexed' => 0), 'a.id_product IN ('.implode(',', $ids).')');
			}
		}

		$languages = Language::getLanguages();
		$languages_count = count($languages);
		for ($i = 0; $i < $languages_count; $i++)
		{
			if ($languages[$i]['iso_code'] == 'en')
			{
				$id_lang = $languages[$i]['id_lang'];
				break;
			}
			else
				return false;
		}

		$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'), Configuration::get('CONVERMAX_CERT'));
		if (!$convermax->batchStart())
			return false;
		while ($products = Search::getProductsToIndex($id_lang, $id_product, 50))
		{
			if (count($products) == 0)
				break;
			$products_array = array();
			$products_count = count($products);

			for ($i = 0; $i < $products_count; $i++)
			{
				$products[$i]['_CatalogName'] = 'products';
				if ($products[$i]['features'])
				{
					foreach ($products[$i]['features'] as $feature)
						$products[$i][$feature['name']] = $feature['value'];
					unset($products[$i]['features']);
				}

				$img_id = Product::getCover($products[$i]['id_product']);
				$link = new Link();
				$fix = 'http:/';
				$products[$i]['img_link'] = $fix.'/'.$link->getImageLink($products[$i]['link_rewrite'], $img_id['id_image'], ImageType::getFormatedName('small'));

				$cat_full = Product::getProductCategoriesFull($products[$i]['id_product']);
				$full_category = '';
				$j = 0;
				foreach ($cat_full as $cat)
				{
					if ($j > 0)
						$full_category .= $cat['name'].($j == (count($cat_full) - 1) ? '' : '>');
					$j++;
				}

				$products[$i]['category_full'] = $full_category;

				if (!in_array($products[$i]['id_product'], $products_array))
					$products_array[] = (int)$products[$i]['id_product'];
			}

			if (!$convermax->batchAdd($products))
				return false;

			Search::setProductsAsIndexed($products_array);
		}
		if (!$convermax->batchEnd())
			return false;
		return true;
	}

	public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
								$order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null, $facets = null)
	{
		$convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'));
		if ($ajax)
			return $convermax->autocomplete($expr);
		unset($use_cookie);
		if (!$context)
			$context = Context::getContext();
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
			return false;

		if ($order_way == 'desc')
			$order_desc = true;
		else
			$order_desc = false;

		$search_results = $convermax->search($expr, $page_number - 1, $page_size, $facets, $order_by, $order_desc);
		$product_pool = '';
		$items = 'Items';
		foreach ($search_results->{$items} as $item)
			$product_pool .= (int)$item->id_product.',';
		$product_order_by = rtrim($product_pool, ',');

		if (empty($product_pool))
			return ($ajax ? array() : array('total' => 0, 'result' => array()));
		$product_pool = ((strpos($product_pool, ',') === false) ? (' = '.(int)$product_pool.' ') : (' IN ('.rtrim($product_pool, ',').') '));

		//sort by convermax result
		$order = 'FIELD(p.`id_product`, '.$product_order_by.')';

		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` manufacturer_name ,
			 MAX(product_attribute_shop.`id_product_attribute`) id_product_attribute,
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						NOW(),
						INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
					)
				) > 0 new
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa	ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
			Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				WHERE p.`id_product` '.$product_pool.'
				GROUP BY product_shop.id_product
				'.($order ? 'ORDER BY  '.$order : '');
		$result = $db->executeS($sql);

		$total_hits = 'TotalHits';
		$total = $search_results->{$total_hits};

		if (!$result)
			$result_properties = false;
		else
			$result_properties = Product::getProductsProperties((int)$id_lang, $result);

		return array('total' => $total,'result' => $result_properties, 'cm_result' => $search_results);
	}

	protected static function getProductsToIndex($id_lang, $id_product = false, $limit = 50)
	{
		$sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `'._DB_PREFIX_.'product` p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = p.`id_supplier`)
				WHERE pl.`id_lang` = '.(int)$id_lang.
			' AND product_shop.visibility IN ("both", "search")
			'.($id_product ? 'AND p.id_product = '.(int)$id_product : '').'
			AND product_shop.indexed = 0
			AND product_shop.`active` = 1
			LIMIT '.(int)$limit;

		$products = Db::getInstance()->executeS($sql);
		return Product::getProductsProperties($id_lang, $products);
	}
}