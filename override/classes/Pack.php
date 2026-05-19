<?php
class Pack extends PackCore
{
    /*
    * module: pga
    * date: 2026-04-23 16:47:10
    * version: 1.8.4
    */
    public static function getItemTable($id_product, $id_lang, $full = false)
    {
        if (!Pack::isFeatureActive()) {
            return array();
        }
        if (Configuration::get('pga_hide_in_packs') == 1) {
            return parent::getItemTable($id_product, $id_lang, $full);
        }
        $context = Context::getContext();
        $sql = 'SELECT p.*, product_shop.*, pl.*, image_shop.`id_image` id_image, il.`legend`, cl.`name` AS category_default, a.quantity AS pack_quantity, product_shop.`id_category_default`, a.id_product_pack, a.id_product_attribute_item
				FROM `'._DB_PREFIX_.'pack` a
				LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = a.id_product_item
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON p.id_product = pl.id_product
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
					ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				INNER JOIN `'._DB_PREFIX_.'product_shop` AS product_shop ON p.`id_product` = product_shop.`id_product`
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
				WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
				AND a.`id_product_pack` = '.(int)$id_product.'
				GROUP BY a.`id_product_item`, a.`id_product_attribute_item`';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($result as &$line) {
            if (Combination::isFeatureActive() && isset($line['id_product_attribute_item']) && $line['id_product_attribute_item']) {
                $line['cache_default_attribute'] = $line['id_product_attribute'] = $line['id_product_attribute_item'];
                $sql = 'SELECT agl.`name` AS group_name, al.`name` AS attribute_name,  pai.`id_image` AS id_product_attribute_image
				FROM `'._DB_PREFIX_.'product_attribute` pa
				'.Shop::addSqlAssociation('product_attribute', 'pa').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = '.$line['id_product_attribute_item'].'
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)Context::getContext()->language->id.')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)Context::getContext()->language->id.')
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_image` pai ON ('.$line['id_product_attribute_item'].' = pai.`id_product_attribute`)
				WHERE pa.`id_product` = '.(int)$line['id_product'].' AND pa.`id_product_attribute` = '.$line['id_product_attribute_item'].'
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`';
                $attr_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                if (isset($attr_name[0]['id_product_attribute_image']) && $attr_name[0]['id_product_attribute_image']) {
                    $line['id_image'] = $attr_name[0]['id_product_attribute_image'];
                }
                $line['name'] .= "\n";
                foreach ($attr_name as $value) {
                    $line['name'] .= ' '.$value['group_name'].'-'.$value['attribute_name'];
                }
            }
            $line = Product::getTaxesInformations($line);
        }
        if (!$full) {
            return $result;
        }
        $array_result = array();
        foreach ($result as $prow) {
            if (!Pack::isPack($prow['id_product'])) {
                $prow['id_product_attribute'] = (int)$prow['id_product_attribute_item'];
                $array_result[] = Product::getProductProperties($id_lang, $prow);
            }
        }
        return $array_result;
    }
}