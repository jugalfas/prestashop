<?php
/**
 * Kahanit Framework for PrestaShop Modules by Kahanit
 *
 * Kahanit Framework by Kahanit(http://www.kahanit.com)
 * is licensed under a Creative Creative Commons Attribution-NoDerivatives 4.0
 * International License. Based on a work at http://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at http://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 */

include_once dirname(__FILE__) . '/Helpers.php';

if (!class_exists('KDProducts')) {
    /**
     * Class KDProducts
     */
    class KDProducts
    {
        public static function getProducts($id_lang = null, $id_category = null, $search = '', $start = 0, $limit = 10, $orderfld = 'p.id_product', $orderdir = 'ASC', $id_shop = null)
        {
            $search_filtered = KDHelpers::filterSearchQuery($search, array('id', 'name'));

            if ($id_lang == null) {
                $id_lang = Context::getContext()->language->id;
            }

            $sql = new DbQuery();
            $sql->select('DISTINCT p.`id_product`, p.`reference`, l.`link_rewrite`, l.`description_short`, p.`id_product` as id, l.name as text, p.`price` as price, image_shop.`id_image` id_image');
            $sql->from('product', 'p');
            $sql->leftJoin('product_shop', 's', 'p.`id_product` = s.`id_product`');
            $sql->leftJoin('product_lang', 'l', 'p.`id_product` = l.`id_product`');
            $sql->leftJoin('image', 'i', 'p.`id_product` = i.`id_product`');
            $sql->innerJoin('image_shop', 'image_shop', 'i.id_image = image_shop.id_image AND image_shop.cover=1 AND image_shop.id_shop='.(int)$id_shop);

            if ($id_category != null) {
                $sql->leftJoin('category_product', 'cp', 'p.`id_product` = cp.`id_product`');
                $sql->where('cp.`id_category` = ' . (int)$id_category);
            }

            if ($search_filtered['id'] != '') {
                $sql->where('p.`id_product` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['name'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['name']) . '%\' OR l.`description_short` LIKE \'%' . pSQL($search_filtered['name']) . '%\' OR p.`reference` LIKE \'%' . pSQL($search_filtered['name']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['name'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('l.`id_lang` = ' . (int)$id_lang . ' AND s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            if ($limit !== null && $start !== null) {
                $sql->limit((int)$limit, (int)$start);
            }

            if ($orderfld == 'entity_item_id') {
                $orderfld = 'p.id_product';
            }

            if ($orderfld == 'text') {
                $orderfld = 'l.name';
            }

            if ($orderfld != '' && $orderdir != '') {
                $sql->orderby(bqSQL($orderfld) . ' ' . bqSQL($orderdir));
            }

            $results =  Db::getInstance()->executeS($sql);


            foreach ($results as &$row)
        		{
              $row['id_image'] = Product::defineProductImage($row, $id_lang);
              $row['id_image'] = Context::getContext()->link->getImageLink($row['link_rewrite'], $row['id_image'], 'cart_default');
              $row['price'] = Product::getPriceStatic($row['id_product']);
              $row['price'] = Tools::ps_round($row['price'], 2);
              $row['price'] = Context::getContext()->currency->getSign().$row['price'];
            }

            return $results;
        }

        public static function getNumProducts($id_category = null, $search = '', $id_shop = null)
        {
            $search_filtered = KDHelpers::filterSearchQuery($search, array('id', 'name'));

            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT p.`id_product`) AS total');
            $sql->from('product', 'p');
            $sql->leftJoin('product_shop', 's', 'p.`id_product` = s.`id_product`');
            $sql->leftJoin('product_lang', 'l', 'p.`id_product` = l.`id_product`');

            if ($id_category != null) {
                $sql->leftJoin('category_product', 'cp', 'p.`id_product` = cp.`id_product`');
                $sql->where('cp.`id_category` = ' . (int)$id_category);
            }

            if ($search_filtered['id'] != '') {
                $sql->where('p.`id_product` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['name'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['name']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['name'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            return Db::getInstance()->getValue($sql);
        }
    }
}
