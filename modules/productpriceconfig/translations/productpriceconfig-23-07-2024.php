<?php

/**
 * 2017-2018 Krupaludev
 *
 * ProductPriceConfig
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    Vipul <krupaludev@icloud.com>
 *  @copyright 2017-2018 Krupaludev
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PSpell\Config;

require_once(dirname(__FILE__) . '/libraries/krupaludev/Helpers.php');
require_once(dirname(__FILE__) . '/libraries/KDVariable.php');
require_once(dirname(__FILE__) . '/libraries/KDOption.php');
require_once(dirname(__FILE__) . '/libraries/KDToolTip.php');
require_once(dirname(__FILE__) . '/libraries/KDRuleList.php');
require_once(dirname(__FILE__) . '/libraries/KDProductSetting.php');
require_once(dirname(__FILE__) . '/libraries/KDAlertMessage.php');
require_once(dirname(__FILE__) . '/libraries/KDProductVariable.php');


/**
 * Class ProductPriceConfig
 */
class ProductPriceConfig extends Module
{
    private $_html = '';
    public $fields_form;
    public $html_form;
    public $fields_value;


    public function __construct()
    {
        $this->name = 'productpriceconfig';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Vipul Jain';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Price Cofiguration');
        $this->description = $this->l('Product Price Configure with options');
        $this->confirmUninstall = $this->l('Uninstalling the module will delete all data?');

        $this->definePublicVariables();
    }

    public function definePublicVariables()
    {
        $this->db = Db::getInstance();
        $this->media_path = $this->_path . 'views/';
        $this->saved_txt = $this->l('Saved');
        $this->error_txt = $this->l('Error');
        $this->is_17 = Tools::substr(_PS_VERSION_, 0, 3) === '1.7';
        $this->page_link_rewrite_text = $this->is_17 ? 'page' : 'p';
        $this->shop_ids = Shop::getContextListShopID();
        $this->all_shop_ids = Shop::getShops(false, null, true);
        $this->i = array(
            'variable_keys' => array('p', 'n', 't'),
            'default' => array('g' => 'PS_UNIDENTIFIED_GROUP', 'c' => 'PS_CURRENCY_DEFAULT'),
            'max_column_suffixes' => 15,
        );
    }


    /**  Function Install Module **/
    public function install()
    {
        if (
            parent::install()
            && $this->registerHook('actionShopDataDuplication')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionAfterDeleteProductInCart')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayReassurance')

            && $this->registerHook('displayCartExtraProductActions')
        ) {
            $sql1 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "variable` (
                `id_variable`   BIGINT(20)  UNSIGNED    NOT NULL AUTO_INCREMENT,
                `name` varchar(125) NOT NULL,
                `type` varchar(255) NOT NULL,
                `fixed_price` int(10) unsigned NOT NULL DEFAULT \'0\',
                `minimum` int(10) unsigned NOT NULL DEFAULT \'0\',
                `maximum` int(10) unsigned NOT NULL DEFAULT \'0\',
                `required` int(1) unsigned NOT NULL DEFAULT \'0\',
                `active` int(1) unsigned NOT NULL DEFAULT \'1\',
                `position` int(10) unsigned NOT NULL DEFAULT \'0\',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_variable`)
              )  ENGINE = " . _MYSQL_ENGINE_ . " DEFAULT CHARSET = utf8";

            $sql2 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "variable_lang` (
                `id_variable`   BIGINT(20)  UNSIGNED    NOT NULL AUTO_INCREMENT,
                `id_lang` int(10) unsigned NOT NULL,
                `label` varchar(255) NOT NULL,
                PRIMARY KEY (`id_variable`,`id_lang`)
            )  ENGINE = " . _MYSQL_ENGINE_ . " DEFAULT CHARSET = utf8";

            $sql3 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "option` (
              `id_option` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `id_variable` INT(10)     UNSIGNED    NOT NULL DEFAULT \'0\',
              `price` decimal(20,4) NOT NULL DEFAULT \'0.00\',
              `position` int(10) unsigned NOT NULL DEFAULT \'0\',
              `weight` decimal(20,2) NOT NULL DEFAULT \'0.00\',
              `active` int(1) unsigned NOT NULL DEFAULT \'0\',
              PRIMARY KEY (`id_option`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql4 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "option_lang` (
                `id_option` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `label` varchar(500) NOT NULL,
                PRIMARY KEY (`id_option`,`id_lang`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql5 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_variable` (
                `id_product_variable`   BIGINT(20)  UNSIGNED    NOT NULL AUTO_INCREMENT,
                `id_product` int(10) unsigned NOT NULL,
                `id_variable` int(10) unsigned NOT NULL,
                `options` text NOT NULL,
                `id_variable_tooltip` int(10) unsigned NOT NULL,
                `default_option` int(10),
                `formula_name` varchar(500) NOT NULL,
                `active` int(1) unsigned NOT NULL DEFAULT \'1\',
                `minimum` DOUBLE NULL DEFAULT NULL,
                `maximum` DOUBLE NULL DEFAULT NULL,
                `multiplier` DOUBLE NULL DEFAULT \'1\',
                  PRIMARY KEY (`id_product_variable`)
                ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql6 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_variable_lang` (
                `id_product_variable` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `name` varchar(500) NOT NULL,
                PRIMARY KEY (`id_product_variable`,`id_lang`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql7 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "variable_tooltip` (
                `id_variable_tooltip` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `label` varchar(500) NOT NULL,
                PRIMARY KEY (`id_variable_tooltip`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql8 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "variable_tooltip_lang` (
                `id_variable_tooltip` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `text` text NOT NULL,
                PRIMARY KEY (`id_variable_tooltip`,`id_lang`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql9 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_setting` (
                `id_product_setting` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_product` int(10) unsigned NOT NULL,
                `formula_price` varchar(255) NOT NULL,
                `formula_weight` varchar(255) NOT NULL,
                `formula_thickness` varchar(255) NOT NULL,
                `baned_comb` int(2) unsigned NOT NULL,
                `tiered` text NOT NULL,
                PRIMARY KEY (`id_product_setting`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql10 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "rule_list` (
                `id_rule_list` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_product` int(10) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `rule` text NOT NULL,
                `disallow` text NOT NULL,
                `active` int(1) unsigned NOT NULL DEFAULT \'1\',
                PRIMARY KEY (`id_rule_list`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql11 = "CREATE TABLE `" . _DB_PREFIX_ . "alert_messages` (
                `id_alert_messages` int unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int unsigned NOT NULL,
                `variable_id` int unsigned NOT NULL,
                `option_id` int unsigned NOT NULL,
                `message` text NOT NULL,
                PRIMARY KEY (`id_alert_messages`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sql12 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "price_for_odd_quantities` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int(10) unsigned NOT NULL,
                `percentage` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            Db::getInstance()->execute($sql1);
            Db::getInstance()->execute($sql2);
            Db::getInstance()->execute($sql3);
            Db::getInstance()->execute($sql4);
            Db::getInstance()->execute($sql5);
            Db::getInstance()->execute($sql6);
            Db::getInstance()->execute($sql7);
            Db::getInstance()->execute($sql8);
            Db::getInstance()->execute($sql9);
            Db::getInstance()->execute($sql10);
            Db::getInstance()->execute($sql11);
            Db::getInstance()->execute($sql12);

            $id_tab = Tab::getIdFromClassName('AdminCatalog');
            $this->installModuleTab('AdminProductPriceConfigHome', array((int)$this->context->language->id => 'Manage Product Price'), 0);
            $id_tab = Tab::getIdFromClassName('AdminProductPriceConfigHome');

            $this->installModuleTab('AdminProductPriceConfig', array((int)$this->context->language->id => 'Product price config'), $id_tab);
            $this->installModuleTab('AdminVariable', array((int)$this->context->language->id => 'Variables'), $id_tab);
            $this->installModuleTab('AdminVariableToolTip', array((int)$this->context->language->id => 'Tool Tips'), $id_tab);
            $this->installModuleTab('AdminAlertMessages', array((int)$this->context->language->id => 'Alert Messages'), $id_tab);


            return true;
        }
        return false;
    }

    public function uninstall()
    {
        $id_tab = Tab::getIdFromClassName('AdminProductPriceConfigHome');

        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'variable`');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'variable_lang`');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'option`');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'option_lang`');

        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'variable_tooltip`');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'variable_tooltip_lang`');

        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_variable`');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_variable_lang`');

        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_setting`');

        if (parent::uninstall() && $this->uninstallModuleTab('AdminVariable', $id_tab) && $this->uninstallModuleTab('AdminVariableToolTips', $id_tab) && $this->uninstallModuleTab('AdminProductPriceConfig', $id_tab) && $this->uninstallModuleTab('AdminProductPriceConfigHome', 0)) {
            return true;
        }

        return true;
    }


    private function installModuleTab($tabClass, $tabName, $idTabParent)
    {
        $tab = new Tab();

        $langues = Language::getLanguages(false);
        foreach ($langues as $langue)
            if (!Tools::getIsset($tabName[$langue['id_lang']])) $tabName[$langue['id_lang']] = $tabName[(int)$this->context->language->id];

        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        $id_tab = $tab->save();
        if (!$id_tab)
            return false;

        //$this->installcleanPositions($tab->id, $idTabParent);

        return true;
    }

    private function uninstallModuleTab($tabClass, $idTabParent)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            //$this->uninstallcleanPositions($idTabParent);
            return true;
        }
        return false;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    public function assignLanguageVariables()
    {
        $this->context->smarty->assign(array(
            'available_languages' => $this->getAvailableLanguages(),
            'id_lang_current' => $this->context->language->id,
        ));
    }


    public function ajaxAction($action)
    {
        $ret = array();
        switch ($action) {
            case 'CallVariableForm':
                $id_product_variable = Tools::getValue('id_product_variable');
                $ret = $this->callVariableForm($id_product_variable);
                break;
            case 'SaveTieredSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->saveTieredPrice();
                break;
            case 'SaveBanCombSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->saveBanComb();
                break;
            case 'AjaxDatatableRulesList':
                $this->rulesListAjax();
                break;
            case 'getOptionsBasedOnVariableId':
                $this->getOptionsBasedOnVariableId();
                break;
            case 'SaveFormulaSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->saveFormulaSettings();

                break;
            case 'SavePercentageSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->savePercentageSettings();

                break;
            case 'SaveVariable':
            case 'DeleteVariable':
            case 'SaveProductVariablePosition':
                $method = 'ajax' . $action;
                $this->$method();
                break;
            case 'ToggleActiveStatus':
                $id_variable = Tools::getValue('id_variable');
                $active = Tools::getValue('active');
                $ret = array('success' => $this->toggleActiveStatus($id_variable, $active));
                break;
            case 'ShowAvailableVariables':
                $available_variables = $this->getAvailableVariables();
                $this->context->smarty->assign(array('available_variables' => $available_variables));
                $html = $this->display(__FILE__, 'views/templates/admin/available-variables.tpl');
                $ret['content'] = utf8_encode($html);
                $ret['title'] = utf8_encode($this->l('Available variables'));
                break;
            case 'SaveVariableElements':
                $keys = explode(',', Tools::getValue('keys'));
                $id_product = Tools::getValue('id_product');

                $html = '';
                $this->assignLanguageVariables();
                foreach ($keys as $key) {
                    $this->saveVariableSelected($key, $id_product);
                }
                $ret['html'] = utf8_encode('Please wait...');
                break;
            case 'DeleteRule':
                $this->DeleteRule();
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function DeleteRule()
    {
        $rule_id = $_POST['id_rule'];
        $rule = new KdRuleList($rule_id);
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'rule_list WHERE id_rule_list = ' . $rule_id;
        // die($sql);

        return $this->db->execute($sql);
    }
    public function getOptionsBasedOnVariableId()
    {
        $id_variable = Tools::getValue('id_variable');
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'variable WHERE id_variable = ' . $id_variable;
        $variable = Db::getInstance()->executeS($sql)[0];

        $count = Tools::getValue('count');
        if ($count == '') {
            $count = 1;
        }
        $sql = 'SELECT  a.* , b.*
            FROM ' . _DB_PREFIX_ . 'option a
            LEFT JOIN ' . _DB_PREFIX_ . 'option_lang b ON (a.id_option = b.id_option)
            WHERE b.id_lang = ' . (int)$this->context->language->id . ' AND a.id_variable = ' . $id_variable . '
            ORDER BY a.`position`';
        $variable_options = Db::getInstance()->executeS($sql);
        $var_name = strtolower($variable['name']);
        $var_name = str_replace(' ', '', $var_name);
        if (empty($variable_options)) {
            $variable_options = $variable['type'];
        }

        $html = "";
        $data = [];
        if (is_array($variable_options)) {
            $html .= "<select class='form-control disallow_option' data-id_variable='$id_variable' name='disallow_options" . $count . "[]' multiple>";
            foreach ($variable_options as $key => $option) {
                $id_option = $option['id_option'];
                $label = $option['label'];
                $html .= "<option value='" . $id_option . "'>" . $label . "</option>";
            }
            $html .= "</select>";
            $data['type'] = 'select';
        } else {
            $data['type'] = 'input';
            if ($variable_options == 1 || $variable_options == 4) {
                $html .= '<input type="number" class="form-control options options' . $count . '" data-id_variable="' . $id_variable . '" name="disallow_options[]">';
            }
            if ($variable_options == 5) {
                $html .= '<input type="text" class="form-control options options' . $count . '" data-id_variable="' . $id_variable . '" name="disallow_options[]">';
            }
        }

        $data['html'] = $html;
        echo json_encode($data);
        exit;
    }

    public function rulesListAjax()
    {
        $id_product = Tools::getValue('id_product');
        $rules_list = KDRuleList::getAllRules($id_product);
        $context = Context::getContext();
        $current_language_id = $context->language->id;
        $html = '<tbody>';
        $data = [];
        $total = count($rules_list);
        if (count($rules_list)) {
            $count = 1;
            foreach ($rules_list as &$rules) {
                $rule_data = json_decode($rules['rule'], true);
                $rule_text = 'if ';
                foreach ($rule_data as $rule) {
                    $varObj = new KDVariable($rule['variable']);
                    $variable_name = $varObj->name;
                    $option_value = $rule['option'];
                    if ($varObj->type == 2) {
                        $optionObj = new KDOption($rule['option'], $this->context->language->id);
                        $option_value = $optionObj->label;
                    }

                    if ($rule['and_or_sign'] == 1) {
                        $and_or_sign = ' OR ';
                    } elseif ($rule['and_or_sign'] == 2) {
                        $and_or_sign = ' AND ';
                    } else {
                        $and_or_sign = '';
                    }

                    if ($rule['sign'] == 1) {
                        $sign = '=';
                    } elseif ($rule['sign'] == 2) {
                        $sign = '>';
                    } elseif ($rule['sign'] == 3) {
                        $sign = '<';
                    } else {
                        $sign = '';
                    }

                    $rule_text .= $variable_name . $sign . $option_value . '' . $and_or_sign;
                }

                $rules['rule_text'] = $rule_text;

                $disallow_data = json_decode($rules['disallow'], true);
                $disallowed = '';
                foreach ($disallow_data as $disallow) {
                    // $disallow = json_decode($disallow);
                    $varObj = new KDVariable();
                    if (is_array($disallow)) {
                        $disallow_variables = $disallow['disallow_variable'];
                        $disallow_options = $disallow['disallow_options'];
                        foreach ($disallow_variables as $variable) {
                            $varObj = new KDVariable($variable);
                            $disallowed .= $varObj->name . ' (';
                            foreach ($disallow_options as $option) {
                                $optionObj = new KDOption($option);
                                $name = $optionObj->label[$current_language_id];
                                $disallowed  .= $name . ", ";
                            }
                            $disallowed .= ') ,';
                        }
                    } else {
                        $varObj = new KDVariable($disallow);
                        $disallowed .= $varObj->name . ' ,';
                    }
                }

                $rules['id'] = $count;
                $rules['disllowed_text'] = $disallowed;
                $rules['delete'] = '<a href="" class="delete-rule" data-id_rule="' . $rules['id_rule_list'] . '"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                $count++;
            }
        }
        $output = array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rules_list
        );
        echo json_encode($output);
        exit;
        // echo json_encode($data);
    }
    // public function rulesListAjax()
    // {
    //     $id_product = Tools::getValue('id_product');
    //     $rules_list = KDRuleList::getAllRules($id_product);
    //     $html = '<tbody>';
    //     $data = [];
    //     if(count($rules_list)){
    //         foreach($rules_list as &$rules){
    //             $rule_data = json_decode($rules['rule'], true);
    //             $rule_text = 'if ';
    //             foreach($rule_data as $rule){
    //                 $varObj = new KDVariable($rule['variable']);
    //                 $variable_name = $varObj->name;
    //                 $option_value = $rule['option'];
    //                 if($varObj->type == 2){
    //                     $optionObj = new KDOption($rule['option'], $this->context->language->id);
    //                     $option_value = $optionObj->label;
    //                 }

    //                 if($rule['and_or_sign'] == 1){
    //                     $and_or_sign = ' OR ';
    //                 }elseif($rule['and_or_sign'] == 2){
    //                     $and_or_sign = ' AND ';
    //                 }else{
    //                     $and_or_sign = '';
    //                 }

    //                 if($rule['sign'] == 1){
    //                     $sign = '=';
    //                 }elseif($rule['sign'] == 2){
    //                 $sign = '>';
    //                 }elseif($rule['sign'] == 3){
    //                     $sign = '<';
    //                 }else{
    //                     $sign = '';
    //                 }

    //                 $rule_text .= $variable_name.$sign.$option_value.''.$and_or_sign;
    //             }

    //             $rules['rule_text'] = $rule_text;

    //             $disallow_data = json_decode($rules['disallow'], true);
    //             $disallowed = '';
    //             foreach($disallow_data as $disallow){
    //                 $varObj = new KDVariable($disallow);
    //                 $disallowed .= $varObj->name.' ,';
    //             }

    //             $rules['disllowed_text'] = $disallowed;
    //         }

    //         foreach ($rules_list as $key => $rules) {
    //             $html .= "<tr>";
    //             $html .= "<td scope='row'>".$rules['id_rule_list']."</td>";
    //             $html .= "<td>".$rules['name']."</td>";
    //             $html .= "<td>".$rules['rule_text']."</td>";
    //             $html .= "<td>".$rules['disllowed_text']."</td>";
    //             $html .= "<td><a href='' class='delete-rule' data-id_rule='" . $rules['id_rule_list'] . "'><i class='fa fa-trash' aria-hidden='true'></i></a></td>";
    //             // $html .= "<td><button type='button' class='delete-rule' data-id_rule='" . $rules['id_rule_list'] . "'><i class='fa fa-trash' aria-hidden='true'></i></button></td>";
    //             $html .= "</tr>";
    //         }
    //         $data['status'] = true;
    //     }else{
    //         $html .= "<tr><td>No records found.</td></tr>";
    //         $data['status'] = false;
    //     }
    //     $html .= '</tbody>';
    //     $data['html'] = $html;
    //     echo $html;
    //     exit;
    //     // echo json_encode($data);
    // }

    public function saveTieredPrice()
    {

        $values = array();
        $qty = array();
        $price = array();
        $id_product = Tools::getValue('id_product');
        $row = KDProductSetting::getByProductId($id_product);

        $product_setting = new KDProductSetting($row['id_product_setting']);

        foreach (array('qty',  'price') as $type) {
            if (Tools::getValue($type) && is_array(${$type} = Tools::getValue($type)) && count(${$type})) {
                ${$type} = Tools::getValue($type);
            }
        }

        if ($total = count($qty)) {
            for ($i = 0; $i < $total; $i++) {
                $values[$i]['from_quantity'] = (int)$qty[$i];
                $values[$i]['price'] = (float)$price[$i];
            }
        }

        $product_setting->id_product = $id_product;

        $product_setting->tiered = json_encode($values);
        $product_setting->save();
        return $product_setting->id;
    }

    public function saveBanComb()
    {

        $rule = array();
        $disallowarr = array();
        $id_product = Tools::getValue('id_product');
        $rule_name = Tools::getValue('rule_name');
        // $disallow = $_POST['disallow'];
        // $disallow_options = $_POST['disallow_options'];

        $count = Tools::getValue('count');
        $cloneOptionCount = Tools::getValue('cloneOptionCount');


        for ($i = 1; $i < $count; $i++) {
            $variable = 'variables' . $i;
            $sign = 'sign' . $i;
            $option = 'options' . $i;
            $and_or_sign = 'and_or_sign' . $i;
            $options = implode('', array_filter($_POST[$option]));
            $rule[] = array('variable' => $_POST[$variable], 'sign' => $_POST[$sign], 'option' => $options, 'and_or_sign' => $_POST[$and_or_sign]);
        }

        for ($i = 1; $i < $cloneOptionCount; $i++) {
            $disallow = 'disallow' . $i;
            $disallow_options = 'disallow_options' . $i;
            $disallowarr[] = array('disallow_variable' => $_POST[$disallow], 'disallow_options' => $_POST[$disallow_options]);
        }

        $rule_list = new KDRuleList();

        $rule_list->active = 1;
        $rule_list->name = $rule_name;
        $rule_list->id_product = $id_product;

        $rule = Tools::jsonEncode($rule);
        $rule_list->rule = $rule;

        $disallow = Tools::jsonEncode($disallowarr);
        $rule_list->disallow = $disallow;


        $rule_list->save();
        return $rule_list->id;
    }

    public function saveFormulaSettings()
    {

        $values = array();
        $id_product = Tools::getValue('id_product');
        $row = KDProductSetting::getByProductId($id_product);

        $product_setting = new KDProductSetting($row['id_product_setting']);


        $product_setting->id_product = $id_product;

        $product_setting->formula_price = Tools::getValue('formula_price');
        $product_setting->formula_weight = Tools::getValue('formula_weight');
        $product_setting->formula_thickness = Tools::getValue('formula_thickness');

        $product_setting->save();
        return $product_setting->id;
    }

    public function savePercentageSettings()
    {
        $id_product = Tools::getValue('id_product');
        $percentage_for_odd_quantity = Tools::getValue('percentage_for_odd_quantity');

        $sql = "INSERT INTO `" . _DB_PREFIX_ . "price_for_odd_quantities` (`product_id`, `percentage`) VALUES (" . $id_product . "," . $percentage_for_odd_quantity . ")";
        Db::getInstance()->execute($sql);

        $id = (int)Db::getInstance()->Insert_ID();
        return $id;
    }

    public function callVariableForm($id_product_variable, $full = true)
    {
        $id_lang = Context::getContext()->language->id;

        if (!$id_product_variable) {
            return false;
        }
        $variable_data_multishop = $this->db->executeS('
            SELECT p.*, pl.name
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable`AND pl.`id_lang` = ' . (int)$id_lang . ' )
            WHERE p.id_product_variable = ' . (int)$id_product_variable . '
        ');
        $variable_data = false;
        foreach ($variable_data_multishop as $data) {
            if (!$variable_data) {
                $variable_data = $data;
                $varObj = new KDVariable($data['id_variable']);
                $variable_data['variable_name'] = $varObj->name;
                $variable_data['variable_type'] = $varObj->type;
                // $variable_data['minimum'] = $varObj->minimum;
                // $variable_data['maximum'] = $varObj->maximum;
            }
            $default_option = $data['default_option'];
        }
        $this->context->smarty->assign(array(
            't' => $variable_data,
            'is_17' => $this->is_17,
        ));
        if ($full && $variable_data) {
            $product_variable_options = Tools::jsonDecode($variable_data['options'], true);
            $variable_options = $varObj->getAllOptions($id_lang);
            if (count($variable_options)) {
                foreach ($variable_options as &$option) {
                    if (in_array($option['id_option'], $product_variable_options)) {
                        $option['selected'] = 1;
                    } else {
                        $option['selected'] = 0;
                    }
                }
            }

            $sortingFunction = function ($a, $b) use ($product_variable_options) {
                $pos_a = array_search($a['id_option'], $product_variable_options);
                $pos_b = array_search($b['id_option'], $product_variable_options);
                return $pos_a - $pos_b;
            };

            // Sort the first array using the custom sorting function
            usort($variable_options, $sortingFunction);

            $all_tool_tips = KDToolTip::getAllToolTips($id_lang);
            $tool_tips = array();
            foreach ($all_tool_tips as $tool_tip) {
                $tool_tips[$tool_tip['id_variable_tooltip']] = $tool_tip['label'];
            }
            $this->context->smarty->assign(array(
                'tool_tips' => $tool_tips,
                'variable_options' => $variable_options,
                'default_option' => $default_option,
            ));
        }
        $this->assignLanguageVariables();
        $ret = array(
            'form_html' => utf8_encode($this->display(__FILE__, 'views/templates/admin/variable-form.tpl')),
            'id_product_variable' => $id_product_variable,
        );
        return $ret;
    }

    public function ajaxSaveVariable()
    {
        $id_product_variable = Tools::getValue('id_product_variable');
        $id_variable = Tools::getValue('id_variable');
        $variable_name = Tools::getValue('variable_name');
        $options_data = Tools::getValue('options');
        $id_product = Tools::getValue('id_product');
        $id_variable_tooltip = Tools::getValue('id_variable_tooltip');
        $formula_name = Tools::getValue('formula_name');
        $default_option = Tools::getValue('default_value');
        $minimum = Tools::getValue('minimum');
        $maximum = Tools::getValue('maximum');
        $multiplier = Tools::getValue('multiplier');

        if (!$options_data) {
            // $errors['no_options'] = $this->l('Please select at least one option.');
        }
        if ($variable_name == '') {
            $errors['no_name'] = $this->l('Please add a variable name');
        }
        if ($errors) {
            $this->throwError($errors);
        }
        if (!$this->saveVariable(
            $id_product_variable,
            $id_variable,
            $id_product,
            $id_variable_tooltip,
            $variable_name,
            $options_data,
            $formula_name,
            $default_option,
            $minimum,
            $maximum,
            $multiplier,
        )) {
            $this->throwError($this->l('Variable not saved'));
        }
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
        );
        die(Tools::jsonEncode($ret));
    }

    public function ajaxSaveProductVariablePosition()
    {
        $position_data = Tools::getValue('positions');
        $id_product = Tools::getValue('id_product');
        
        if (!$position_data) {
             $errors['no_options'] = $this->l('Action failed.');
        }
        if ($errors) {
            $this->throwError($errors);
        }

        $row =  KDProductSetting::getByProductId($id_product);
        $product_setting = new KDProductSetting($row['id_product_setting']);

        $product_setting->variable_position = Tools::jsonEncode($position_data);

        if (!$product_setting->update()) {
            $this->throwError($this->l('Variable order not saved'));
        }
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
        );
        die(Tools::jsonEncode($ret));
    }

    public function ajaxDeleteVariable()
    {
        $id_product_variable = Tools::getValue('id_product_variable');
        $result = array(
            'success' => $this->deleteVariable($id_product_variable),
        );
        exit(Tools::jsonEncode($result));
    }

    public function deleteVariable($id_product_variable)
    {
        $product_variable = new KDProductVariable($id_product_variable);

        return $product_variable->delete();
    }

    public function getAvailableVariables()
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $available_variables = array();
        // available_variables
        $variables = KDVariable::getAllVariables($id_lang);  // sorted by position initially
        foreach ($variables as $f) {
            $available_variables[$f['id_variable']] = array(
                'id' => $f['id_variable'],
                'name' => $f['name'],
                'position' => $f['position'],
                'prefix' => $this->l('Variable'),
            );
        }

        return $available_variables;
    }

    public function saveVariableSelected($id_variable, $id_product)
    {
        $id_product_variable = '';
        $variable = new KDVariable($id_variable);
        $variable_name = $variable->name;
        $options_data = array();
        $id_product = $id_product;
        $id_variable_tooltip = '';
        $formula_name = '';
        if (!$this->saveVariable(
            $id_product_variable,
            $id_variable,
            $id_product,
            $id_variable_tooltip,
            $variable_name,
            $options_data,
            $formula_name,
        )) {
            $this->throwError($this->l('Variable not saved'));
        }
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
        );

        return $ret;
    }



    public function saveVariable(
        $id_product_variable,
        $id_variable,
        $id_product,
        $id_variable_tooltip,
        $variable_name,
        $options_data = array(),
        $formula_name,
        $default_option = '',
        $minimum = null,
        $maximum = null,
        $multiplier = 1
    ) {
        $id_lang = Context::getContext()->language->id;

        if (!$id_product_variable) {
            $product_variable = new KDProductVariable();
        } else {
            $product_variable = new KDProductVariable($id_product_variable);
        }

        $product_variable->active = 1;
        $product_variable->id_variable = $id_variable;
        $product_variable->id_product = $id_product;
        $product_variable->id_variable_tooltip = $id_variable_tooltip;
        $product_variable->formula_name = $formula_name;
        $product_variable->default_option = $default_option;
        $product_variable->minimum = $minimum;
        $product_variable->maximum = $maximum;
        $product_variable->multiplier = $multiplier;
        
        $encoded_options_data = Tools::jsonEncode($options_data);

        $product_variable->options = $encoded_options_data;

        $languages = Language::getLanguages(false);
        // $default_lang_text = Tools::getValue('question_text_'.$id_default_lang);
        foreach ($languages as $lang) {
            $id_lang = (int)$lang['id_lang'];
            $product_variable->name[$id_lang] = $variable_name;

            //$question->text[$id_lang] = Tools::getValue('question_text_'.$id_lang, $default_lang_text);
        }

        $product_variable->save();

        return $product_variable->id;
    }

    // public function saveVariable(
    //     $id_product_variable,
    //     $id_variable,
    // 	$id_product,
    // 	$id_variable_tooltip,
    //     $variable_name,
    //     $options_data = array(),
    //     $formula_name
    // ) {
    //     $id_lang = Context::getContext()->language->id;

    //     if (!$id_product_variable) {
    //         $product_variable = new KDProductVariable();
    // 	}else{
    // 		$product_variable = new KDProductVariable($id_product_variable);
    // 	}

    //     $product_variable->active = 1;
    // 	$product_variable->id_variable = $id_variable;
    // 	$product_variable->id_product = $id_product;
    // 	$product_variable->id_variable_tooltip = $id_variable_tooltip;
    //     $product_variable->formula_name = $formula_name;

    // 	$encoded_options_data = Tools::jsonEncode($options_data);

    // 	$product_variable->options = $encoded_options_data;

    //     $languages = Language::getLanguages(false);
    //    // $default_lang_text = Tools::getValue('question_text_'.$id_default_lang);
    //     foreach ($languages as $lang) {
    //         $id_lang = (int)$lang['id_lang'];
    //         $product_variable->name[$id_lang] = $variable_name;

    //         //$question->text[$id_lang] = Tools::getValue('question_text_'.$id_lang, $default_lang_text);
    //     }

    //     $product_variable->save();

    //     return $product_variable->id;
    // }

    public function parseStr($str)
    {
        $params = array();
        parse_str(str_replace('&amp;', '&', $str), $params);
        return $params;
    }

    public function throwError($errors, $render_html = true)
    {
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        if ($render_html) {
            $html = '<div class="thrown-errors">' . $this->displayError(implode('<br>', $errors)) . '</div>';
            if (!Tools::isSubmit('ajax')) {
                return $html;
            } else {
                $errors = utf8_encode($html);
            }
        }
        die(Tools::jsonEncode(array('errors' => $errors)));
    }


    public function getContent()
    {
        $baned_combs = [];
        // $ps_option = "INSERT INTO " . _DB_PREFIX_ . "option (id_variable, price, position, weight, active)
        //     SELECT 51, price, position, weight, active
        //     FROM ps_option
        //     WHERE id_variable = 5;";

        // $this->db->execute($ps_option);

        // exit;
        // $sql2 = "SELECT id_option FROM `" . _DB_PREFIX_ . "option` WHERE `id_variable` = 5";

        // $options = $this->db->executeS($sql2);
        // $sql3 = "SELECT id_option FROM `" . _DB_PREFIX_ . "option` WHERE `id_variable` = 51";
        // $options51 = $this->db->executeS($sql3);

        // foreach ($options as $key => $option) {
        //     $sql = "INSERT INTO " . _DB_PREFIX_ . "option_lang (id_option, id_lang, label)
        //     SELECT ".$options51[$key]['id_option'].", id_lang, label
        //     FROM ps_option_lang
        //     WHERE id_option = ".$option['id_option'].";";
        //     $this->db->execute($sql);
        // }

        // exit;

        if (Tools::isSubmit('ajax') && $action = Tools::getValue('action')) {
            $this->ajaxAction($action);
        }

        if (Tools::version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            $product_url = 'index.php?controller=AdminProducts&token=' . Tools::getAdminTokenLite('AdminProducts') . '&ajax=1&action=productsList&disableCombination=1';
        } else {
            $product_url = 'ajax_products_list.php?disableCombination=true';
        }
        Media::addJsDef(['st_product_url' => $product_url]);
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        $this->context->controller->addJS(($this->_path) . 'views/js/admin.js');

        $id_product_setting = (int)Tools::getValue('id_product_setting');
        $id_product = (int)Tools::getValue('id_product');

        $id_shop_current = $this->context->shop->id;

        if (Tools::isSubmit('delete' . $this->name) && ($id_product_setting)) {
            if ($id_product_setting) {
                $productsetting = new KDProductSetting($id_product_setting);
                if (Validate::isLoadedObject($productsetting) && $productsetting->delete())
                    Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
            }

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('product' . $this->name) && ($id_product = Tools::getValue('id_product'))) {
            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&id_product=' . $id_product . '&view' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('saveHtml' . $this->name)) {
            $content = Tools::getValue('html_content');

            Configuration::updateValue('html_content', $content, true);

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('duplicate' . $this->name) && ($id_product || $id_product_setting)) {
            if (isset($id_product_setting) and $id_product_setting) {
                $product_setting = new KDProductSetting($id_product_setting);
                $id_product = $product_setting->id_product;
            } else {

                $row =  KDProductSetting::getByProductId($id_product);
                $product_setting = new KDProductSetting($row['id_product_setting']);
            }

            $product = new Product($id_product, false, (int)$this->context->language->id);
            $helper_start_form = $this->initDuplicateForm();
            $helper = $this->initList();
            return $this->_html . $helper_start_form->generateForm($this->fields_form) . $helper->generateList(KDProductSetting::getAll((int)$this->context->language->id), $this->fields_list);
        } elseif (Tools::isSubmit('duplicat' . $this->name) && ($id_product = Tools::getValue('id_product')) && ($id_product_setting = Tools::getValue('id_product_setting'))) {
            if (KDProductSetting::getSettingIdByProductId($id_product)) {
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&error_setting=1');
            }
            if (isset($id_product_setting) and $id_product_setting) {
                $product_setting = new KDProductSetting($id_product_setting);
                $id_product_original = $product_setting->id_product;
            }

            $duplicate_product_setting = new KDProductSetting();
            $duplicate_product_setting->formula_price = $product_setting->formula_price;
            $duplicate_product_setting->formula_weight = $product_setting->formula_weight;
            $duplicate_product_setting->formula_thickness = $product_setting->formula_thickness;
            $duplicate_product_setting->tiered = $product_setting->tiered;
            $duplicate_product_setting->baned_comb = 0;
            $duplicate_product_setting->id_product = $id_product;
            $duplicate_product_setting->save();

            $available_globle_variables = KDVariable::getVariables();
            $available_product_variables = $this->db->executeS('
                SELECT p.*, pl.name 
                FROM ' . _DB_PREFIX_ . 'product_variable p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
                WHERE p.id_product = ' . (int)$id_product_original . '
            ');
            foreach ($available_product_variables as $data) {
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "product_variable` (`id_product`, `id_variable`, `options`,`id_variable_tooltip`,`formula_name`) VALUES (" . $id_product . "," . $data['id_variable'] . ",'" . $data['options'] . "'," . $data['id_variable_tooltip'] . ",'" . $data['formula_name'] . "')";
                Db::getInstance()->execute($sql);
                $id = (int)Db::getInstance()->Insert_ID();
                $sql_lang = 'INSERT INTO `' . _DB_PREFIX_ . 'product_variable_lang` (`id_product_variable`,`id_lang`,`name`) VALUES (' . $id . ',' . $this->context->language->id . ',"' . $data['name'] . '")';
                Db::getInstance()->execute($sql_lang);
            }
            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('view' . $this->name) && ($id_product || $id_product_setting)) {

            if (isset($id_product_setting) and $id_product_setting) {
                $product_setting = new KDProductSetting($id_product_setting);
                $id_product = $product_setting->id_product;
            } else {

                $row =  KDProductSetting::getByProductId($id_product);
                $product_setting = new KDProductSetting($row['id_product_setting']);
            }

            $product = new Product($id_product, false, (int)$this->context->language->id);

            if (Validate::isLoadedObject($product)) {
                $product_html = '<div class=" panel">' . $this->l('Update for') . ' <a href="' . $product->getLink() . '">' . $product->id . '-' . $product->name . '[' . $product->reference . ']' . '</a>
                </div>';
            } else {
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
            }

            $back = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

            $product_name = $product->name;
            $available_globle_variables = KDVariable::getVariables();

            $available_product_variables = $this->db->executeS('
                SELECT p.*, pl.name 
                FROM ' . _DB_PREFIX_ . 'product_variable p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
                WHERE p.id_product = ' . (int)$id_product . '
            ');

            $variable_position = json_decode($product_setting->variable_position, true);
           // print_r($variable_position);
            if($variable_position){
                usort($available_product_variables, function ($a, $b) use ($variable_position) {
                    $pos_a = array_search($a['id_product_variable'], $variable_position);
                    $pos_b = array_search($b['id_product_variable'], $variable_position);
                    return $pos_a - $pos_b;
                });
            }
            
            

            foreach ($available_product_variables as &$data) {
                $varObj = new KDVariable($data['id_variable']);
                $data['variable_name'] = $varObj->name;
                $data['variable_type'] = $varObj->type;
                $options = json_decode($data['options']);
                $options_arr = [];
                foreach ($options as $key => $option) {
                    $optionObj = new KDOption($option);
                    $options_arr[] = $optionObj;
                }
                if (!empty($options_arr)) {
                    $data['options'] = $options_arr;
                }
            }
            //$available_product_variables = KDProductVariable::getAll($id_product);

            $currency = $this->context->currency;
            $default_class = 'highlighted';

            $tiered_price = $this->rendertieredPrice($product_setting->tiered);

            $options = [];
            foreach ($available_globle_variables as $key => $variable) {
                $sql = 'SELECT  a.* , b.*
                    FROM ' . _DB_PREFIX_ . 'option a
                    LEFT JOIN ' . _DB_PREFIX_ . 'option_lang b ON (a.id_option = b.id_option)
                    WHERE b.id_lang = ' . (int)$this->context->language->id . ' AND a.id_variable = ' . $variable['id_variable'] . '
                    ORDER BY a.`position`';
                $variable_options = Db::getInstance()->executeS($sql);
                $var_name = strtolower($variable['name']);
                $var_name = str_replace(' ', '', $var_name);
                if (empty($variable_options)) {
                    // if variable options is empty then it will store variable type it will help in configure.tpl file for display correct input for variable options
                    $variable_options = $variable['type'];
                }
                $options[$variable['id_variable']] = $variable_options;
            }

            $rules_list = KDRuleList::getAllRules($id_product);

            if (count($rules_list)) {
                foreach ($rules_list as &$rules) {
                    $rule_data = json_decode($rules['rule'], true);
                    $rule_text = 'if ';
                    foreach ($rule_data as $rule) {
                        $varObj = new KDVariable($rule['variable']);
                        $variable_name = $varObj->name;
                        $option_value = $rule['option'];
                        if ($varObj->type == 2) {
                            $optionObj = new KDOption($rule['option'], $this->context->language->id);
                            $option_value = $optionObj->label;
                        }

                        if ($rule['and_or_sign'] == 1) {
                            $and_or_sign = ' OR ';
                        } elseif ($rule['and_or_sign'] == 2) {
                            $and_or_sign = ' AND ';
                        } else {
                            $and_or_sign = '';
                        }

                        if ($rule['sign'] == 1) {
                            $sign = '=';
                        } elseif ($rule['sign'] == 2) {
                            $sign = '>';
                        } elseif ($rule['sign'] == 3) {
                            $sign = '<';
                        } else {
                            $sign = '';
                        }

                        $rule_text .= $variable_name . $sign . $option_value . '' . $and_or_sign;
                    }

                    $rules['rule_text'] = $rule_text;

                    $disallow_data = json_decode($rules['disallow'], true);
                    $disallowed = '';
                    foreach ($disallow_data as $disallow) {
                        $varObj = new KDVariable($disallow);
                        $disallowed .= $varObj->name . ' ,';
                    }

                    $rules['disllowed_text'] = $disallowed;
                }
            }
            // exit;
            $sql = 'SELECT *  FROM ' . _DB_PREFIX_ . 'price_for_odd_quantities WHERE product_id = ' . (int) $id_product;
            $price_for_odd_quantities = Db::getInstance()->executeS($sql);

            $this->context->smarty->assign(array(
                'variables' => $available_product_variables,
                'product_name' => $product_name,
                'id_product' => $id_product,
                'product' => $product,
                'link' => $this->context->link,
                'product_combinations' => $baned_combs,
                'product_setting' => $product_setting,
                'globle_variables' => $available_globle_variables,
                'variable_options' => $options,
                'tiered_price' => $tiered_price,
                'back' => $back,
                'product_html' => $product_html,
                'rules_list' => $rules_list,
                'this' => $this,
                'percentage_for_odd_quantity' => $price_for_odd_quantities[0]['percentage']
            ));

            $html = $this->display(__FILE__, 'views/templates/admin/configure.tpl');
            return $html;
        } else {
            $html_error = "";
            if (Tools::getValue('error_setting') == 1) {
                // $this->_html .= Tools::displayError("This Product is already exists!");
                $this->context->controller->errors[] = $this->trans(
                    'This Product is already exists!',
                    [],
                    'Shop.Notifications.Error'
                );
            }
            $helper_start_form = $this->initStartForm();
            $html_form = $this->initHtmlForm();
            $helper = $this->initList();
            // return $this->_html . $helper_start_form->generateForm($this->fields_form)  . $helper->generateList(KDProductSetting::getAll((int)$this->context->language->id), $this->fields_list);
            return $this->_html . $helper_start_form->generateForm($this->fields_form) . $html_form->generateForm($this->html_form) . $helper->generateList(KDProductSetting::getAll((int)$this->context->language->id), $this->fields_list);
        }
    }


    public function renderTieredPrice($tiered_price)
    {
        $current_index = AdminController::$currentIndex;
        $token = Tools::getAdminTokenLite('AdminModules');
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (!isset($back) || empty($back))
            $back = $current_index . '&amp;configure=' . $this->name . '&token=' . $token;


        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));


        $currencies = Currency::getCurrencies(false, true, true);

        $currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $tiered_price = json_decode($tiered_price, true);

        $this->context->smarty->assign(
            array(
                'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),

                'currencies' => $currencies,
                'currentIndex' => $currentIndex,
                'currentToken' => Tools::getAdminTokenLite('AdminModules'),
                'tiered_price' => $tiered_price,
                'base_url' => $this->context->shop->getBaseURL(),
                'language' => array(
                    'id_lang' => $language->id,
                    'iso_code' => $language->iso_code
                ),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id

            )
        );

        return $this->display(__FILE__, 'views/templates/admin/form_tiered_price.tpl');
        //return $this->createTemplate('form_option.tpl')->fetch();

    }


    protected function initList()
    {
        $this->fields_list = array(
            'id_product_setting' => array(
                'title' => $this->l('Id'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'name' => array(
                'title' => $this->l('Product name'),
                'width' => 300,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_product_setting';
        $helper->actions = array('view', 'duplicate', 'delete');
        $helper->show_toolbar = true;
        $helper->imageType = 'jpg';

        $helper->title = $this->l('Products');
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        return $helper;
    }
    protected function initDuplicateForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Select a product to duplicate price setting'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'products' => array(
                    'type' => 'text',
                    'label' => $this->l('Enter product name:'),
                    'name' => 'products',
                    'autocomplete' => false,
                    'class' => 'fixed-width-xxl',
                    'desc' => '<ul id="curr_products"></ul>',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_product',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_product_setting',
                    'value' => Tools::getValue('id_product_setting'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Duplicate'),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'duplicat' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array('products' => '', 'id_product' => '', 'id_product_setting' => Tools::getValue('id_product_setting')),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper;
    }
    protected function initStartForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Select a product to add new price setting or edit existed price setting'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'products' => array(
                    'type' => 'text',
                    'label' => $this->l('Enter product name:'),
                    'name' => 'products',
                    'autocomplete' => false,
                    'class' => 'fixed-width-xxl',
                    'desc' => '<ul id="curr_products"></ul>',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_product',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Continue'),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'product' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array('products' => '', 'id_product' => ''),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper;
    }

    protected function initHtmlForm()
    {
        $this->html_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('HTML Box'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'textarea', // Use textarea type for CKEditor
                    'label' => $this->l('HTML Content'),
                    'name' => 'html_content',
                    'cols' => 40,
                    'rows' => 10,
                    'class' => 'rte',
                    'autoload_rte' => true, // This is important for CKEditor
                ),
            ),
            'submit' => array(
                'title' => $this->l('Continue'),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveHtml' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $content = Configuration::get('html_content');

        $helper->tpl_vars = array(
            'fields_value' => array('html_content' => $content),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper;
    }

    public function getAvailableOptions($include_parents = true)
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $available_options = array();

        // feats
        $options = KDOption::getOptions($id_lang);  // sorted by position initially
        foreach ($options as $f) {
            $available_options['f' . $f['id_option']] = array(
                'id' => $f['id_option'],
                'name' => $f['label'],
                'position' => $f['active'],
                'prefix' => $this->l('Options'),
            );
        }

        return $available_options;
    }


    public function hookDisplayCartExtraProductActions2($params)
    {

        $product = $params['product'];



        if (is_numeric($product['id_product'])) {

            $this->context->smarty->assign(array(
                'product' => $product,
                'url_base' => Tools::getHttpHost(true) . __PS_BASE_URI__,
            ));

            return $this->display(__FILE__, 'views/templates/hook/cart_extra_product_actions.tpl');
        }
    }


    public function hookDisplayHeader()
    {
        $css = $js = '';

        $context = Context::getContext();
        $current_language_id = $context->language->id;
        if (Tools::getValue('controller') == 'product') {
            //$this->loadIconFontIfRequired();

            //$this->addCustomMedia();
            Media::addJsDef(array(
                'kd_ajax_path' => $this->context->link->getModuleLink($this->name, 'ajax', array('ajax' => 1)),
                'current_controller' => Tools::getValue('controller'),
                'is_17' => (int)$this->is_17,
            ));

            $id_product = Tools::getValue('id_product');
            $available_product_variables = $this->db->executeS('
                SELECT p.*,pv.*,pvl.*, pl.name as name, p.maximum as p_maximum,p.minimum as p_minimum
                FROM ' . _DB_PREFIX_ . 'product_variable p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
                LEFT JOIN `' . _DB_PREFIX_ . 'variable` pv ON (pv.`id_variable`= p.`id_variable`)
                LEFT JOIN `' . _DB_PREFIX_ . 'variable_lang` pvl ON (pvl.`id_variable`= p.`id_variable` AND pvl.`id_lang` = ' . (int)$this->context->language->id . ' )
                WHERE p.id_product = ' . (int)$id_product . '
                ORDER BY pv.`position`
            ');
            $rules_list = KDRuleList::getAllRules($id_product);
            $js = 'function rulesForbannedComb() {';

            if (count($rules_list)) {
                $js_data = [];
                $disallowed = [];
                $reset_value_text = '';

                // Reset all elements to initial state (remove disabled class and enable disabled elements)
                $js .= '$("select[data-variable-name] option, input[data-variable-name]").each(function() {'
                    . '$(this).removeClass("disabled");
                    $(this).removeAttr("disabled");'
                    . '});';

                foreach ($rules_list as &$rules) {
                    $rule_data = json_decode($rules['rule'], true);
                    if (!empty($rule_data)) {
                        $rule_text = 'if (';

                        foreach ($rule_data as $rule) {
                            $varObj = new KDVariable($rule['variable']);
                            $variable_name = str_replace(' ', '.', $varObj->name);
                            if ($variable_name) {
                                if ($varObj->type == 2) {
                                    $variable_text = '$("select[data-variable_id=\'' . $rule['variable'] . '\'] option:selected:first").text()';
                                } else {
                                    $variable_text = '$("input[data-variable_id=\'' . $rule['variable'] . '\']").val()';
                                }
                                // $variable_text = '$("input:hidden[data-variable_id=\'' . $rule['variable'] . '\']").val()';
                                $option_value = $rule['option'];
                                if ($varObj->type == 2) {
                                    $optionObj = new KDOption($rule['option'], $this->context->language->id);
                                    $option_value = $optionObj->label;
                                }

                                if ($rule['and_or_sign'] == 1) {
                                    $and_or_sign = ' || ';
                                } elseif ($rule['and_or_sign'] == 2) {
                                    $and_or_sign = ' && ';
                                } else {
                                    $and_or_sign = '';
                                }

                                if ($rule['sign'] == 1) {
                                    $sign = '==';
                                } elseif ($rule['sign'] == 2) {
                                    $sign = '>';
                                } elseif ($rule['sign'] == 3) {
                                    $sign = '<';
                                } else {
                                    $sign = '';
                                }

                                if (!is_numeric($option_value)) {
                                    $option_value = '"' . $option_value . '"';
                                }

                                $rule_text .= $variable_text . $sign . $option_value . $and_or_sign;

                                if (!$and_or_sign) {
                                    $rule_text .= '){';
                                }
                            }
                        }

                        $disallow_data = json_decode($rules['disallow'], true);
                        $disallowed = array_merge($disallowed, $disallow_data);

                        foreach ($disallow_data as $disallow) {
                            if (is_array($disallow)) {
                                $disallow_variables = $disallow['disallow_variable'];
                                $disallow_options = $disallow['disallow_options'];
                                foreach ($disallow_variables as $variable) {
                                    $varObj = new KDVariable($variable);
                                    $rule_text .= '$("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val("");'
                                        // . '$("span[data-variable_id=\'' . $variable . '\']").text("'.$this->l('Bitte auswählen').'");'
                                        . '$("select[data-variable_id=\'' . $variable . '\'] option").each(function() {';
                                    foreach ($disallow_options as $option) {
                                        $optionObj = new KDOption($option);
                                        $name = $optionObj->label[$current_language_id];
                                        $disallowed  .= $name . ', ';

                                        $rule_text .= 'if ($(this).text() === "' . $name . '") {
                                            $("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val("");'
                                            . '
                                            if (!$(this).hasClass("disabled") && $(this).is(":selected")) {'
                                            . '$(".btn-select-value[data-variable_id=\'' . $variable . '\']").val(0);
                                               $(this).parent().prop("selectedIndex", 0).val();'  // Set the default label
                                            . '}
                                            $("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val($(this).parent().find(".selected").attr("data-id_option"));'
                                            . '$(this).addClass("disabled");$(this).attr("disabled", "disabled");'
                                            . '}';
                                    }
                                    $rule_text .= '});'
                                        . 'var ul = $("select[data-variable_id=\'' . $variable . '\']");'
                                        . 'var span = $(".btn-select-value[data-variable_id=\'' . $variable . '\']");'
                                        . 'ul.on("click", "option", function() {'
                                         . 'if ($(this).text() === "' . $name . '") {
                                            $(".btn-select-value[data-variable_id=\'' . $variable . '\']").val(0);
                                            $(this).parent().prop("selectedIndex", 0).val();
                                         }'
                                        . '});';
                                }
                            } else {
                                $varObj = new KDVariable($disallow);
                                $variable_text = '$("span[data-variable_id=\'' . $disallow . '\']").';
                                $rule_text .= $variable_text . 'parent().addClass("disabled");';
                                $rule_text .= '$("#' . $varObj->name . '").prop("disabled", true);';
                            }
                        }

                        $rule_text .= '}';

                        $js_data[] = $rule_text;
                    }
                }

                $disallowed = array_unique($disallowed);

                foreach ($disallowed as $disallow) {
                    $varObj = new KDVariable($disallow);
                    $variable_text = '$("span[data-variable_id=\'' . $variable . '\']").';
                    $reset_value_text .= $variable_text . 'parent().removeClass("disabled");';
                    $reset_value_text .= '$("#' . $varObj->name . '").prop("disabled", false);';
                }

                $js .= implode(PHP_EOL, $js_data);
            }

            $js .= '}';
            $alert_message_list = KDAlertMessage::getAllAlertMessages($id_product);
            $alert_messages_combination = 'function alert_messages_combination(target){';
            if (count($alert_message_list) > 0) {
                $alert_message_js_data = [];

                foreach ($alert_message_list as $key => $alert_message) {
                    $variable = new KDVariable($alert_message['variable_id']);
                    $option = $alert_message['option_id'];
                    $alert_message_text = $alert_message['message'];

                    $optionObj = new KDOption($option, $this->context->language->id);
                    $option_text = $optionObj->label;

                    $message_text = 'var alert_message_text = "";';
                    $message_text .= 'if($(target).attr("data-variable-name") == \'' . $variable->name . '\'){if (';
                    if ($variable->type == 2) {
                        $second_variable_condition_text = '$("select[data-variable-name=\'' . $variable->name . '\'] option:selected").text()';
                    } else {
                        $second_variable_condition_text = '$("input[data-variable-name=\'' . $variable->name . '\']").val()';
                    }
                    $message_text .= $second_variable_condition_text . " == '" . $option_text . "'";
                    $message_text .= ') {';
                    $message_text .= "$('div.modal#alert_message').modal('show');
                        $('div.modal#alert_message #message').html('" . $alert_message_text . "');";
                    $message_text .= '}}';


                    $alert_message_js_data[] = $message_text;
                }
                $alert_messages_combination .= implode(PHP_EOL, $alert_message_js_data);
            }
            $alert_messages_combination .= '}';
            $js .= $alert_messages_combination;
            $this->addJS('front.js');
            $this->addCSS('front.css');
        }
        // echo '<pre>';
        // print_r($js);
        // echo '</pre>';

        return ($css ? '<style type="text/css">' . $css . '</style>' : '')
            . ($js ? '<script type="text/javascript">' . $js . '</script>' : '');
    }


    public function hookdisplayFooterProduct($params)
    {

        //return $this->hookDisplayReassurance($params);
    }


    public function hookDisplayReassurance($params)
    {
        //$product = $params['product'];

        $context = Context::getContext();
        $id_product = Tools::getValue('id_product');
        $id_product_attribute = Tools::getValue('id_product_attribute', 0);
        $id_customization = 0;

        if (!$id_product) {
            return false;
        }

        $priceFormatter = new PriceFormatter();


        $row =  KDProductSetting::getByProductId($id_product);

        if (!$row['id_product_setting'] and $id_product) {
            return false;
        }

        $product_setting = new KDProductSetting($row['id_product_setting']);

        $product = new Product($id_product, false, (int)$this->context->language->id);

        $available_product_variables = $this->db->executeS('
            SELECT p.*,pv.*,pvl.*, pl.name as name, p.maximum as p_maximum,p.minimum as p_minimum
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            LEFT JOIN `' . _DB_PREFIX_ . 'variable` pv ON (pv.`id_variable`= p.`id_variable`)
            LEFT JOIN `' . _DB_PREFIX_ . 'variable_lang` pvl ON (pvl.`id_variable`= p.`id_variable` AND pvl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$id_product.' 
            ORDER BY p.`id_product_variable` ');

        $variable_position = json_decode($product_setting->variable_position, true);
        
        if($variable_position){
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }
        // echo '<pre>';
        // var_dump($available_product_variables);
        // die();
        foreach ($available_product_variables as &$data) {
            $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
            $data['variable_name'] = $varObj->name;
            if (!$data['formula_name']) {
                $data['formula_name'] = $data['variable_name'];
            }
            // $data['minimum'] = $varObj->minimum;
            // $data['maximum'] = $varObj->maximum;
            $data['type'] = $varObj->type;
            $data['fixed_price'] = $varObj->fixed_price;
            $tip = new KDToolTip($data['id_variable_tooltip'], (int)$this->context->language->id);
            $data['tooltip_text'] = $tip->text;

            $options = json_decode($data['options'], true);
            if (is_array($options) && count($options)) {
                $options_implode = implode(",", $options);
                $option_sql = '
    				SELECT  a.`id_option`
    				FROM ' . _DB_PREFIX_ . 'option a
    				WHERE a.`id_option` IN (' . $options_implode . ') AND active = 1
    				ORDER BY a.`position`';
                $option_res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($option_sql);
                if (count($option_res)) {
                    $sortingFunction = function ($a, $b) use ($options) {
                        $pos_a = array_search($a['id_option'], $options);
                        $pos_b = array_search($b['id_option'], $options);
                        return $pos_a - $pos_b;
                    };
                    // Sort the first array using the custom sorting function
                    usort($option_res, $sortingFunction);
                    foreach ($option_res as $option_data) {
                        $option = new KDOption($option_data['id_option'], (int)$this->context->language->id);
                        $data['options_data'][$option->id]['id'] = $option->id;
                        $data['options_data'][$option->id]['name'] = $option->label;
                        $data['options_data'][$option->id]['price'] = $option->price;
                        $data['options_data'][$option->id]['weight'] = $option->weight;
                        $data['options_data'][$option->id]['thickness'] = $option->thickness;
                    }
                }
            }
        }

        $tiered_price = json_decode($product_setting->tiered, true);

        $id_customization = Db::getInstance()->getValue(
            'SELECT cu.`id_customization`
            FROM `' . _DB_PREFIX_ . 'cart_product` cu
            WHERE cu.id_cart = ' . (int)$context->cart->id . '
            AND cu.id_product = ' . (int)$id_product .
                (!empty($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int)$id_product_attribute : '')
        );

        $html_content = Configuration::get('html_content');
        $display_customize_hook = Hook::exec('displayProductButtons');

        $this->context->smarty->assign(array(
            'variables' => $available_product_variables,
            'id_product' => $id_product,
            'id_cart' => $id_product,
            'link' => $this->context->link,
            'product_setting' => $product_setting,
            'tiered_price' => $tiered_price,
            'this' => $this,
            'id_customization' => $id_customization,
            'html_content' => $html_content,
            'display_customize_hook' => $display_customize_hook,

        ));


        return $this->display(__FILE__, 'views/templates/hook/display_footer_product.tpl');
    }

    public function getPriceFactor($value, $type)
    {

        $auto_size_data = array(
            '191'         => array('width' => '842-1188', 'height' => '14001-20000'),
            '134'         => array('width' => '842-1188', 'height' => '9901-14000'),
            '95'          => array('width' => '842-1188', 'height' => '7001-9900'),
            '67'          => array('width' => '842-1188', 'height' => '4951-7000'),
            '48'          => array('width' => '842-1188', 'height' => '3501-4950'),
            '34'          => array('width' => '842-1188', 'height' => '2476-3500'),
            '24'          => array('width' => '842-1188', 'height' => '1751-2475'),
            '17'          => array('width' => '842-1188', 'height' => '1189-1750'),
            '8'           => array('width' => '595-841', 'height' => '842-1188'),
            '4'           => array('width' => '421-594', 'height' => '595-841'),
            '2'           => array('width' => '298-420', 'height' => '421-594'),
            '1'           => array('width' => '211-297', 'height' => '298-420'),
            '0.525'       => array('width' => '149-210', 'height' => '211-297'),
            '0.275625'      => array('width' => '106-148', 'height' => '149-210'),
            '0.144703125'     => array('width' => '75-105', 'height' => '106-148'),
            '0.075969141'    => array('width' => '53-74', 'height' => '75-105'),
            '0.039883799'   => array('width' => '38-52', 'height' => '53-74'),
            '0.020938994'  => array('width' => '27-37', 'height' => '38-52'),
            '0.010992972' => array('width' => '10-26', 'height' => '10-37')
        );
        foreach ($auto_size_data as $key => $size) {
            $size_array = explode('-', $size[$type]);

            if (($value >= $size_array[0] && $value <= $size_array[1])) {
                return $key;
            }
        }

        return 0;
    }

    public function getWireThicknessByRange($value)
    {

        $value = Tools::ps_round($value, 1);
        $auto_thickness_data = array(
            '6.9 mm (3:1)'         => '0-4.5',
            '8.0 mm (3:1)'         => '4.6-6',
            '9.5 mm (3:1)'         => '6.1-7.5',
            '11.0 mm (3:1)'        => '7.6-9',
            '12.7 mm (3:1)'        => '9.1-10.5',
            '14.3 mm (2:1)'        => '10.6-12',
            '16.0 mm (2:1)'        => '12.1-13.5',
            '19 mm (2:1)'          => '13.6-16',
            '22 mm (2:1)'          => '16.1-19',
            '25.4 mm (2:1)'        => '19.1-22',
            '32 mm (2:1)'          => '22.1-25',
            '38 mm (2:1)'          => '25.1-34'
        );
        foreach ($auto_thickness_data as $key => $thickness) {
            $thickness_array = explode('-', $thickness);

            if (($value >= $thickness_array[0] && $value <= $thickness_array[1])) {
                return $key;
            }
        }

        return 0;
    }

    public function ajaxGetFilteredVariables($params)
    {
        $product = new Product($params['id_product'], true, (int)$this->context->language->id);
        $priceFormatter = new PriceFormatter();

        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $price_weight['price'];
        $price_wot = Tools::ps_round($price_wot, 2);
        
        $tax = $price_wot * ($product->tax_rate / 100);
        $tax = Tools::ps_round($tax, 2);
        
        $total = $price_wot + $tax;
        // print('price:'.$price_wot);
        // print('tax:'.$tax);
        // print('total:'.$total);

        $price_data['price_wot'] = $priceFormatter->format($price_wot);
        $price_data['tax'] = $priceFormatter->format($tax);
        $price_data['total'] = $priceFormatter->format($total);


        $price_data['total_weight'] = $price_weight['weight'];
        $price_data['total_thickness'] = $price_weight['thickness'];


        die(json_encode($price_data));
    }

    public function getCalculatedProductPriceWeight($params)
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        require_once(dirname(__FILE__) . "/expression.php");
        $m = new expression;
        $price_data = array();
        $formula_width = $formula_height = $thickness = 0;
        $priceFormatter = new PriceFormatter();

        $autosize_formula_string   = '#customsize#';
        $autothickness_formula_string   = '#wire#';
        
        $width_price_factor = $height_price_factor = 0;


        $product_setting = new KDProductSetting($params['id_product_setting']);
       // $product = new Product($params['id_product'], true, (int)$this->context->language->id);

        $tiered_price = json_decode($product_setting->tiered, true);
        $formula_price = $product_setting->formula_price;
        $formula_weight = $product_setting->formula_weight;
        $formula_thickness = $product_setting->formula_thickness;


        $available_product_variables = $this->db->executeS('
            SELECT p.*, pl.name 
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$params['id_product'] . '
        ');

        $sql = 'SELECT *  FROM ' . _DB_PREFIX_ . 'price_for_odd_quantities WHERE product_id = ' . (int) $params['id_product'];
        $price_for_odd_quantities = Db::getInstance()->executeS($sql);

        foreach ($available_product_variables as $data) {


            if (isset($params['variable_' . $data['id_product_variable']])) {
                $value_price = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 1) {
                    $qty = $params['variable_' . $data['id_product_variable']];
                    $value_weight = $value_price  = $qty;
                    if ($price_for_odd_quantities && $qty % 2 == 1) {
                        $percentage_for_odd_quantity = (int) $price_for_odd_quantities[0]['percentage'];
                        $value_price_with_odd = $value_price * $percentage_for_odd_quantity / 100;
                        $value_price += $value_price_with_odd;
                    }
                } elseif ($varObj->type == 2) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    $value_weight = $option->weight;
                    $value_thickness = $option->thickness;
                } elseif ($varObj->type == 4) {
                    $custom_input = $params['variable_' . $data['id_product_variable']];
                    $value_weight = $value_price = $value_thickness = $custom_input;

                    if ($data['formula_name'] == 'breedte') {
                        // $width_price_factor = $this->getPriceFactor($custom_input, 'width');
                        $formula_width = $custom_input;
                    }

                    if ($data['formula_name'] == 'hoogte') {
                        // $height_price_factor = $this->getPriceFactor($custom_input, 'height');
                        $formula_height = $custom_input;
                    }
                } elseif ($varObj->type == 3) {
                    $value_weight = $value_price = $varObj->fixed_price;
                }

                //$name = '[variable_'.$data['id_product_variable'].']';

                $name =  '[' . $data['formula_name'] . ']';

                if ($value_price) {
                    $formula_price = str_replace($name, $value_price, $formula_price);
                }

                if ($value_weight) {
                    $formula_weight = str_replace($name, $value_weight, $formula_weight);
                }

                if ($formula_thickness and $value_thickness) {
                    $formula_thickness = str_replace($name, $value_thickness, $formula_thickness);
                }
            }
        }

        if ($formula_width && $formula_height) {
            if ($formula_width > $formula_height) {
                $width_price_factor = $this->getPriceFactor($formula_height, 'width');
                $height_price_factor = $this->getPriceFactor($formula_width, 'height');
            } else {
                $width_price_factor = $this->getPriceFactor($formula_width, 'width');
                $height_price_factor = $this->getPriceFactor($formula_height, 'height');
            }
        }
        //echo  $formula_width.'/'.$formula_height;
        if ($width_price_factor || $height_price_factor) {
            $final_price_factor = max($width_price_factor, $height_price_factor);
            if (strpos($formula_price, $autosize_formula_string) !== false) {
                $formula_price = str_replace($autosize_formula_string, $final_price_factor, $formula_price);
            }
        }

        $formula_price = str_replace("[", "", $formula_price);
        $formula_price = str_replace("]", "", $formula_price);
        $formula_price = preg_replace('/[A-Z][a-z]+/', '0', $formula_price);

       
        $price_wot = $m->evaluate($formula_price);

        $discount = 1;

        if (count($tiered_price)) {

            foreach ($tiered_price as $tired) {
                if ($qty >= $tired['from_quantity']) {
                    $discount = $tired['price'];
                    $discount = $discount / 100;
                }
            }
        }

        $customerGroupId = $this->context->customer->id_default_group;
        $customerGroup = new Group($customerGroupId);
        $discounted_price = $price_wot * $customerGroup->reduction / 100;
        $price_wot = $price_wot - $discounted_price;

        $price_wot = $discount * $price_wot;

        $weight = $m->evaluate($formula_weight);

        if($formula_thickness){
            if (strpos($formula_thickness, $autothickness_formula_string) !== false) {
                $formula_thickness = str_replace($autothickness_formula_string, '', $formula_thickness);
                $thickness = $m->evaluate($formula_thickness);
                $thickness = $this->getWireThicknessByRange($thickness);
    
            }else{
                $thickness = $m->evaluate($formula_thickness);

                $thickness = $thickness.' mm';
            }
        }
       
        return array('price' => $price_wot, 'weight' => $weight, 'thickness' => $thickness);

    }

    public function ajaxAddToCart($params)
    {
        //ini_set('display_errors', 1);
        //ini_set('display_startup_errors', 1);
        //error_reporting(E_ALL);

        
        $priceFormatter = new PriceFormatter();


        $product_setting = new KDProductSetting($params['id_product_setting']);
        $product = new Product($params['id_product'], false, (int)$this->context->language->id);

        $id_product_attribute = $params['id_product_attribute'];

        if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = (int) $this->context->cart->id;
        }

        $id_customization = $this->context->cart->saveCustomization($product->id, $id_product_attribute);
        // $id_customization = $params['id_customization'];
        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $price_weight['price'];
        
       // $tax = $price_wot * ($product->tax_rate / 100);
        // $total = $price_wot + $tax;
        
        // $total_weight = 1.2;
        $total_weight = $price_weight['weight'];
        $total_thickness = $price_weight['thickness'];


        $available_product_variables = $this->db->executeS('
            SELECT p.*, pl.name 
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$params['id_product'] . '
            ORDER BY p.`id_product_variable`
        ');
        $variable_position = json_decode($product_setting->variable_position, true);
        
        if($variable_position){
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }
        $send['error'] = false;
        $count = 1;
        foreach ($available_product_variables as $data) {
            // if(isset($params['variable_'.$data['id_product_variable']]) AND !empty($params['variable_'.$data['id_product_variable']])){
            if (isset($params['variable_' . $data['id_product_variable']])) {
                $count++;
                $value_price = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 2) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    $value_weight = $option->weight;
                    $value = $option->label;

                    $options = $this->db->getValue('
                        SELECT p.options
                        FROM ' . _DB_PREFIX_ . 'product_variable p
                        WHERE p.id_product_variable = ' . (int)$data['id_product_variable'] . '
                    ');

                    $options = json_decode($options, true);
                    if(in_array($id_option, $options)){
                        $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                    }else{
                        $send['error'] = "Please select only from avalible options";
                    }

                    
                } elseif ($varObj->type == 1) {
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value, $price_wot, $total_weight);
                } elseif ($varObj->type == 3) {
                    $value = $varObj->fixed_price;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 4) {
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 5) { // type 5 is for custom text input
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 6) { // type 6 is for thickness text input
                    $value = $total_thickness;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } else {
                $send['error'] = "Please select all options";
            }
        }
        $send['id_customization'] = $id_customization;
        die(json_encode($send));
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->addJqueryBO();
        $this->context->controller->addJqueryUI('ui.tooltip');
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->css_files[$this->_path . 'views/css/back.css?v=' . $this->version] = 'all';
        if ($this->is_17) {
            $this->context->controller->css_files[$this->_path . 'views/css/back-17.css?' . $this->version] = 'all';
        }
        $this->context->controller->js_files[] = $this->_path . 'views/js/back.js?v=' . $this->version;
        $js_def = array(
            'indexingTxt' => $this->l('Indexation is in progress... Please do not close this tab'),
            'indexingSuccessTxt' => $this->l('Ready!'),
            'savedTxt' => $this->saved_txt,
            'errorTxt' => $this->error_txt,
            'deletedTxt' => $this->l('Deleted'),
            'areYouSureTxt' => $this->l('Are you sure?'),
        );
        // plain js for retro-compatibility
        $js = '<script type="text/javascript">';
        foreach ($js_def as $name => $value) {
            $js .= "\nvar $name = '" . $this->escapeApostrophe($value) . "';";
        }
        $js .= "\n</script>";

        return $js;
    }

    public function addJqueryBO()
    {
        if (empty($this->context->jqueryAdded)) {
            version_compare(_PS_VERSION_, '7.6.0', '>=') ? $this->context->controller->setMedia() :
                $this->context->controller->addJquery();
            $this->context->jqueryAdded = 1;
        }
    }

    public function escapeApostrophe($string)
    {
        return str_replace("'", "\'", $string);
    }

    public function getAvailableLanguages($only_ids = false)
    {
        $available_languages = array();
        foreach (Language::getLanguages(false) as $lang) {
            $available_languages[$lang['id_lang']] = $lang['iso_code'];
        }
        return $only_ids ? array_keys($available_languages) : $available_languages;
    }

    public function addJS($file_name, $custom_path = '')
    {
        $path = ($custom_path ? $custom_path : 'modules/' . $this->name . '/views/js/') . $file_name;
        if ($this->is_17) {
            // priority should be more than 90 in order to be loaded after jqueryUI
            $params = array('server' => $custom_path ? 'remote' : 'local', 'priority' => 100);
            $this->context->controller->registerJavascript(sha1($path), $path, $params);
        } else {
            $path = $custom_path ? $path : __PS_BASE_URI__ . $path;
            $this->context->controller->addJS($path);
            // $this->context->controller->js_files[] = $path.'?'.microtime(true); // debug
        }
    }

    public function addCSS($file_name, $custom_path = '', $media = 'all')
    {
        $path = ($custom_path ? $custom_path : 'modules/' . $this->name . '/views/css/') . $file_name;
        if ($this->is_17) {
            $params = array('media' => $media, 'server' => $custom_path ? 'remote' : 'local');
            $this->context->controller->registerStylesheet(sha1($path), $path, $params);
        } else {
            $path = $custom_path ? $path : __PS_BASE_URI__ . $path;
            $this->context->controller->addCSS($path, $media);
            // $this->context->controller->css_files[$path.'?'.microtime(true)] = $media; // debug
        }
    }
}
