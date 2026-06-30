<?php

/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class KDRuleList extends ObjectModel
{
    public $disallow;
    public $rule;
    public $active;
    public $name;
    public $id_product;

    public static $definition = array(
        'table' => 'rule_list',
        'primary' => 'id_rule_list',
        'multilang' => false,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'name' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 255),
            'rule' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'disallow' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        )
    );

    public static function getAllRules($id_product)
    {
        $result = Db::getInstance()->executeS(
            'SELECT r.* FROM `' . _DB_PREFIX_ . 'rule_list` r
            WHERE r.`id_product` = ' . (int)$id_product
        );
        return $result;
    }

    /**
     * Check if a configuration is valid against the rules
     * @param int $id_product
     * @param array $configuration associative array of [id_product_variable => value]
     * @param int $id_lang
     * @return bool true if valid, false if invalid
     */
    public static function isConfigurationValid($id_product, $configuration, $id_lang)
    {
        $rules = self::getAllRules($id_product);
        foreach ($rules as $rule) {
            if (!$rule['active']) {
                continue;
            }
            $rule_data = json_decode($rule['rule'], true);
            $disallow_data = json_decode($rule['disallow'], true);

            // First check if the rule conditions are met
            $rule_met = self::evaluateRule($rule_data, $configuration, $id_lang, $id_product);
            if (!$rule_met) {
                continue;
            }

            // Now check if any disallowed options are selected
            foreach ($disallow_data as $disallow) {
                if (is_array($disallow) && isset($disallow['disallow_variable']) && isset($disallow['disallow_options'])) {
                    $id_variable = $disallow['disallow_variable'];
                    $disallowed_options = $disallow['disallow_options'];
                    
                    // Find id_product_variable for this id_variable and product
                    $id_product_variable = self::getIdProductVariableByIdVariable($id_product, $id_variable);
                    if (!$id_product_variable) {
                        continue;
                    }

                    if (isset($configuration[$id_product_variable])) {
                        $selected_value = $configuration[$id_product_variable];
                        
                        $varObj = new KDVariable($id_variable);
                        if ($varObj->type == 2) {
                            // If it's an option type, convert the selected label to option ID
                            if (!is_numeric($selected_value)) {
                                $options = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                                    SELECT o.id_option
                                    FROM ' . _DB_PREFIX_ . 'option o
                                    LEFT JOIN ' . _DB_PREFIX_ . 'option_lang ol ON o.id_option = ol.id_option AND ol.id_lang = ' . (int)$id_lang . '
                                    WHERE o.id_variable = ' . (int)$id_variable . '
                                    AND ol.label = \'' . pSQL($selected_value) . '\'
                                ');
                                if (!empty($options)) {
                                    $selected_value = $options[0]['id_option'];
                                }
                            }
                            $selected_value = (int)$selected_value;
                            // Convert disallowed options to integers too
                            $disallowed_options = array_map('intval', $disallowed_options);
                        }
                        
                        // Check if selected value is in disallowed options
                        if (in_array($selected_value, $disallowed_options)) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get id_product_variable from id_variable and id_product
     * @param int $id_product
     * @param int $id_variable
     * @return int|false
     */
    private static function getIdProductVariableByIdVariable($id_product, $id_variable)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT id_product_variable
            FROM ' . _DB_PREFIX_ . 'product_variable
            WHERE id_product = ' . (int)$id_product . ' AND id_variable = ' . (int)$id_variable
        );
        return $result ? (int)$result['id_product_variable'] : false;
    }

    /**
     * Evaluate a single rule against the configuration
     * @param array $rule_data
     * @param array $configuration (key: id_product_variable, value: selected value)
     * @param int $id_lang
     * @param int $id_product
     * @return bool
     */
    private static function evaluateRule($rule_data, $configuration, $id_lang, $id_product)
    {
        if (empty($rule_data)) {
            return false;
        }

        $result = null;
        foreach ($rule_data as $rule) {
            $id_variable = $rule['variable'];
            $sign = $rule['sign'];
            $rule_value = $rule['option'];
            $and_or_sign = isset($rule['and_or_sign']) ? $rule['and_or_sign'] : 0;

            // Find id_product_variable for this id_variable
            $id_product_variable = self::getIdProductVariableByIdVariable($id_product, $id_variable);
            if (!$id_product_variable) {
                $part_result = false;
            } else {
                $selected_value = isset($configuration[$id_product_variable]) ? $configuration[$id_product_variable] : null;
                $part_result = self::compareValues($selected_value, $sign, $rule_value, $id_lang, $id_variable);
            }

            if ($result === null) {
                $result = $part_result;
            } else {
                if ($and_or_sign == 1) { // OR
                    $result = $result || $part_result;
                } elseif ($and_or_sign == 2) { // AND
                    $result = $result && $part_result;
                }
            }
        }

        return $result;
    }

    /**
     * Compare two values based on sign
     * @param mixed $selected_value
     * @param int $sign
     * @param mixed $rule_value
     * @param int $id_lang
     * @param int $id_variable
     * @return bool
     */
    private static function compareValues($selected_value, $sign, $rule_value, $id_lang, $id_variable = 0)
    {
        if ($selected_value === null) {
            return false;
        }

        $varObj = new KDVariable($id_variable);
        if ($varObj->type == 2) {
            // For option type variables:
            // Rule value is option ID
            // Selected value from customization is option label—so we need to find the option ID for this label
            if (!is_numeric($selected_value)) {
                // Find option by label and variable ID
                $options = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                    SELECT o.id_option
                    FROM ' . _DB_PREFIX_ . 'option o
                    LEFT JOIN ' . _DB_PREFIX_ . 'option_lang ol ON o.id_option = ol.id_option AND ol.id_lang = ' . (int)$id_lang . '
                    WHERE o.id_variable = ' . (int)$id_variable . '
                    AND ol.label = \'' . pSQL($selected_value) . '\'
                ');
                if (!empty($options)) {
                    $selected_value = $options[0]['id_option'];
                }
            }
            // Now both should be numeric IDs
            $selected_value = (int)$selected_value;
            $rule_value = (int)$rule_value;
        }

        switch ($sign) {
            case 1: // Equals
                return $selected_value == $rule_value;
            case 2: // Greater than
                return (float)$selected_value > (float)$rule_value;
            case 3: // Less than
                return (float)$selected_value < (float)$rule_value;
            default:
                return false;
        }
    }
}
