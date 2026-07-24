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

require_once(dirname(__FILE__) . '/vendor/autoload.php');

require_once(dirname(__FILE__) . '/libraries/krupaludev/Helpers.php');
require_once(dirname(__FILE__) . '/libraries/KDVariable.php');
require_once(dirname(__FILE__) . '/libraries/KDOption.php');
require_once(dirname(__FILE__) . '/libraries/KDToolTip.php');
require_once(dirname(__FILE__) . '/libraries/KDRuleList.php');
require_once(dirname(__FILE__) . '/libraries/KDProductSetting.php');
require_once(dirname(__FILE__) . '/libraries/KDAlertMessage.php');
require_once(dirname(__FILE__) . '/libraries/KDProductVariable.php');
require_once(dirname(__FILE__) . '/libraries/KDReconfigureReorder.php');
require_once(dirname(__FILE__) . '/libraries/KDBannedCombinationValidator.php');
require_once(dirname(__FILE__) . '/libraries/ProductFormulaCondition.php');

require_once(dirname(__FILE__) . '/offer/classes/Offer.php');
require_once(dirname(__FILE__) . '/offer/classes/OfferProduct.php');
require_once(dirname(__FILE__) . '/offer/classes/OfferHistory.php');
require_once(dirname(__FILE__) . '/offer/classes/OfferAttachment.php');
require_once(dirname(__FILE__) . '/offer/services/OfferHistoryLogger.php');
require_once(dirname(__FILE__) . '/offer/services/OfferEmailNotifier.php');
require_once(dirname(__FILE__) . '/offer/services/OfferPdfGenerator.php');
require_once(dirname(__FILE__) . '/offer/services/OfferService.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class ProductPriceConfig
 */
class ProductPriceConfig extends Module
{
    private $_html = '';
    public $fields_form;
    public $html_form;
    public $trusted_badge_form;
    public $upload_form;
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
            && $this->registerHook('displayQuoteRequest')
            && $this->registerHook('displayReassurance')

            && $this->registerHook('displayCartExtraProductActions')
            && $this->registerHook('displayMyAccountOrder')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('actionAdminCustomersControllerAfter')
        ) {
            $sql1 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "variable` (
                `id_variable`   BIGINT(20)  UNSIGNED    NOT NULL AUTO_INCREMENT,
                `name` varchar(125) NOT NULL,
                `printformer_name` varchar(255) DEFAULT NULL,
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
              `printformer_name` varchar(255) DEFAULT NULL,
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

            $sql13 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_formula_condition` (
                `id_formula_condition` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_product` int(10) unsigned NOT NULL,
                `id_variable` int(10) unsigned NOT NULL,
                `id_option` int(10) unsigned NOT NULL DEFAULT '0',
                `surcharge_type` varchar(20) NOT NULL DEFAULT 'percentage',
                `amount` decimal(20,4) NOT NULL DEFAULT '0.0000',
                `apply_type` varchar(20) NOT NULL DEFAULT 'total',
                `notes` text,
                `active` int(1) unsigned NOT NULL DEFAULT '1',
                `position` int(10) unsigned NOT NULL DEFAULT '0',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_formula_condition`),
                KEY `id_product` (`id_product`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            Db::getInstance()->execute($sql13);

            // Offer / Quotation tables
            $sqlOffer1 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "offer` (
                `id_offer` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `reference` varchar(64) NOT NULL,
                `title` varchar(255) DEFAULT NULL,
                `id_customer` int(10) unsigned DEFAULT NULL,
                `id_cart` int(10) unsigned DEFAULT NULL,
                `id_order` int(10) unsigned DEFAULT NULL,
                `guest_email` varchar(255) DEFAULT NULL,
                `guest_name` varchar(255) DEFAULT NULL,
                `customer_note` text,
                `admin_note` text,
                `status` varchar(32) NOT NULL DEFAULT 'draft',
                `expire_date` date DEFAULT NULL,
                `total_products` int(10) unsigned NOT NULL DEFAULT 0,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_offer`),
                KEY `reference` (`reference`),
                KEY `id_customer` (`id_customer`),
                KEY `status` (`status`),
                KEY `expire_date` (`expire_date`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sqlOffer2 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "offer_product` (
                `id_offer_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_offer` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `id_product_attribute` int(10) unsigned DEFAULT 0,
                `id_currency` int(10) unsigned DEFAULT NULL,
                `configuration_json` text,
                `uploaded_file` varchar(500) DEFAULT NULL,
                `quantity` int(10) unsigned NOT NULL DEFAULT 1,
                `offered_price` decimal(20,4) DEFAULT NULL,
                `discounted_amount` decimal(20,4) DEFAULT NULL,
                `weight` decimal(20,2) DEFAULT NULL,
                `production_time` varchar(255) DEFAULT NULL,
                `product_status` varchar(32) NOT NULL DEFAULT 'waiting_for_price',
                `admin_note` text,
                `customer_note` text,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_offer_product`),
                KEY `id_offer` (`id_offer`),
                KEY `id_product` (`id_product`),
                KEY `product_status` (`product_status`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sqlOffer3 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "offer_history` (
                `id_offer_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_offer` int(10) unsigned NOT NULL,
                `id_offer_product` int(10) unsigned DEFAULT NULL,
                `id_customer` int(10) unsigned DEFAULT NULL,
                `id_employee` int(10) unsigned DEFAULT NULL,
                `action` varchar(64) NOT NULL,
                `previous_value` text,
                `new_value` text,
                `description` text,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_offer_history`),
                KEY `id_offer` (`id_offer`),
                KEY `id_offer_product` (`id_offer_product`),
                KEY `action` (`action`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            $sqlOffer4 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "offer_attachment` (
                `id_offer_attachment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_offer_product` int(10) unsigned NOT NULL,
                `original_name` varchar(255) NOT NULL,
                `saved_name` varchar(255) NOT NULL,
                `extension` varchar(20) NOT NULL,
                `mime_type` varchar(100) NOT NULL,
                `size` int(10) unsigned DEFAULT NULL,
                `width` int(10) unsigned DEFAULT NULL,
                `height` int(10) unsigned DEFAULT NULL,
                `uploaded_by` int(10) unsigned DEFAULT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_offer_attachment`),
                KEY `id_offer_product` (`id_offer_product`)
              ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

            Db::getInstance()->execute($sqlOffer1);
            Db::getInstance()->execute($sqlOffer2);
            Db::getInstance()->execute($sqlOffer3);
            Db::getInstance()->execute($sqlOffer4);

            // Default offer validity setting
            Configuration::updateValue('PPC_OFFER_VALIDITY_DAYS', 15);

            // Offer admin tab under ProductPriceConfig parent
            
            $id_tab = Tab::getIdFromClassName('AdminCatalog');
            $this->installModuleTab('AdminProductPriceConfigHome', array((int)$this->context->language->id => 'Manage Product Price'), 0);
            $id_tab = Tab::getIdFromClassName('AdminProductPriceConfigHome');

            $this->installModuleTab('AdminProductPriceConfig', array((int)$this->context->language->id => 'Product price config'), $id_tab);
            $this->installModuleTab('AdminVariable', array((int)$this->context->language->id => 'Variables'), $id_tab);
            $this->installModuleTab('AdminVariableToolTip', array((int)$this->context->language->id => 'Tool Tips'), $id_tab);
            $this->installModuleTab('AdminAlertMessages', array((int)$this->context->language->id => 'Alert Messages'), $id_tab);
            $this->installModuleTab('AdminOffers', array((int)$this->context->language->id => 'Offers'), $id_tab);

            $this->installModuleTab('AdminProductPriceExport', array((int)$this->context->language->id => 'Export Configuration'), $id_tab);
            $this->installModuleTab('AdminProductPriceImport', array((int)$this->context->language->id => 'Import Configuration'), $id_tab);

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

        // Remove offer tab
        $this->uninstallModuleTab('AdminOffers', $id_tab);
        // Remove offer configuration
        // Configuration::deleteByName('PPC_OFFER_VALIDITY_DAYS');

        if (parent::uninstall() && $this->uninstallModuleTab('AdminVariable', $id_tab) && $this->uninstallModuleTab('AdminVariableToolTips', $id_tab) && $this->uninstallModuleTab('AdminProductPriceConfig', $id_tab) && $this->uninstallModuleTab('AdminProductPriceExport', $id_tab) && $this->uninstallModuleTab('AdminProductPriceImport', $id_tab) && $this->uninstallModuleTab('AdminProductPriceConfigHome', 0)) {
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
                $id_product_variable = Tools::getValue('id_product_variable');
                $active = Tools::getValue('active');
                $ret = array('success' => $this->toggleActiveStatus($id_product_variable, $active));
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
            case 'AjaxGetRuleData':
                $this->AjaxGetRuleData();
                break;
            case 'AjaxUpdateRule':
                $this->AjaxUpdateRule();
                break;
            case 'FcList':
                $this->fcList();
                break;
            case 'FcCreate':
                $this->fcCreate();
                break;
            case 'FcGetCondition':
                $this->fcGetCondition();
                break;
            case 'FcUpdate':
                $this->fcUpdate();
                break;
            case 'FcDelete':
                $this->fcDelete();
                break;
            case 'FcToggleStatus':
                $this->fcToggleStatus();
                break;
            case 'FcGetVariableInfo':
                $this->fcGetVariableInfo();
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function toggleActiveStatus($id_product_variable, $active)
    {

        $update_query = '
            UPDATE ' . _DB_PREFIX_ . 'product_variable
            SET active = ' . (int)$active . '
            WHERE id_product_variable = ' . (int)$id_product_variable . '
        ';
        return $this->db->execute($update_query);
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
        $selected_options = Tools::getValue('selected_options');
        $selected_options = explode(',', $selected_options);
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
                $html .= "<option value='" . $id_option . "'" . (in_array($id_option, $selected_options) ? " selected" : "") . ">" . $label . "</option>";
            }
            $html .= "</select>";
            $data['type'] = 'select';
        } else {
            $data['type'] = 'input';
            if ($variable_options == 1 || $variable_options == 4) {
                $html .= '<input type="number" class="form-control options options' . $count . '" data-id_variable="' . $id_variable . '" name="disallow_options' . $count . '[]" value="' . $selected_options[0] . '">';
            }
            if ($variable_options == 5) {
                $html .= '<input type="text" class="form-control options options' . $count . '" data-id_variable="' . $id_variable . '" name="disallow_options' . $count . '[]" value="' . $selected_options[0] . '">';
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
                $rules['actions'] = '<a href="#" class="edit-rule" data-id_rule="' . $rules['id_rule_list'] . '"><i class="fa fa-pencil" aria-hidden="true"></i></a> <a href="" class="delete-rule" data-id_rule="' . $rules['id_rule_list'] . '"><i class="fa fa-trash" aria-hidden="true"></i></a>';
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

        foreach (array('qty', 'price') as $type) {
            ${$type} = Tools::getValue($type);
            if (!is_array(${$type})) ${$type} = array();
        }

        // tier basis formula (optional) - stored inside the tiered JSON payload
        $tier_basis_formula = Tools::getValue('tier_basis_formula', '');

        if ($total = count($qty)) {
            for ($i = 0; $i < $total; $i++) {
                $values[$i] = array();
                $values[$i]['from_quantity'] = (isset($qty[$i]) ? (int) $qty[$i] : 0);
                // price now represents multiplier percentage (e.g., 110 => 110%)
                $values[$i]['price'] = (isset($price[$i]) ? (float) $price[$i] : 0);
            }
        }

        $product_setting->id_product = $id_product;

        // store both basis_formula and tiers in one JSON field
        $payload = array(
            'basis_formula' => $tier_basis_formula,
            'tiers' => $values,
        );

        $product_setting->tiered = json_encode($payload);
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


        for ($i = 1; $i <= $count; $i++) {
            $variable = 'variables' . $i;
            $sign = 'sign' . $i;
            $option = 'options' . $i;
            $and_or_sign = 'and_or_sign' . $i;
            $options = implode('', array_filter($_POST[$option]));
            $rule[] = array('variable' => $_POST[$variable], 'sign' => $_POST[$sign], 'option' => $options, 'and_or_sign' => $_POST[$and_or_sign]);
        }

        for ($i = 1; $i <= $cloneOptionCount; $i++) {
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

    public function AjaxGetRuleData()
    {
        $id_rule_list = Tools::getValue('id_rule_list');
        $rule = new KDRuleList($id_rule_list);
        if (Validate::isLoadedObject($rule)) {
            die(json_encode(array('success' => true, 'rule' => (array) $rule)));
        } else {
            die(json_encode(array('success' => false, 'message' => 'Rule not found.')));
        }
    }

    public function AjaxUpdateRule()
    {
        $id_rule_list = (int) Tools::getValue('id_rule_list');
        $rule_list = new KDRuleList($id_rule_list);

        if (!Validate::isLoadedObject($rule_list)) {
            die(Tools::jsonEncode([
                'success' => false,
                'message' => 'Invalid rule ID'
            ]));
        }

        $rule = [];
        $disallowarr = [];

        $rule_name = Tools::getValue('rule_name');
        $id_product = (int) Tools::getValue('id_product');

        // 🔒 IMPORTANT: counts MUST be sent from JS
        $count = (int) Tools::getValue('count', 0);
        $cloneOptionCount = (int) Tools::getValue('cloneOptionCount', 0);

        // ===== RULES =====
        for ($i = 1; $i <= $count; $i++) {
            if (
                !isset($_POST["variables$i"], $_POST["sign$i"], $_POST["and_or_sign$i"])
            ) {
                continue;
            }

            $optionsKey = "options$i";
            $options = [];

            // Normalize options (array OR scalar)
            if (isset($_POST[$optionsKey])) {
                if (is_array($_POST[$optionsKey])) {
                    $options = array_filter($_POST[$optionsKey]);
                } else {
                    $options = [(string) $_POST[$optionsKey]];
                }
            }

            $rule[] = [
                'variable' => $_POST["variables$i"],
                'sign' => $_POST["sign$i"],
                'option' => implode('', $options),
                'and_or_sign' => $_POST["and_or_sign$i"],
            ];
        }

        // ===== DISALLOW =====
        for ($i = 1; $i <= $cloneOptionCount; $i++) {
            if (!isset($_POST["disallow$i"])) {
                continue;
            }

            $disallow_options = [];

            if (isset($_POST["disallow_options$i"])) {
                $disallow_options = is_array($_POST["disallow_options$i"])
                    ? $_POST["disallow_options$i"]
                    : explode(",", $_POST["disallow_options$i"]);
            }

            $disallowarr[] = [
                'disallow_variable' => $_POST["disallow$i"],
                'disallow_options' => $disallow_options,
            ];
        }
        
        // ===== SAVE =====
        $rule_list->name = $rule_name;
        $rule_list->id_product = $id_product;
        $rule_list->rule = Tools::jsonEncode($rule);
        $rule_list->disallow = Tools::jsonEncode($disallowarr);

        $rule_list->update();
        if (Db::getInstance()->getNumberError() === 0) {
            echo Tools::jsonEncode([
                'success' => true,
                'message' => 'Rule updated correctly'
            ]);
        } else {
            echo Tools::jsonEncode([
                'success' => false,
                'message' => Db::getInstance()->getMsgError()
            ]);
        }
        die();
    }

    // ──────────────────────────────────────────────
    //  FORMULA CONDITIONS AJAX HANDLERS
    // ──────────────────────────────────────────────

    public function fcList()
    {
        $id_product = (int) Tools::getValue('id_product');
        $conditions = ProductFormulaCondition::getAllConditions($id_product);
        $id_lang = (int) $this->context->language->id;

        // Build a map of id_variable => product variable name for this product
        $product_variables = Db::getInstance()->executeS('
            SELECT pv.id_variable, pvl.name
            FROM ' . _DB_PREFIX_ . 'product_variable pv
            LEFT JOIN ' . _DB_PREFIX_ . 'product_variable_lang pvl
                ON (pv.id_product_variable = pvl.id_product_variable AND pvl.id_lang = ' . $id_lang . ')
            WHERE pv.id_product = ' . $id_product
        ) ?: [];

        $var_names = [];
        foreach ($product_variables as $pv) {
            $var_names[(int) $pv['id_variable']] = $pv['name'];
        }

        $html = '';
        if (empty($conditions)) {
            $html = '<tr><td colspan="9" class="text-center">No conditions found.</td></tr>';
        } else {
            foreach ($conditions as $c) {
                $var_name = isset($var_names[(int) $c['id_variable']])
                    ? $var_names[(int) $c['id_variable']]
                    : '--';

                // Get variable type to determine "For Value" display
                $varObj = new KDVariable((int) $c['id_variable'], $id_lang);
                $for_value = '--';
                if (Validate::isLoadedObject($varObj) && $varObj->type == 2 && $c['id_option']) {
                    $option = new KDOption((int) $c['id_option'], $id_lang);
                    $for_value = Validate::isLoadedObject($option) ? $option->label : '--';
                } elseif ($c['id_option']) {
                    $for_value = $c['id_option'];
                }

                $surcharge_label = $c['surcharge_type'] === 'percentage'
                    ? '<span class="fc-badge fc-badge-percentage">Percentage</span>'
                    : '<span class="fc-badge fc-badge-fixed">Fixed</span>';

                $apply_label = $c['apply_type'] === 'total'
                    ? '<span class="fc-badge fc-badge-total">Total Price</span>'
                    : '<span class="fc-badge fc-badge-per-unit">Per Unit</span>';

                $unit = $c['surcharge_type'] === 'percentage' ? '%' : '&euro;';
                $checked = $c['active'] ? 'checked' : '';

                $html .= '<tr>';
                $html .= '<td><input type="checkbox" class="fc-toggle-active" data-id="' . (int) $c['id_formula_condition'] . '" ' . $checked . '></td>';
                $html .= '<td>' . htmlspecialchars($var_name, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . $for_value . '</td>';
                $html .= '<td>' . $surcharge_label . '</td>';
                $html .= '<td>' . $c['amount'] . '</td>';
                $html .= '<td>' . $unit . '</td>';
                $html .= '<td>' . $apply_label . '</td>';
                $html .= '<td>' . htmlspecialchars($c['notes'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td class="fc-actions">';
                $html .= '<a href="#" class="fc-edit" data-id="' . (int) $c['id_formula_condition'] . '"><i class="fa fa-edit"></i> Edit</a>';
                $html .= '<a href="#" class="fc-delete" data-id="' . (int) $c['id_formula_condition'] . '"><i class="fa fa-trash"></i> Delete</a>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }

        die(Tools::jsonEncode(['success' => true, 'html' => $html]));
    }

    public function fcCreate()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_variable = (int) Tools::getValue('id_variable');
        $id_option = (int) Tools::getValue('id_option');
        $custom_value = Tools::getValue('custom_value');
        $surcharge_type = Tools::getValue('surcharge_type');
        $amount = (float) Tools::getValue('amount');
        $apply_type = Tools::getValue('apply_type');
        $notes = Tools::getValue('notes');
        $active = (int) Tools::getValue('active') ? 1 : 0;

        if (!$id_product || !$id_variable || !$surcharge_type || !$amount) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Missing required fields']));
        }

        // For non-select variables, store custom_value as id_option
        if ($custom_value !== '' && $custom_value !== null) {
            $id_option = (float) $custom_value;
        }

        $cond = new ProductFormulaCondition();
        $cond->id_product = $id_product;
        $cond->id_variable = $id_variable;
        $cond->id_option = $id_option;
        $cond->surcharge_type = $surcharge_type;
        $cond->amount = $amount;
        $cond->apply_type = $apply_type;
        $cond->notes = $notes;
        $cond->active = $active;
        $cond->position = 0;
        $cond->date_add = date('Y-m-d H:i:s');
        $cond->date_upd = date('Y-m-d H:i:s');

        if ($cond->save()) {
            die(Tools::jsonEncode(['success' => true, 'message' => 'Condition created']));
        }
        die(Tools::jsonEncode(['success' => false, 'message' => 'Failed to create condition']));
    }

    public function fcGetCondition()
    {
        $id = (int) Tools::getValue('id_formula_condition');
        $row = ProductFormulaCondition::getCondition($id);
        if (!$row) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Not found']));
        }

        // Get variable type for frontend
        $varObj = new KDVariable((int) $row['id_variable'], (int) $this->context->language->id);
        $row['type'] = (int) $varObj->type;

        die(Tools::jsonEncode(['success' => true, 'condition' => $row]));
    }

    public function fcUpdate()
    {
        $id = (int) Tools::getValue('id_formula_condition');
        $id_product = (int) Tools::getValue('id_product');
        $id_variable = (int) Tools::getValue('id_variable');
        $id_option = (int) Tools::getValue('id_option');
        $custom_value = Tools::getValue('custom_value');
        $surcharge_type = Tools::getValue('surcharge_type');
        $amount = (float) Tools::getValue('amount');
        $apply_type = Tools::getValue('apply_type');
        $notes = Tools::getValue('notes');
        $active = (int) Tools::getValue('active') ? 1 : 0;

        if (!$id || !$id_product || !$id_variable || !$surcharge_type || !$amount) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Missing required fields']));
        }

        // For non-select variables, store custom_value as id_option
        if ($custom_value !== '' && $custom_value !== null) {
            $id_option = (float) $custom_value;
        }

        $cond = new ProductFormulaCondition($id);
        if (!Validate::isLoadedObject($cond)) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Condition not found']));
        }

        $cond->id_product = $id_product;
        $cond->id_variable = $id_variable;
        $cond->id_option = $id_option;
        $cond->surcharge_type = $surcharge_type;
        $cond->amount = $amount;
        $cond->apply_type = $apply_type;
        $cond->notes = $notes;
        $cond->active = $active;
        $cond->date_upd = date('Y-m-d H:i:s');

        if ($cond->update()) {
            die(Tools::jsonEncode(['success' => true, 'message' => 'Condition updated']));
        }
        die(Tools::jsonEncode(['success' => false, 'message' => 'Failed to update condition']));
    }

    public function fcDelete()
    {
        $id = (int) Tools::getValue('id_formula_condition');
        $cond = new ProductFormulaCondition($id);
        if (!Validate::isLoadedObject($cond)) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Condition not found']));
        }
        if ($cond->delete()) {
            die(Tools::jsonEncode(['success' => true, 'message' => 'Condition deleted']));
        }
        die(Tools::jsonEncode(['success' => false, 'message' => 'Failed to delete condition']));
    }

    public function fcToggleStatus()
    {
        $id = (int) Tools::getValue('id_formula_condition');
        $active = (int) Tools::getValue('active') ? 1 : 0;

        if (ProductFormulaCondition::FCtoggleStatus($id, $active)) {
            die(Tools::jsonEncode(['success' => true, 'message' => 'Status updated']));
        }
        die(Tools::jsonEncode(['success' => false, 'message' => 'Failed to toggle status']));
    }

    public function fcGetVariableInfo()
    {
        $id_variable = (int) Tools::getValue('id_variable');
        $id_product = (int) Tools::getValue('id_product');
        $id_lang = (int) $this->context->language->id;

        $varObj = new KDVariable($id_variable, $id_lang);
        if (!Validate::isLoadedObject($varObj)) {
            die(Tools::jsonEncode(['success' => false, 'message' => 'Variable not found']));
        }

        $options = [];
        if ($varObj->type == 2) {
            // Load only options assigned to this product for this variable, active only
            $product_variable = Db::getInstance()->getRow('
                SELECT options
                FROM ' . _DB_PREFIX_ . 'product_variable
                WHERE id_product = ' . $id_product . '
                AND id_variable = ' . $id_variable
            );

            $assigned_option_ids = [];
            if ($product_variable && $product_variable['options']) {
                $decoded = json_decode($product_variable['options'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $opt_id) {
                        $assigned_option_ids[] = (int) $opt_id;
                    }
                }
            }

            if (!empty($assigned_option_ids)) {
                $sql = '
                    SELECT a.id_option, b.label
                    FROM ' . _DB_PREFIX_ . 'option a
                    LEFT JOIN ' . _DB_PREFIX_ . 'option_lang b ON (a.id_option = b.id_option)
                    WHERE b.id_lang = ' . $id_lang . '
                    AND a.id_variable = ' . $id_variable . '
                    AND a.active = 1
                    AND a.id_option IN (' . implode(',', $assigned_option_ids) . ')
                    ORDER BY a.position
                ';
                $all_options = Db::getInstance()->executeS($sql);
                if ($all_options) {
                    foreach ($all_options as $opt) {
                        $options[] = [
                            'id_option' => $opt['id_option'],
                            'label' => $opt['label'],
                        ];
                    }
                }
            }
        }

        die(Tools::jsonEncode([
            'success' => true,
            'type' => (int) $varObj->type,
            'options' => $options,
        ]));
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
        $product_setting->formula_shipping = Tools::getValue('formula_shipping');


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
        $id_variable_tooltip = '';
        $formula_name = '';
        if (!$this->saveVariable(
            $id_product_variable,
            $id_variable,
            $id_product,
            $id_variable_tooltip,
            $variable_name,
            $options_data,
            $formula_name
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

        $this->context->controller->addCSS($this->_path . 'views/css/formula-conditions.css');
        $this->context->controller->addJS(($this->_path) . 'views/js/formula-conditions.js');


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
        } elseif (Tools::isSubmit('saveTrustedBadge' . $this->name)) {
            $trusted_html = Tools::getValue('trusted_badge');
            $safe_html = base64_encode($trusted_html);

            Configuration::deleteByName('productpriceconfig_trusted_badge2');
            Configuration::updateValue('productpriceconfig_trusted_badge', $safe_html, true);

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('saveUpload' . $this->name)) {
            ob_start();
            // Disable error reporting for production to avoid any warnings being sent in the CSV output
            $uploaded_file = $_FILES['upload_file'];
            $file_tmp_path = $_FILES['upload_file']['tmp_name'];

            try {
                // Load the uploaded Excel file
                $spreadsheet = IOFactory::load($file_tmp_path);
                $sheet = $spreadsheet->getActiveSheet();

                $data = $sheet->toArray(); // Convert the sheet data to an array

                // Get headers from Excel file (first row)
                $headers = $data[0];  // Headers are the first row
                $outputArray = [];

                $priceFormatter = new PriceFormatter();

                // Loop through data (start from 1 to skip headers)
                for ($i = 1; $i < count($data); $i++) {
                    $row = $data[$i];
                    $assocArray = [];

                    // Convert Excel row to associative array
                    foreach ($row as $key => $value) {
                        $assocArray[$headers[$key]] = $value;
                    }

                    // Fetching product data and calculating prices
                    $product_id = $assocArray['item_group_id'];
                    $link = $assocArray['link'];
                    $parsedLink = parse_url($link);
                    $queryString = $parsedLink['query'];
                    parse_str($queryString, $queryParams);

                    $params['id_product'] = "$product_id";
                    $params['id_product_attribute'] = "0";

                    $row =  KDProductSetting::getByProductId($product_id);
                    $params['id_product_setting'] = $row['id_product_setting'];

                    $params['baned_comb'] = "1";
                    $params['id_customization'] = "";

                    $sql = '
                    SELECT *
                    FROM ' . _DB_PREFIX_ . 'product_variable
                    WHERE id_product = ' . $product_id;

                    $available_product_variables = $this->db->executeS($sql);


                    foreach ($available_product_variables as $key => $product_variable) {
                        if (!isset($queryParams[$product_variable['formula_name']])) {
                            if ($product_variable['default_option'] != 0) {
                                $queryParams[$product_variable['formula_name']] = $product_variable['default_option'];
                            } else {
                                $queryParams[$product_variable['formula_name']] = $product_variable['minimum'];
                            }
                        }
                    }

                    foreach ($queryParams as $key => $value) {
                        // Query the database for product variables
                        $product_variable = $this->db->executeS('SELECT id_product_variable FROM ' . _DB_PREFIX_ . 'product_variable WHERE formula_name = "' . $key . '" AND id_product = ' . $product_id)[0]['id_product_variable'];
                        // $product_variable2 = $this->db->executeS('SELECT id_product_variable FROM ' . _DB_PREFIX_ . 'product_variable WHERE id_product = ' . $product_id)[0]['id_product_variable'];
                        if ($product_variable == "") {
                            $variable = $this->db->executeS('SELECT id_variable FROM ' . _DB_PREFIX_ . 'variable WHERE name LIKE "%' . $key . '%"');
                            $variable_id = $variable[0]['id_variable'];
                            $product_variable = $this->db->executeS('SELECT id_product_variable FROM ' . _DB_PREFIX_ . 'product_variable WHERE id_variable = "' . $variable_id . '" AND id_product = ' . $product_id)[0]['id_product_variable'];
                        }

                        $params_key = 'variable_' . $product_variable;

                        $params[$params_key] = $value;
                    }



                    // Calculate price using the existing methods
                    $product = new Product($params['id_product'], true, (int)$this->context->language->id);
                    $price_weight = $this->getCalculatedProductPriceWeight($params, 2);
                    $price_wot = Tools::ps_round($price_weight['price'], 2);

                    $tax = $price_wot * ($product->tax_rate / 100);
                    $tax = Tools::ps_round($tax, 2);
                    $test_param = implode(',', $params);
                    $total = $price_wot + $tax;
                    // Update the price field in the associative array
                    $assocArray['price'] = $test_param;
                    $assocArray['sale_price'] = $total;

                    // Add updated row to output array
                    $outputArray[] = $assocArray;
                }

                // Create a new Spreadsheet for output
                $outputSpreadsheet = new Spreadsheet();
                $outputSheet = $outputSpreadsheet->getActiveSheet();

                // Write headers to the new Excel sheet
                $outputSheet->fromArray($headers, NULL, 'A1');

                // Write updated rows to the new Excel sheet
                $outputSheet->fromArray($outputArray, NULL, 'A2');

                // Set headers to force download as Excel
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="updated_prices_' . time() . '.xlsx"');
                header('Cache-Control: max-age=0');

                // Write the Excel file to the output stream
                $writer = new Xlsx($outputSpreadsheet);
                $writer->save('php://output');

                exit;  // Stop further script execution
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                echo "Failed to open the Excel file. Error: " . $e->getMessage();
            }
            // Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
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
            $duplicate_product_setting->formula_shipping = $product_setting->formula_shipping;

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
            if ($variable_position) {
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
            $trusted_badge_form = $this->initTrustedBadgeForm();
            $upload_form = $this->initUploadForm();
            $helper = $this->initList();
            // return $this->_html . $helper_start_form->generateForm($this->fields_form)  . $helper->generateList(KDProductSetting::getAll((int)$this->context->language->id), $this->fields_list);
            return $this->_html
                . $helper_start_form->generateForm($this->fields_form)
                . $html_form->generateForm($this->html_form)
                . $trusted_badge_form->generateForm($this->trusted_badge_form)
                . $upload_form->generateForm($this->upload_form)
                . $helper->generateList(KDProductSetting::getAll((int)$this->context->language->id), $this->fields_list);
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

        $decoded = json_decode($tiered_price, true);
        $tier_basis_formula = '';
        $tiers = array();
        if (is_array($decoded) && isset($decoded['tiers'])) {
            $tier_basis_formula = isset($decoded['basis_formula']) ? $decoded['basis_formula'] : '';
            $tiers = $decoded['tiers'];
        } elseif (is_array($decoded)) {
            // legacy: tiered_price was a simple array of tiers
            $tiers = $decoded;
        }

        // Attempt to load printside options for this product so admin can pick an option id
        $id_product_setting = (int) Tools::getValue('id_product_setting', 0);
        $product_setting = new KDProductSetting($id_product_setting);
        

        $this->context->smarty->assign(
            array(
                'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),

                'currencies' => $currencies,
                'currentIndex' => $currentIndex,
                'currentToken' => Tools::getAdminTokenLite('AdminModules'),
                'tiered_price' => $tiers,
                'tier_basis_formula' => $tier_basis_formula,
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
    protected function initTrustedBadgeForm()
    {
        $this->trusted_badge_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Trusted Badge Snippet'),
                'icon'  => 'icon-code'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Trusted Badge HTML'),
                    'name' => 'trusted_badge',
                    'cols' => 60,
                    'rows' => 8,
                    'class' => 'rte',        // enable RTE class
                    //'autoload_rte' => true, // ensure CKEditor/autoload runs so HTML is preserved
                    'hint' => $this->l('Paste the TrustedShops / eTrusted widget snippet here.')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Continue'),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveTrustedBadge' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $content = Configuration::get('productpriceconfig_trusted_badge');
        $trusted_html = base64_decode($content);

        $helper->tpl_vars = array(
            'fields_value' => array('trusted_badge' => $trusted_html),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id
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
    protected function initUploadForm()
    {
        $this->upload_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Upload sheet file and get updated prices'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'file',
                    'label' => $this->l('Sheet file'),
                    'name' => 'upload_file',
                    'id' => 'upload_file',
                    'desc' => $this->l('Allowed file types: csv'),
                    'required' => true,
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
        $helper->submit_action = 'saveUpload' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
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

    public function getBannedCombinationJs($id_product)
    {
        $js = '';

        $context = Context::getContext();
        $current_language_id = $context->language->id;
        //$this->loadIconFontIfRequired();

        //$this->addCustomMedia();
        Media::addJsDef(array(
            'kd_ajax_path' => $this->context->link->getModuleLink($this->name, 'ajax', array('ajax' => 1)),
            'current_controller' => Tools::getValue('controller'),
            'is_17' => (int)$this->is_17,
            'adding_to_cart_text' => $this->l('Aan winkelwagen toevoegen'),
        ));


        $rules_list = KDRuleList::getAllRules($id_product);
        $js = 'function rulesForbannedComb() {';

        if (count($rules_list)) {
            $js_data = [];
            $disallowed = [];
            $reset_value_text = '';

            // Reset all elements to initial state (remove disabled class and enable disabled elements)
            $js .= '$("select[data-variable-name] option, input[data-variable-name]").each(function() {'
                . '$(this).removeClass("disabled");'
                . '$(this).removeAttr("disabled");'
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
                                $variable_text = '$("select[data-variable_id=\'' . $rule['variable'] . '\'] option:selected:first").attr("data-option_id")';
                            } else {
                                $variable_text = '$("input[data-variable_id=\'' . $rule['variable'] . '\']").val()';
                            }

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

                            $rule_text .= $variable_text . ' ' . $sign . ' ' . $rule['option'] . $and_or_sign;

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
                                $id_product_variable = Db::getInstance()->executeS(
                                    'SELECT *
                                    FROM `' . _DB_PREFIX_ . 'product_variable`
                                    WHERE `id_variable` = ' . (int) $variable . ' AND `id_product` = ' . (int) $id_product
                                );
                                $rule_text .=
                                    // '$("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val("");' . 
                                    '$("select[data-variable_id=\'' . $variable . '\'] option").each(function() {';

                                foreach ($disallow_options as $option) {
                                    $optionObj = new KDOption($option);

                                    $name = $optionObj->label[$current_language_id];
                                    $disallowed[] = $name;

                                    $rule_text .= '
                                            if ($(this).attr("data-id_option") === "' . $option . '") {'
                                        . '$(this).parents(".right-frm-section").find("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val("");'
                                        . 'if (!$(this).hasClass("disabled") && $(this).is(":selected")) {'
                                        . '$(".btn-select-value[data-variable_id=\'' . $variable . '\']").val(0);'
                                        . '$(this).parent().prop("selectedIndex", 0).val();'
                                        . '}'
                                        . '$(this).parents(".right-frm-section").find("input:hidden.btn-select-input[data-variable_id=\'' . $variable . '\']").val($(this).parent().find(":selected").attr("data-id_option"));'
                                        . '$(this).addClass("disabled");'
                                        . '$(this).attr("disabled", "disabled");'
                                        . '}';
                                }
                                $rule_text .= '});';
                                // . 'var ul = $("select[data-variable_id=\'' . $variable . '\']");'
                                // . 'var span = $(".btn-select-value[data-variable_id=\'' . $variable . '\']");'
                                // . 'ul.on("click", "option", function() {'
                                // . 'if ($(this).text() === "' . $name . '") {
                                //         $(".btn-select-value[data-variable_id=\'' . $variable . '\']").val(0);
                                //         $(this).parent().prop("selectedIndex", 0).val();
                                //     }'
                                // . '});';
                            }
                        } else {
                            $varObj = new KDVariable($disallow);
                            $variable_text = '$("span[data-variable_id=\'' . $disallow . '\']").';
                            $rule_text .= $variable_text . 'parent().addClass("disabled");'
                                . '$("#' . $varObj->name . '").prop("disabled", true);';
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
                $reset_value_text .= $variable_text . 'parent().removeClass("disabled");'
                    . '$("#' . $varObj->name . '").prop("disabled", false);';
            }

            $js .= implode(PHP_EOL, $js_data);
        }

        $js .= '}';
        $alert_message_list = KDAlertMessage::getAllAlertMessages($id_product);
        $alert_messages_combination = 'function alert_messages_combination(target){console.log(target);';
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
                    $second_variable_condition_text = '$("select[data-variable-name=\'' . $variable->name . '\'] option:selected").attr("data-option_id")';
                } else {
                    $second_variable_condition_text = '$("input[data-variable-name=\'' . $variable->name . '\']").val()';
                }
                $message_text .= $second_variable_condition_text . " == '" . $option . "'";
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

        // echo '<pre>';
        // print_r($js);
        // echo '</pre>';

        return $js;
    }



    public function hookDisplayHeader()
    {
        $css = $js = '';

        if (Tools::getValue('controller') == 'product') {

            $id_product = Tools::getValue('id_product');
            $js = $this->getBannedCombinationJs($id_product);
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

    public function hookdisplayQuoteRequest($id_product, $id_product_attribute = 0)
    {

        $id_product = $id_product['id_product'];
        $context = Context::getContext();
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

        $js = $this->getBannedCombinationJs($id_product);

        if ($js) {
            $js = '<script type="text/javascript">' . $js . '</script>';
        }

        $this->addJS('front.js');

        $available_product_variables = $this->db->executeS('
            SELECT p.*,pv.*,pvl.*, pl.name as name, p.maximum as p_maximum,p.minimum as p_minimum
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            LEFT JOIN `' . _DB_PREFIX_ . 'variable` pv ON (pv.`id_variable`= p.`id_variable`)
            LEFT JOIN `' . _DB_PREFIX_ . 'variable_lang` pvl ON (pvl.`id_variable`= p.`id_variable` AND pvl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$id_product . ' 
            ORDER BY p.`id_product_variable` ');

        $variable_position = json_decode($product_setting->variable_position, true);

        if ($variable_position) {
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
            'js' => $js,
            'html_content' => $html_content,
            'display_customize_hook' => $display_customize_hook,

        ));

        return $this->display(__FILE__, 'views/templates/hook/display_quote_request.tpl');
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
        //Configuration::updateValue('WEB2PRINT_PRINTFORMER_OPTION_ID', '394');
        $template_upload_id = Configuration::get('WEB2PRINT_PRINTFORMER_TEMPLATE_ID');
        $template_option_id = Configuration::get('WEB2PRINT_PRINTFORMER_OPTION_ID');
        $show_upload_tool = false;

        $product_setting = new KDProductSetting($row['id_product_setting']);

        $product = new Product($id_product, false, (int)$this->context->language->id);

        $available_product_variables = $this->db->executeS('
            SELECT p.*,pv.*,pvl.*, pl.name as name, p.maximum as p_maximum,p.minimum as p_minimum, p.active as active
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            LEFT JOIN `' . _DB_PREFIX_ . 'variable` pv ON (pv.`id_variable`= p.`id_variable`)
            LEFT JOIN `' . _DB_PREFIX_ . 'variable_lang` pvl ON (pvl.`id_variable`= p.`id_variable` AND pvl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$id_product . ' 
            ORDER BY p.`id_product_variable` ');

        $variable_position = json_decode($product_setting->variable_position, true);

        if ($variable_position) {
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }
        // echo '<pre>';
        // print_r($available_product_variables);
        // echo '</pre>';
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

            if ($varObj->type == 7) {
                $obj_option = new KDOption($data['default_option']);
                $data['fixed_price'] = $obj_option->price;
            }

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
                        if ($option_data['id_option'] == $template_option_id) {
                            $show_upload_tool = true;
                        }
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

        $encoded = Configuration::get('productpriceconfig_trusted_badge');
        $trusted_html = base64_decode($encoded);

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
            'show_upload_tool' => $show_upload_tool,
            'template_upload_id' => $template_upload_id,
            'template_option_id' => $template_option_id,
            'trusted_badge' => $trusted_html,
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

    public function getShipPriceFactor($value, $type)
    {

        $auto_size_data = array(
            '0.75' => array('width' => '211-1188', 'height' => '298-20000'),
            '1' => array('width' => '149-210', 'height' => '211-297'),
            '2' => array('width' => '106-148', 'height' => '149-210'),
            '4' => array('width' => '75-105', 'height' => '106-148'),
            '8' => array('width' => '53-74', 'height' => '75-105'),
            '16' => array('width' => '38-52', 'height' => '53-74'),
            '25' => array('width' => '27-37', 'height' => '38-52'),
            '64' => array('width' => '10-26', 'height' => '10-37'),
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
            '6.9 mm (3:1)'     => '0-4.5',
            '8.0 mm (3:1)'     => '4.6-6.0',
            '9.5 mm (3:1)'     => '6.1-7.5',
            '11.0 mm (3:1)'    => '7.6-9.0',
            '12.7 mm (3:1)'    => '9.1-10.5',
            '14.3 mm (2:1)'    => '10.6-12.0',
            '16.0 mm (2:1)'    => '12.1-13.5',
            '19.0 mm (2:1)'    => '13.6-16.0',
            '22.0 mm (2:1)'    => '16.1-19.0',
            '25.4 mm (2:1)'    => '19.1-22.0',
            '28.5 mm (2:1)'    => '22.1-25.0',
            '32.0 mm (2:1)'    => '25.1-28.0',
            '38.0 mm (2:1)'    => '28.1-34.0'
        );
        foreach ($auto_thickness_data as $key => $thickness) {
            $thickness_array = explode('-', $thickness);

            if (($value >= $thickness_array[0] && $value <= $thickness_array[1])) {
                return $key;
            }
        }

        return 0;
    }

    /**
     * // === OnlyPrint Special Prices — per-customer surcharge hook ===
     * Apply OnlyPrint customer-specific pricing adjustments.
     *
     * @param int   $idProduct
     * @param int   $qty
     * @param float $priceWot Price without tax
     *
     * @return float
     */
    private function getAdjustedDisplayPrice($idProduct, $qty, $priceWot)
    {
        try {
            $results = Hook::exec(
                'onlyprintAdjustProductDisplayPrice',
                array(
                    'id_product'  => (int) $idProduct,
                    'id_customer' => (isset($this->context->customer) && Validate::isLoadedObject($this->context->customer))
                        ? (int) $this->context->customer->id
                        : 0,
                    'qty'         => (int) $qty,
                    'price_wot'   => (float) $priceWot,
                ),
                null,
                true
            );

            if (is_array($results)) {
                foreach ($results as $response) {
                    if (is_numeric($response)) {
                        $priceWot = (float) $response;
                    }
                }
            }

            return Tools::ps_round((float) $priceWot, 2);

        } catch (Exception $e) {
            // Never break pricing because of a hook failure.
            return Tools::ps_round((float) $priceWot, 2);
        }
    }

 

    public function ajaxGetFilteredVariables($params)
    {
        $product = new Product($params['id_product'], true, (int)$this->context->language->id);
        $priceFormatter = new PriceFormatter();

        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $price_weight['price'];
        $price_wot = Tools::ps_round($price_wot, 2);

        $tax = $price_wot * ($product->tax_rate / 100);
        $price_wot = Tools::ps_round($price_wot, 2);


        // === OnlyPrint Special Prices — per-customer surcharge hook ===
        /* try {
            $op_results = Hook::exec('onlyprintAdjustProductDisplayPrice', array(
                'id_product'  => (int)$params['id_product'],
                'id_customer' => (isset($this->context->customer) && $this->context->customer->id) ? (int)$this->context->customer->id : 0,
                'qty'         => (int)$price_weight['qty'],
                'price_wot'   => (float)$price_wot,
            ), null, true);
            if (is_array($op_results)) {
                foreach ($op_results as $op_resp) {
                    if (is_numeric($op_resp)) {
                        $price_wot = (float)$op_resp;
                    }
                }
            }
            $price_wot = Tools::ps_round($price_wot, 2);
        } catch (Exception $e) { // fail-safe: never break pga's pricing  } */
        // === /OnlyPrint Special Prices ===

        $price_wot = $this->getAdjustedDisplayPrice(
            (int) $params['id_product'],
            (int) $price_weight['qty'],
            (float) $price_wot
        );

       // echo $price_wot;

        $tax = $price_wot * ($product->tax_rate / 100);
        $tax = Tools::ps_round($tax, 2);

        $total = $price_wot + $tax;        // print('price:'.$price_wot);
        // print('tax:'.$tax);
        // print('total:'.$total);
        $qty = $price_weight['qty'];
        $price_per_qty = $total / $qty;
        $price_per_qty = Tools::ps_round($price_per_qty, 2);

        $price_data['price_wot'] = $priceFormatter->format($price_wot);
        $price_data['tax'] = $priceFormatter->format($tax);
        $price_data['total'] = $priceFormatter->format($total);
        $price_data['price_per_qty'] = $priceFormatter->format($price_per_qty);


        $price_data['total_weight'] = $price_weight['weight'];
        $price_data['total_thickness'] = $price_weight['thickness'];

        $price_data['total_package'] = $price_weight['package'];

        die(json_encode($price_data));
    }

    public function getCalculatedProductPriceWeight($params, $customer_group_id = null)
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        require_once(dirname(__FILE__) . "/expression.php");
        $m = new expression;
        $price_data = array();
        $price_by_thickness = array();
        $formula_width = $formula_price_evaluated = $formula_height = $thickness = $weight =  $shipping_price_per_pack = $qty = 0;
        $priceFormatter = new PriceFormatter();

        $autosize_formula_string   = '#customsize#';
        $autothickness_formula_string   = '#wire#';
        $price_by_thickness_formula_string = '#wireos#';

        $width_price_text = $this->l('breedte');
        $height_price_text = $this->l('hoogte');

        $width_price_factor = $height_price_factor = $width_ship_price_factor = $height_ship_price_factor =  $value_thickness = 0;


        $product_setting = new KDProductSetting($params['id_product_setting']);
        // $product = new Product($params['id_product'], true, (int)$this->context->language->id);

        $tiered_price = json_decode($product_setting->tiered, true);

         // tiered_price may be stored as a payload: ['basis_formula' => string, 'tiers' => [...]]
        $basis_formula = '';
        $tiers = array();
        if (is_array($tiered_price) && isset($tiered_price['tiers'])) {
            $formula_tiers = isset($tiered_price['basis_formula']) ? $tiered_price['basis_formula'] : '';
            $tiers = $tiered_price['tiers'];
        } elseif (is_array($tiered_price)) {
            // legacy format: treat as tiers array
            $tiers = $tiered_price;
        }

        $formula_price = $product_setting->formula_price;
        $formula_weight = $product_setting->formula_weight;
        $formula_thickness = $product_setting->formula_thickness;
        $formula_shipping = $product_setting->formula_shipping;



        $available_product_variables = $this->db->executeS('
            SELECT p.*, pl.name 
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$params['id_product'] . '
        ');

        $sql = 'SELECT *  FROM ' . _DB_PREFIX_ . 'price_for_odd_quantities WHERE product_id = ' . (int) $params['id_product'];
        $price_for_odd_quantities = Db::getInstance()->executeS($sql);

    $current_pages = null;
    $current_printside = null;

    foreach ($available_product_variables as $data) {

            if (isset($params['variable_' . $data['id_product_variable']])) {
                $value_price = $value_shipping = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 1) {
                    $qty = $params['variable_' . $data['id_product_variable']];
                    $value_shipping = $value_weight = $value_price = $qty;
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
                    $value_shipping = $value_thickness = $option->thickness;
                    // if this variable is named 'printside' (case-insensitive), capture option id
                    if (isset($varObj->name) && strtolower($varObj->name) === 'printside') {
                        $current_printside = $id_option;
                    }
                } elseif ($varObj->type == 4) {
                    $custom_input = $params['variable_' . $data['id_product_variable']];
                    $value_shipping = $value_weight = $value_price = $value_thickness = $custom_input;

                    // if this variable is named 'pages' (case-insensitive), capture pages count
                    if (isset($varObj->name) && strtolower($varObj->name) === 'pages') {
                        $current_pages = is_numeric($custom_input) ? (int)$custom_input : $custom_input;
                    }

                    if ($data['formula_name'] == $width_price_text) {
                        // $width_price_factor = $this->getPriceFactor($custom_input, 'width');
                        $formula_width = $custom_input;
                    }

                    if ($data['formula_name'] == $height_price_text) {
                        // $height_price_factor = $this->getPriceFactor($custom_input, 'height');
                        $formula_height = $custom_input;
                    }
                } elseif ($varObj->type == 3) {
                    $value_weight = $value_price = $varObj->fixed_price;
                } elseif ($varObj->type == 6) {
                    $spine_options = $varObj->getAllOptions((int)$this->context->language->id);
                    foreach ($spine_options as $option) {
                        $option = new KDOption($option['id_option'], (int)$this->context->language->id);
                        $price_by_thickness[$option->label] = $option->price;
                    }
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    
                } elseif ($varObj->type == 7) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $shipping_price_per_pack = $option->price;
                } elseif ($varObj->type == 9) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                }

                //$name = '[variable_'.$data['id_product_variable'].']';

                $name =  '[' . $data['formula_name'] . ']';

                if ($value_price) {
                    $formula_price = str_replace($name, $value_price, $formula_price);
                    $formula_tiers = str_replace($name, $value_price, $formula_tiers);
                }

                if ($value_weight) {
                    $formula_weight = str_replace($name, $value_weight, $formula_weight);
                }

                if ($value_shipping) {
                    $formula_shipping = str_replace($name, $value_shipping, $formula_shipping);
                }

                if ($formula_thickness and $value_thickness) {
                    $formula_thickness = str_replace($name, $value_thickness, $formula_thickness);
                }
            }
        }

        if ($formula_thickness) {
            if (strpos($formula_thickness, $autothickness_formula_string) !== false) {
                $formula_thickness = str_replace($autothickness_formula_string, '', $formula_thickness);
                $thickness = $m->evaluate($formula_thickness);
                $thickness = (float)$thickness;
                $thickness = $this->getWireThicknessByRange($thickness);
            } else {

                $thickness = $m->evaluate($formula_thickness);
                $thickness = Tools::ps_round($thickness, 1);
                $thickness = $thickness . ' mm';
            }
        }

        if ($formula_width && $formula_height) {
            if ($formula_width > $formula_height) {
                $width_price_factor = $this->getPriceFactor($formula_height, 'width');
                $height_price_factor = $this->getPriceFactor($formula_width, 'height');

                $width_ship_price_factor = $this->getShipPriceFactor($formula_height, 'width');
                $height_ship_price_factor = $this->getShipPriceFactor($formula_width, 'height');
            } else {
                $width_price_factor = $this->getPriceFactor($formula_width, 'width');
                $height_price_factor = $this->getPriceFactor($formula_height, 'height');

                $width_ship_price_factor = $this->getShipPriceFactor($formula_width, 'width');
                $height_ship_price_factor = $this->getShipPriceFactor($formula_height, 'height');
            }
        }
        //echo  $formula_width.'/'.$formula_height;
        if ($width_price_factor || $height_price_factor) {
            $final_price_factor = max($width_price_factor, $height_price_factor);
            if (strpos($formula_price, $autosize_formula_string) !== false) {
                $formula_price = str_replace($autosize_formula_string, $final_price_factor, $formula_price);
            }
        }

        if ($thickness and count($price_by_thickness)) {
            $final_price_by_thickness = $price_by_thickness[$thickness];
            if(!$final_price_by_thickness){
                $final_price_by_thickness = 0;
            }
            if (strpos($formula_price, $price_by_thickness_formula_string) !== false) {
                $formula_price = str_replace($price_by_thickness_formula_string, $final_price_by_thickness, $formula_price);
            }
        }

        $formula_price = str_replace("[", "", $formula_price);
        $formula_price = str_replace("]", "", $formula_price);
        $formula_price = preg_replace('/[A-Z][a-z]+/', '0', $formula_price);
        // echo '<pre>';
        // print_r($params);
        // echo '</pre>';
        // echo 'formula '.$formula_price;
        if ($formula_price) {
            $price_wot = $m->evaluate($formula_price);
            $formula_price_evaluated = $price_wot;
        }

        //echo $price_wot;

        if ($width_ship_price_factor || $height_ship_price_factor) {
            $final_ship_price_factor = min($width_ship_price_factor, $height_ship_price_factor);
            if (strpos($formula_shipping, $autosize_formula_string) !== false) {
                $formula_shipping = str_replace($autosize_formula_string, $final_ship_price_factor, $formula_shipping);
            }
        }

        $formula_shipping = str_replace("[", "", $formula_shipping);
        $formula_shipping = str_replace("]", "", $formula_shipping);
        $formula_shipping = preg_replace('/[A-Z][a-z]+/', '0', $formula_shipping);
        //echo 'formula shipping '.$formula_shipping;
        $package = 0;
        if ($formula_shipping) {
            $shipping = $m->evaluate($formula_shipping);
            $package = ceil($shipping);
            $package = (int)$package;
            if ($package) {
                $shipping_cost = $shipping_price_per_pack * $package;
               // echo 'shipping cost '.$shipping_cost;
                $price_wot = $price_wot + $shipping_cost;
                //echo 'price with shipping '.$price_wot;
            }
        }

        $discount = 1;

        // Evaluate units using basis_formula (if present), substituting variables with submitted values
        $units = null;
        if ($formula_tiers) {
            
            if ($width_price_factor || $height_price_factor) {
                $final_tired_factor = max($width_price_factor, $height_price_factor);
                if (strpos($formula_price, $autosize_formula_string) !== false) {
                    $formula_tiers = str_replace($autosize_formula_string, $final_tired_factor, $formula_tiers);
                }
            }

            try {
                
                $units_eval = $m->evaluate($formula_tiers);
                $units = is_numeric($units_eval) ? (int)$units_eval : null;
            } catch (Exception $e) {
                $units = null;
            }
        }

        if (count($tiers)) {
            //echo 'units: '.$units;
            foreach ($tiers as $tier) {
                if (!isset($tier['from_quantity'])) continue;
                $compare = ($units !== null) ? $units : 0;
                if ($compare < $tier['from_quantity']) continue;

                // this tier applies
                $discount = $tier['price'];
                $discount = $discount / 100;
                // continue to allow later tiers to override
            }
           // echo 'discount: '.$discount;
        }


        $price_wot = $discount * $price_wot;
        
        $price_wot_dis = $price_wot;
        
        if ($customer_group_id) {
            $customerGroupId = $customer_group_id;
        } else {
            $customerGroupId = $this->context->customer->id_default_group;
        }

        $customerGroup = new Group($customerGroupId);
        $discounted_price = $price_wot * $customerGroup->reduction / 100;
        $price_wot = $price_wot - $discounted_price;


        $formula_weight = str_replace("[", "", $formula_weight);
        $formula_weight = str_replace("]", "", $formula_weight);
        $formula_weight = preg_replace('/[A-Z][a-z]+/', '0', $formula_weight);

        if ($formula_weight) {
            $weight = $m->evaluate($formula_weight);
        }

        // ── Formula Conditions surcharge ──
        $surcharge_total = $this->calculateFormulaConditionSurcharge(
            (int) $params['id_product'],
            $params,
            $price_wot,
            $qty
        );
        $price_wot += $surcharge_total;
        $price_wot_dis += $surcharge_total;


        return array('price' => $price_wot, 'price_wot_dis' => $price_wot_dis, 'formula_price' => $formula_price_evaluated, 'weight' => $weight, 'thickness' => $thickness, 'package' => $package, 'qty' => $qty);
    }

    /**
     * Calculate the total surcharge from all matching active Formula Conditions.
     *
     * For each condition, check if the customer's selected value for the
     * target variable matches the condition's configured value. If it matches,
     * apply the surcharge:
     *   - percentage: price * (amount / 100)
     *   - fixed: amount
     *   - per_unit: multiply surcharge by quantity
     *   - total: apply once
     *
     * @param int   $id_product
     * @param array $params     variable_X => value
     * @param float $price_wot  Current price without tax
     * @param int   $qty        Quantity
     * @return float Total surcharge to add
     */
    private function calculateFormulaConditionSurcharge($id_product, $params, $price_wot, $qty)
    {
        $conditions = ProductFormulaCondition::getActiveConditions($id_product);
        if (!$conditions) {
            return 0;
        }

        // Build a map of id_variable => selected value from params
        $selected_values = [];
        $available_product_variables = $this->db->executeS('
            SELECT id_product_variable, id_variable
            FROM ' . _DB_PREFIX_ . 'product_variable
            WHERE id_product = ' . (int) $id_product
        ) ?: [];

        foreach ($available_product_variables as $pv) {
            $var_key = 'variable_' . $pv['id_product_variable'];
            if (isset($params[$var_key])) {
                $selected_values[(int) $pv['id_variable']] = $params[$var_key];
            }
        }

        $total_surcharge = 0;

        foreach ($conditions as $cond) {
            $id_variable = (int) $cond['id_variable'];
            $id_option = (int) $cond['id_option'];

            if (!isset($selected_values[$id_variable])) {
                continue;
            }

            $selected = $selected_values[$id_variable];

            // Determine variable type to decide matching logic
            $varObj = new KDVariable($id_variable, (int) $this->context->language->id);

            $matches = false;

            if ($varObj->type == 2) {
                // Select type: match by option ID
                $matches = ((int) $selected === $id_option);
            } else {
                // Non-select: match by value (id_option stores the numeric/text value)
                if ($id_option > 0) {
                    $matches = ((float) $selected === (float) $id_option);
                }
            }

            if (!$matches) {
                continue;
            }

            // Calculate surcharge amount
            $amount = (float) $cond['amount'];

            if ($cond['surcharge_type'] === 'percentage') {
                $surcharge = $price_wot * ($amount / 100);
            } else {
                $surcharge = $amount;
            }

            // Apply per-unit or total
            if ($cond['apply_type'] === 'per_unit') {
                $surcharge = $surcharge * (int) $qty;
            }

            $total_surcharge += $surcharge;
        }

        return $total_surcharge;
    }

    public function ajaxAddToCart($params)
    {
        // Thin wrapper for backward-compatible AJAX: call processAddToCart and return the array.
        // The caller (HTTP front-controller) should JSON-encode and output the result when serving an AJAX request.
        $send =  $this->processAddToCart($params);

        die(json_encode($send));
        
    }

    /**
     * In-process version of ajaxAddToCart. Returns an array with keys like 'error' and 'id_customization'.
     * This allows other modules (eg. web2print) to call this method directly instead of relying on an HTTP
     * request. Keeps same behavior as previous ajaxAddToCart but without echo/die.
     */
    public function processAddToCart($params)
    {
        //ini_set('display_errors', 1);
        //ini_set('display_startup_errors', 1);
        //error_reporting(E_ALL);

        $priceFormatter = new PriceFormatter();
        if (isset($params['id_cart']) && $params['id_cart']) {
            $this->context->cookie->id_cart = (int) $params['id_cart'];
            $this->context->cart = new Cart($params['id_cart']);
            unset($params['id_cart']);
            //$this->context->cart->deleteProduct($product->id, $id_product_attribute, (int) $params['id_customization']);
        } elseif (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = (int) $this->context->cart->id;
        }

        $product_setting = new KDProductSetting($params['id_product_setting']);
        $product = new Product($params['id_product'], false, (int)$this->context->language->id);

        $id_product_attribute = $params['id_product_attribute'];
        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $price_weight['price'];

        $formula_price = $price_weight['formula_price'];
        $send = [];
        if ($formula_price == 0) {
            $send['error'] = $this->l("Bitte wählen Sie nur aus den verfügbaren Optionen");
            
            return $send;
        }

        $price_wot = Tools::ps_round($price_wot, 2);
        $price_wot = $this->getAdjustedDisplayPrice(
            (int) $params['id_product'],
            (int) $price_weight['qty'],
            (float) $price_wot
        );

        
       

        if (isset($params['id_customization']) && $params['id_customization']) {
            //$this->context->cart->deleteProduct($product->id, $id_product_attribute, (int) $params['id_customization']);
        }

        $id_customization = $this->context->cart->saveCustomization($product->id, $id_product_attribute);

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

        if ($variable_position) {
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }

        foreach ($available_product_variables as $data) {

            if (!$data['active']) {
                continue;
            }

            if (!isset($params['variable_' . $data['id_product_variable']])) {

                return [
                    'error' => $this->l('Please select all options')
                ];
            }
        }

        $send['error'] = false;
        $count = 1;
        foreach ($available_product_variables as $data) {
            // if(isset($params['variable_'.$data['id_product_variable']]) AND !empty($params['variable_'.$data['id_product_variable']])){
            if (!$data['active']) {
                    continue;
                }
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
                    if (in_array($id_option, $options)) {
                        $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                    } else {
                        $send['options'] = $options;
                        $send['id_option'] = $id_option;
                        $send['error'] = $this->l('Please select only from avalible options');
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
                $send['error'] = $this->l('Please select all options');
            }
        }
        $send['id_customization'] = $id_customization;

        return $send;
    }

    public function ajaxGetUnitPrice($params)
    {
        echo '<pre>';
        print_r($params);
        echo '</pre>';
        exit;
    }
    /**
     * Process rule conditions for a single rule entry.
     *
     * NOTE: This method only evaluates the CONDITION part of a rule (the "if" clause).
     * It does NOT check the DISALLOW actions. For full banned combination validation
     * that mirrors getBannedCombinationJs(), use KDBannedCombinationValidator::validate()
     * instead.
     *
     * Kept for backward compatibility with existing callers that only need condition checks.
     *
     * @param array $rule_data                  Array of condition objects from rule JSON
     * @param array $available_product_variables Product variables for this product
     * @param array $params                      variable_X => value format
     * @return bool  True if conditions are met (rule would fire)
     */
    public function processRuleConditions($rule_data, $available_product_variables, $params)
    {
        $result = null;
        $prev_connector = null;

        foreach ($rule_data as $condition) {
            $id_variable = (int) $condition['variable'];
            $rule_option = $condition['option'];
            $sign = (int) $condition['sign'];
            $and_or_sign = (int) $condition['and_or_sign'];

            // Find the selected value for this variable
            $selected_value = null;
            foreach ($available_product_variables as $data) {
                if ((int) $data['id_variable'] === $id_variable) {
                    $variable_key = 'variable_' . $data['id_product_variable'];
                    if (isset($params[$variable_key])) {
                        $selected_value = $params[$variable_key];
                        break;
                    }
                }
            }

            // If we can't find a value for this variable, condition cannot be met
            $condition_met = false;
            if ($selected_value !== null) {
                $condition_met = $this->evaluateCondition($selected_value, $rule_option, $sign);
            }

            // Combine with previous result using the PREVIOUS condition's and_or_sign
            if ($result === null) {
                $result = $condition_met;
            } else {
                if ($prev_connector == 2) {
                    // AND: both must be true
                    $result = $result && $condition_met;
                } elseif ($prev_connector == 1) {
                    // OR: either can be true
                    $result = $result || $condition_met;
                } else {
                    $result = $condition_met;
                }
            }

            $prev_connector = $and_or_sign;
        }

        return $result === true;
    }

    /**
     * Full banned combination validation using KDBannedCombinationValidator.
     *
     * This mirrors the exact logic of getBannedCombinationJs() but on the server side.
     * It evaluates both conditions AND disallow actions.
     *
     * @param int   $id_product
     * @param array $params variable_X => value format
     * @return array ['banned' => bool, 'reason' => string|null]
     */
    public function validateBannedCombinations($id_product, $params)
    {
        $available_product_variables = $this->db->executeS('
            SELECT p.*, pv.*, pvl.*, pl.name, p.maximum AS p_maximum, p.minimum AS p_minimum, p.active
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_variable_lang pl
                ON (pl.id_product_variable = p.id_product_variable AND pl.id_lang = ' . (int)$this->context->language->id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'variable pv
                ON (pv.id_variable = p.id_variable)
            LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang pvl
                ON (pvl.id_variable = p.id_variable AND pvl.id_lang = ' . (int)$this->context->language->id . ')
            WHERE p.id_product = ' . (int) $id_product . '
            ORDER BY p.id_product_variable
        ');

        // Convert params to id_variable => value format
        $customization_values = [];
        if ($available_product_variables) {
            foreach ($available_product_variables as $pv) {
                $var_key = 'variable_' . $pv['id_product_variable'];
                $id_variable = (int) $pv['id_variable'];
                if (isset($params[$var_key])) {
                    $customization_values[$id_variable] = $params[$var_key];
                }
            }
        }

        $validator = new KDBannedCombinationValidator();
        return $validator->validate($id_product, $customization_values);
    }

    public function get_sign_by_id($sign)
    {
        switch ($sign) {
            case 1:
                return "==";
            case 2:
                return ">";
            case 3:
                return "<";
            default:
                return false;
        }
    }

    public function get_and_or_sign_by_id($sign)
    {
        switch ($sign) {
            case 1:
                return "||";
            case 2:
                return "&&";
            default:
                return false;
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $module_name = Tools::getValue('configure');
        if ($module_name != $this->name) {
            return;
        }

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

    /**
     * Evaluate a single rule condition (sign-based comparison).
     * Used by processRuleConditions() but was previously undefined.
     *
     * @param mixed $selected_value  The value the user selected
     * @param mixed $option_value    The value from the rule condition
     * @param int   $sign            1 = ==, 2 = >, 3 = <
     * @return bool
     */
    public function evaluateCondition($selected_value, $option_value, $sign)
    {
        $selected_value = (float) $selected_value;
        $option_value = (float) $option_value;

        switch ((int) $sign) {
            case 1:
                return $selected_value == $option_value;
            case 2:
                return $selected_value > $option_value;
            case 3:
                return $selected_value < $option_value;
            default:
                return false;
        }
    }

    /**
     * Hook: displayMyAccountOrder
     * Adds a "Reconfigure & Reorder" link to the order detail page
     * for orders that contain ProductPriceConfig products.
     */
    public function hookDisplayMyAccountOrder($params)
    {
        return $this->renderReorderLink($params);
    }

    /**
     * Hook: displayOrderDetail
     * Same as displayMyAccountOrder - adds reorder link on order detail page.
     */
    public function hookDisplayOrderDetail($params)
    {
        return $this->renderReorderLink($params);
    }

    /**
     * Render the reorder link button (shared by both hooks).
     */
    private function renderReorderLink($params)
    {
        $id_order = 0;
        if (isset($params['order']) && is_object($params['order'])) {
            $id_order = (int) $params['order']->id;
        } elseif (isset($params['order']) && is_array($params['order'])) {
            $id_order = (int) $params['order']['id'];
        }

        if (!$id_order) {
            return '';
        }

        // Quick check if order has PPC products
        $service = new KDReconfigureReorder($this);
        if (!$service->orderHasPPCProducts($id_order)) {
            return '';
        }

        $reconfigure_url = $this->context->link->getModuleLink(
            $this->name,
            'reconfigurereorder',
            ['id_order' => $id_order],
            true
        );

        $this->context->smarty->assign(array(
            'reconfigure_url' => $reconfigure_url,
            'id_order' => $id_order,
        ));

        return $this->display(__FILE__, 'views/templates/hook/reorder_link.tpl');
    }

    /**
     * Hook: displayCustomerAccount
     * Adds a "My Offers" link on the customer account page.
     */
    public function hookDisplayCustomerAccount($params)
    {
        $offers_url = $this->context->link->getModuleLink($this->name, 'offerlist', [], true);
        $this->context->smarty->assign('offers_url', $offers_url);
        return $this->display(__FILE__, 'views/templates/hook/my_offers_link.tpl');
    }
}
