<?php
/**
 * @author MyPresta.eu | Milos "VEKIA" Myszczuk <support@mypresta.eu>
 * All rights reserved! Copying, duplication strictly prohibited
 * http://www.mypresta.eu - prestashop modules
 *
 */
class Shop extends ShopCore
{
    /*
    * module: pga
    * date: 2026-04-23 16:47:11
    * version: 1.8.4
    */
    public static function addSqlAssociation($table, $alias, $inner_join = true, $on = null, $force_not_default = false)
    {
        $table_alias = $table . '_shop';
        if (strpos($table, '.') !== false)
        {
            list($table_alias, $table) = explode('.', $table);
        }
        $asso_table = Shop::getAssoTable($table);
        if ($asso_table === false || $asso_table['type'] != 'shop')
        {
            return;
        }
        $sql = (($inner_join) ? ' INNER' : ' LEFT') . ' JOIN ' . _DB_PREFIX_ . $table . '_shop ' . $table_alias . '
		ON (' . $table_alias . '.id_' . $table . ' = ' . $alias . '.id_' . $table;
        if ((int)self::$context_id_shop)
        {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . (int)self::$context_id_shop;
        }
        elseif (Shop::checkIdShopDefault($table) && !$force_not_default)
        {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . $alias . '.id_shop_default';
        }
        else
        {
            $sql .= ' AND ' . $table_alias . '.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }
        $sql .= (($on) ? ' AND ' . $on : '');
        if (Module::isInstalled('pga') && !defined('_PS_ADMIN_DIR_') && Configuration::get('pga_cathide') == 1)
        {
            if ($table == 'product' AND $alias == 'p')
            {
                $array_to_check = array();
                $context = new Context();
                $context = $context->getContext();
                if (isset($context->cart->id_customer))
                {
                    if ($context->cart->id_customer == 0)
                    { //VISTOR
                        $array_to_check[1] = 1;
                    }
                    else
                    { //CUSTOMER
                        if (Configuration::get('pga_autohide_customers') == 0)
                        {
                            foreach (Customer::getGroupsStatic($context->cart->id_customer ? $context->cart->id_customer : $context->cart->id_customer) as $group)
                            {
                                $array_to_check[$group] = $group;
                            }
                        } else {
                            $has_orders = 0;
                            if (isset($context->cart->id_customer)) {
                                if ($context->cart->id_customer > 0)
                                {
                                    if (self::getCustomerNbOrdersValid($context->cart->id_customer) > 0)
                                    {
                                        $has_orders = 1;
                                    }
                                }
                            }
                            if ($has_orders == 1)
                            {
                                $array_to_check[-1] = -1;
                            }
                            else
                            {
                                foreach (Customer::getGroupsStatic($context->cart->id_customer ? $context->cart->id_customer : $context->cart->id_customer) as $group)
                                {
                                    $array_to_check[$group] = $group;
                                }
                            }
                        }
                    }
                }
                elseif ($context->customer->is_guest == 1)
                {
                    $array_to_check[2] = Configuration::get('PS_GUEST_GROUP');
                }
                else
                {
                    $array_to_check[1] = Configuration::get('PS_UNIDENTIFIED_GROUP');
                }
                if (Configuration::get('pga_access_details') == 0 && 1 == 1)
                {
                    $sql .= ' AND p.id_product NOT IN (SELECT id_product FROM ' . _DB_PREFIX_ . 'pga pga WHERE pga.id_shop IN (' . (int)self::$context_id_shop . ') AND pga.`group` IN (' . implode(',', $array_to_check) . '))';
                }
                else
                {
                    $sql .= ' AND p.id_product NOT IN (SELECT id_product FROM ' . _DB_PREFIX_ . 'pga pga WHERE pga.id_shop IN (' . (int)self::$context_id_shop . ') AND pga.`group` IN (' . implode(',', $array_to_check) . ') HAVING COUNT(*) >= ' . count($array_to_check) . ')';
                }
                unset($array_to_check);
            }
        }
        $sql .= ')';
        return $sql;
    }
    /*
    * module: pga
    * date: 2026-04-23 16:47:11
    * version: 1.8.4
    */
    public static function getCustomerNbOrdersValid($id_customer)
    {
        $sql = 'SELECT COUNT(`id_order`) AS nb
                FROM `'._DB_PREFIX_.'orders`
                WHERE `id_customer` = '.(int)$id_customer.' AND valid = 1'
               .Shop::addSqlRestriction();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return isset($result['nb']) ? $result['nb'] : 0;
    }
}