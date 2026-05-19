<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class Product extends ProductCore
{
    public static function getAllCustomizedDatas($id_cart, $id_lang = null, $only_in_cart = true, $id_shop = null, $id_customization = null)
    {
        $id_product = 0;
        $is_normal_product = false;
            
        if (!Customization::isFeatureActive()) {
            return false;
        }
        if (!$id_cart) {
            return false;
        }
        if ($id_customization === 0) {
            $product_customizations = (int) Db::getInstance()->getValue('
                SELECT COUNT(`id_customization`) FROM `' . _DB_PREFIX_ . 'cart_product`
                WHERE `id_cart` = ' . (int) $id_cart .
                ' AND `id_customization` != 0');
            if ($product_customizations) {
                return false;
            }
        }
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        if (Shop::isFeatureActive() && !$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        if (!$result = Db::getInstance()->executeS('
            SELECT cd.`id_customization`, c.`id_address_delivery`, c.`id_product`, cfl.`id_customization_field`, c.`id_product_attribute`,
                cd.`type`, cd.`index`, cd.`value`, cd.`id_module`, cfl.`name` as label,  pvl.`name` as name
            FROM `' . _DB_PREFIX_ . 'customized_data` cd
            NATURAL JOIN `' . _DB_PREFIX_ . 'customization` c
            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field_lang` cfl ON (cfl.id_customization_field = cd.`index` AND id_lang = ' . (int) $id_lang .
            ($id_shop ? ' AND cfl.`id_shop` = ' . (int) $id_shop : '') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable` pv ON (pv.id_product_variable = cd.`index`)
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pvl ON (pvl.id_product_variable = pv.id_product_variable AND pvl.id_lang = ' . (int) $id_lang .')
            LEFT JOIN `' . _DB_PREFIX_ . 'variable` v ON (v.id_variable = pv.`id_variable`)
            WHERE c.`id_cart` = ' . (int) $id_cart .
            ($only_in_cart ? ' AND c.`in_cart` = 1' : '') .
            ((int) $id_customization ? ' AND cd.`id_customization` = ' . (int) $id_customization : '') . '
            ORDER BY `id_product`, `id_product_attribute`, `type`, `index`')) {
            return false;
        }
        $customized_datas = [];
        foreach ($result as $row) {
            
            $id_product = $row['id_product'];
        }
        if($id_product){
            $variable_position = Db::getInstance()->getValue('
            SELECT variable_position
            FROM `'._DB_PREFIX_.'product_setting`
            WHERE `id_product`='.(int)$id_product
            );
            if ($variable_position) {
                $variable_position = json_decode($variable_position, true);
                if($variable_position){
                    usort($result, function ($a, $b) use ($variable_position) {
                        $pos_a = array_search($a['index'], $variable_position);
                        $pos_b = array_search($b['index'], $variable_position);
                        return $pos_a - $pos_b;
                    });
                }
            }else{
                $is_normal_product = true;
            }
        }
        foreach ($result as $row) {
            if ((int) $row['id_module'] && (int) $row['type'] == Product::CUSTOMIZE_TEXTFIELD) {
                $row['value'] = Hook::exec('displayCustomization', ['customization' => $row], (int) $row['id_module']);
            }
            $id_product = $row['id_product'];
            if($is_normal_product){
                $row['name'] = $row['label'];
            }
            $customized_datas[(int) $row['id_product']][(int) $row['id_product_attribute']][(int) $row['id_address_delivery']][(int) $row['id_customization']]['datas'][(int) $row['type']][] = $row;
        }
        
        if (!$result = Db::getInstance()->executeS(
            'SELECT `id_product`, `id_product_attribute`, `id_customization`, `id_address_delivery`, `quantity`, `quantity_refunded`, `quantity_returned`
            FROM `' . _DB_PREFIX_ . 'customization`
            WHERE `id_cart` = ' . (int) $id_cart .
                ((int) $id_customization ? ' AND `id_customization` = ' . (int) $id_customization : '') .
                ($only_in_cart ? ' AND `in_cart` = 1' : '')
        )) {
            return false;
        }
        foreach ($result as $row) {
            $customized_datas[(int) $row['id_product']][(int) $row['id_product_attribute']][(int) $row['id_address_delivery']][(int) $row['id_customization']]['quantity'] = (int) $row['quantity'];
            $customized_datas[(int) $row['id_product']][(int) $row['id_product_attribute']][(int) $row['id_address_delivery']][(int) $row['id_customization']]['quantity_refunded'] = (int) $row['quantity_refunded'];
            $customized_datas[(int) $row['id_product']][(int) $row['id_product_attribute']][(int) $row['id_address_delivery']][(int) $row['id_customization']]['quantity_returned'] = (int) $row['quantity_returned'];
            $customized_datas[(int) $row['id_product']][(int) $row['id_product_attribute']][(int) $row['id_address_delivery']][(int) $row['id_customization']]['id_customization'] = (int) $row['id_customization'];
            
        }
        return $customized_datas;
    }
    public static function getPriceFromOrder(
        int $orderId,
        int $productId,
        int $combinationId,
        bool $withTaxes,
        bool $useReduction,
        bool $withEcoTax,
        int $id_customzation = 0
    ): ?float {
        $sql = new DbQuery();
        $sql->select('od.*, t.rate AS tax_rate');
        $sql->from('order_detail', 'od');
        $sql->where('od.`id_order` = ' . $orderId);
        $sql->where('od.`product_id` = ' . $productId);
        if (Combination::isFeatureActive()) {
            $sql->where('od.`product_attribute_id` = ' . $combinationId);
        }
        
        $sql->where('od.`id_customization` = ' . $id_customzation);
        $sql->leftJoin('order_detail_tax', 'odt', 'odt.id_order_detail = od.id_order_detail');
        $sql->leftJoin('tax', 't', 't.id_tax = odt.id_tax');
        $res = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($res) || empty($res)) {
            return null;
        }
        $orderDetail = $res[0];
        if ($useReduction) {
            $price = $withTaxes ? $orderDetail['unit_price_tax_incl'] : $orderDetail['unit_price_tax_excl'];
        } else {
            $tax_rate = $withTaxes ? (1 + ($orderDetail['tax_rate'] / 100)) : 1;
            $price = $orderDetail['original_product_price'] * $tax_rate;
        }
        if (!$withEcoTax) {
            $price -= ($withTaxes ? $orderDetail['ecotax'] * (1 + $orderDetail['ecotax_tax_rate']) : $orderDetail['ecotax']);
        }
        return $price;
    }
	/*
    * module: vatchecker
    * date: 2025-11-21 13:56:28
    * version: 3.1.5
    */
    public static function getIdTaxRulesGroupByIdProduct( $id_product, Context $context = null )
	{
		if ( ! $context ) {
			$context = Context::getContext();
		}
		$vatchecker = Module::getInstanceByName( 'vatchecker' );
		$addressToCheck = $vatchecker->selectAddressFromCart($context->cart);
		$key = 'product_id_tax_rules_group_' . (int) $id_product . '_' . (int) $context->shop->id;
		if ( $addressToCheck ) {
			if ( $vatchecker && $vatchecker->canOrderWithoutVat( $addressToCheck ) ) {
				$skipProduct = Db::getInstance()->getRow(
					'SELECT `id_product`
        			 FROM `' . _DB_PREFIX_ . 'vatchecker_excluded_products`
                     WHERE `id_product` =' . (int) $id_product
				);
				if ( empty($skipProduct) ) {
					$taxRuleGroupId = Configuration::get( 'VATCHECKER_TAXRATE_RULE' );
					if ( ! Cache::isStored( $key ) ) {
						Cache::store( $key, (int) $taxRuleGroupId );
					}
					return (int) $taxRuleGroupId;
				}
			}
		}
		if ( ! Cache::isStored( $key ) ) {
			$result = Db::getInstance( _PS_USE_SQL_SLAVE_ )->getValue(
				'
					SELECT `id_tax_rules_group`
					FROM `' . _DB_PREFIX_ . 'product_shop`
					WHERE `id_product` = ' . (int) $id_product . ' AND id_shop=' . (int) $context->shop->id
			);
			Cache::store( $key, (int) $result );
			return (int) $result;
		}
		return Cache::retrieve( $key );
	}
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getAnchor($id_product_attribute, $with_id = false)
    {
        if (Module::isEnabled('ets_seo') && (bool) Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
            if ((bool) Configuration::get('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS')) {
                return '';
            }
            if ((bool) Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_ATTR_ALIAS')) {
                return parent::getAnchor($id_product_attribute, false);
            }
        }
        return parent::getAnchor($id_product_attribute, $with_id);
    }
    
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getImages($id_lang, Context $context = null)
    {
        $data = $this->coreGetImages($id_lang);
        if (Module::isEnabled('ets_seo') && $module = Module::getInstanceByName('ets_seo')) {
            
            $meta = $module->getCurrentMetaData();
            if (isset($meta['img_alt']) && $alts = $meta['img_alt']) {
                foreach ($data as &$item) {
                    if (is_array($alts)) {
                        $item['legend'] = isset($alts[$item['id_image']]) && $alts[$item['id_image']] ? $alts[$item['id_image']] : $item['legend'];
                    } else {
                        $item['legend'] = $alts;
                    }
                }
                unset($item);
            }
            return $data;
        }
        return $data;
    }
    
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function coreGetImages($id_lang)
    {
        $context = Context::getContext();
        $cache_id = 'ProductCore::getImages_' . $this->id . '_' . (int) $id_lang . '-' . (int) $context->shop->id;
        if (!Cache::isStored($cache_id)) {
            $data = parent::getImages($id_lang);
            Cache::store($cache_id, $data);
            return $data;
        }
        return Cache::retrieve($cache_id);
    }
}
