<?php

//require_once(_PS_MODULE_DIR_.'convermax/ConvermaxAPI.php');
require_once('/home/demo/prestashop/www/modules/convermax/ConvermaxAPI.php');

Class Search extends SearchCore
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
            // Do it even if you already know the product id in order to be sure that it exists and it needs to be indexed
            $products = $db->executeS('
				SELECT p.id_product
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE product_shop.visibility IN ("both", "search")
				AND product_shop.`active` = 1
				AND '.($id_product ? 'p.id_product = '.(int)$id_product : 'product_shop.indexed = 0')
            );

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


        // Every fields are weighted according to the configuration in the backend
        $weight_array = array(
            'pname' => Configuration::get('PS_SEARCH_WEIGHT_PNAME'),
            'reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'pa_reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'ean13' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'upc' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'description_short' => Configuration::get('PS_SEARCH_WEIGHT_SHORTDESC'),
            'description' => Configuration::get('PS_SEARCH_WEIGHT_DESC'),
            'cname' => Configuration::get('PS_SEARCH_WEIGHT_CNAME'),
            'mname' => Configuration::get('PS_SEARCH_WEIGHT_MNAME'),
            'tags' => Configuration::get('PS_SEARCH_WEIGHT_TAG'),
            'attributes' => Configuration::get('PS_SEARCH_WEIGHT_ATTRIBUTE'),
            'features' => Configuration::get('PS_SEARCH_WEIGHT_FEATURE')
        );



        // Retrieve the number of languages
        //$total_languages = count(Language::getLanguages(false));
        $languages = Language::getLanguages();
        for ($i = 0; $i < count($languages); $i++) {
            if ($languages[$i]['iso_code'] == 'en') {
                $id_lang = $languages[$i]['id_lang'];
                break;
            } else {
                return false;
            }
        }



        $convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'), Configuration::get('CONVERMAX_HASH'), Configuration::get('CONVERMAX_CERT'));
        if (!$convermax->batchStart())
            return false;
        //$id_lang = 1;
        $product = new Product();
        $products = $product -> getProducts($id_lang, 0, 0, 'id_product', 'ASC', false, true);
        $products = Product::getProductsProperties((int)$id_lang, $products);

        // Products are processed 50 by 50 in order to avoid overloading MySQL
        /*while (($products = Search::getProductsToIndex($total_languages, $id_product, 50, $weight_array)) && (count($products) > 0)) {
            */$products_array = array();




        // Now each non-indexed product is processed one by one, langage by langage
       /* $j = 0;
        foreach ($products as $product) {
            $products[$j]['_CatalogName'] = 'products';
            if ($products[$j]['features'])
            {
                $k = 0;
                foreach ($products[$j]['features'] as $feature) {
                    $products[$j][$feature['name']] = $feature['value'];
                    unset($products[$j]['features']);
                    $k++;
                }
            }*/

            //unset($products[$j]['attachments']);
            //unset($products[$j]['packItems']);
            //unset($products[$j]['specific_prices']);

            /*foreach ($products[$j] as $key => $value)
                if (is_array($value))
                    unset($products[$j][$key]);*/


           /* $j++;

            if (!in_array($product['id_product'], $products_array))
                $products_array[] = (int)$product['id_product'];
        }*/
//
        for ($i = 0; $i < count($products); $i++)
        {
            $products[$i]['_CatalogName'] = 'products';
            if ($products[$i]['features'])
            {
                //$k = 0;
                foreach ($products[$i]['features'] as $feature)
                {
                    $products[$i][$feature['name']] = $feature['value'];
                    //unset($products[$i]['features']);
                    //$k++;
                }
                unset($products[$i]['features']);
            }

            $img_id = Product::getCover($products[$i]['id_product']);
            $link = new Link();
            $products[$i]['img_link'] = 'http://' . $link->getImageLink($products[$i]['link_rewrite'], $img_id['id_image'], 'small_default');

            if (!in_array($products[$i]['id_product'], $products_array))
                $products_array[] = (int)$products[$i]['id_product'];
        }

        if (!$convermax->batchAdd($products))
            return false;
        //Search::setProductsAsIndexed($products_array);

        // One last save is done at the end in order to save what's left
        //Search::saveIndex($query_array3);
        /*}*/
        if (!$convermax->batchEnd())
            return false;
        Search::setProductsAsIndexed($products_array);
        return true;
    }

    public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
                                $order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null, $facets = null)
    {

        if (!$context)
            $context = Context::getContext();
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
            return false;
        /*
                // TODO : smart page management
                if ($page_number < 1) $page_number = 1;
                if ($page_size < 1) $page_size = 1;

                if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
                    return false;

                $intersect_array = array();
                $score_array = array();
                $words = explode(' ', Search::sanitize($expr, $id_lang, false, $context->language->iso_code));

                foreach ($words as $key => $word)
                    if (!empty($word) && strlen($word) >= (int)Configuration::get('PS_SEARCH_MINWORDLEN'))
                    {
                        $word = str_replace('%', '\\%', $word);
                        $word = str_replace('_', '\\_', $word);
                        $intersect_array[] = 'SELECT si.id_product
                            FROM '._DB_PREFIX_.'search_word sw
                            LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
                            WHERE sw.id_lang = '.(int)$id_lang.'
                                AND sw.id_shop = '.$context->shop->id.'
                                AND sw.word LIKE
                            '.($word[0] == '-'
                                ? ' \''.pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
                                : '\''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
                            );

                        if ($word[0] != '-')
                            $score_array[] = 'sw.word LIKE \''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'';
                    }
                    else
                        unset($words[$key]);

                if (!count($words))
                    return ($ajax ? array() : array('total' => 0, 'result' => array()));

                $score = '';
                if (count($score_array))
                    $score = ',(
                        SELECT SUM(weight)
                        FROM '._DB_PREFIX_.'search_word sw
                        LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
                        WHERE sw.id_lang = '.(int)$id_lang.'
                            AND sw.id_shop = '.$context->shop->id.'
                            AND si.id_product = p.id_product
                            AND ('.implode(' OR ', $score_array).')
                    ) position';

                $sql_groups = '';
                if (Group::isFeatureActive())
                {
                    $groups = FrontController::getCurrentCustomerGroups();
                    $sql_groups = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
                }

                $results = $db->executeS('
                SELECT cp.`id_product`
                FROM `'._DB_PREFIX_.'category_product` cp
                '.(Group::isFeatureActive() ? 'INNER JOIN `'._DB_PREFIX_.'category_group` cg ON cp.`id_category` = cg.`id_category`' : '').'
                INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
                INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
                '.Shop::addSqlAssociation('product', 'p', false).'
                WHERE c.`active` = 1
                AND product_shop.`active` = 1
                AND product_shop.`visibility` IN ("both", "search")
                AND product_shop.indexed = 1
                '.$sql_groups);

                $eligible_products = array();
                foreach ($results as $row)
                    $eligible_products[] = $row['id_product'];
                foreach ($intersect_array as $query)
                {
                    $eligible_products2 = array();
                    foreach ($db->executeS($query) as $row)
                        $eligible_products2[] = $row['id_product'];

                    $eligible_products = array_intersect($eligible_products, $eligible_products2);
                    if (!count($eligible_products))
                        return ($ajax ? array() : array('total' => 0, 'result' => array()));
                }

                $eligible_products = array_unique($eligible_products);
        */

        //$context->shop->id;

        if ($order_way == 'desc')
            $order_desc = true;
        else
            $order_desc = false;

        $convermax = new ConvermaxAPI(Configuration::get('CONVERMAX_URL'), Configuration::get('CONVERMAX_HASH'));
        $search_results = $convermax->search($expr, $page_number - 1, $page_size, $facets, $order_by, $order_desc);
        $product_pool = '';
        //foreach ($eligible_products as $id_product)
        foreach ($search_results->Items as $item)
            //if ($id_product)
            //$product_pool .= (int)$id_product.',';
            $product_pool .= (int)$item->id_product . ',';
        //if ($order_by == 'position')
            $product_order_by = rtrim($product_pool, ',');


        if (empty($product_pool))
            return ($ajax ? array() : array('total' => 0, 'result' => array()));
        $product_pool = ((strpos($product_pool, ',') === false) ? (' = '.(int)$product_pool.' ') : (' IN ('.rtrim($product_pool, ',').') '));

        //sort by convermax result
        $order = 'FIELD(p.`id_product`, '.$product_order_by.')';
        $order_way = 'asc';

        if ($ajax)
        {
            /*$sql = 'SELECT DISTINCT p.id_product, pl.name pname, cl.name cname,
						cl.link_rewrite crewrite, pl.link_rewrite prewrite '.$score.'
					FROM '._DB_PREFIX_.'product p
					INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
					)
					'.Shop::addSqlAssociation('product', 'p').'
					INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (
						product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
					)
					WHERE p.`id_product` '.$product_pool.'
					ORDER BY '.$order.'asc';
					//ORDER BY position DESC LIMIT 10';
            return $db->executeS($sql);*/
            //$autocomplete = $convermax->autocomplete($expr);
            return $convermax->autocomplete($expr);
        }

        if (strpos($order_by, '.') > 0)
        {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
        }
        $alias = '';
        //if ($order_by == 'position')
        //{
            //$product_order_by = rtrim($product_pool, ',');
            //$order = 'FIELD(p.`id_product`, '.$product_order_by.')';
            //$order_way = 'asc';
        //}
        /*if ($order_by == 'price')
            $alias = 'product_shop.';
        else if ($order_by == 'date_upd')
            $alias = 'p.';*/
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` manufacturer_name '.$score.', MAX(product_attribute_shop.`id_product_attribute`) id_product_attribute,
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
				'.($order ? 'ORDER BY  '.$order : '');/*.($order_way ? ' '.$order_way : ''); .'
				LIMIT '.(int)(($page_number - 1) * $page_size).','.(int)$page_size;*/
        $result = $db->executeS($sql);


        /*
        $sql = 'SELECT COUNT(*)
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` '.$product_pool;
        $total = $db->getValue($sql);
        */
        $total = $search_results->TotalHits;

        if (!$result)
            $result_properties = false;
        else
            $result_properties = Product::getProductsProperties((int)$id_lang, $result);

        return array('total' => $total,'result' => $result_properties, 'cm_result' => $search_results);
    }
}