<?php

/**
 * 2017-2024 Krupaludev
 *
 * QuoteRequest
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

//require_once(dirname(__FILE__) . '/libraries/krupaludev/Helpers.php');
require_once(dirname(__FILE__) . '/libraries/KDQuote.php');
require_once(dirname(__FILE__) . '/libraries/KDQuoteProduct.php');


/**
 * Class QuoteRequest
 */
class QuoteRequest extends Module
{
    private $_html = '';
    public $fields_form;
    public $html_form;
    public $fields_value;

    public $status = [];
    public function __construct()
    {
        $this->name = 'quoterequest';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Vipul Jain';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Quote Request');
        $this->description = $this->l('Advance Product Quote Request Managment');
        $this->confirmUninstall = $this->l('Uninstalling the module will delete all data?');

        $this->definePublicVariables();

        $this->status =  [
            0 => $this->l('Waiting for price'),
            1 =>  $this->l('Quotation'),
            2 =>  $this->l('Cancelled by admin'),
            3 => $this->l('Rejected by user'),
            4 => $this->l('Ordered'),
        ];
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
            && $this->registerHook('header')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionAfterDeleteProductInCart')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayMyAccountBlock')
        ) {
            $sql1 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "quote_request` (
                `id_quote`   INT(10)  UNSIGNED    NOT NULL AUTO_INCREMENT,
                `id_customer` int(10) unsigned NOT NULL,
                `id_order` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `id_cart` int(10) unsigned NOT NULL,
                `id_currency` int(10) unsigned NOT NULL,
                `title` varchar(500) NOT NULL,
                `email` varchar(500) NOT NULL,
                `allow_partial` int(1) unsigned NOT NULL DEFAULT 0,
                `current_status` int(1) unsigned NOT NULL DEFAULT 0,
                `note` text NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_quote`)
              )  ENGINE = " . _MYSQL_ENGINE_ . " DEFAULT CHARSET = utf8";

            $sql2 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "quote_product` (
              `id_quote_product`   BIGINT(20)  UNSIGNED    NOT NULL AUTO_INCREMENT,
			  `id_quote` INT(10)     UNSIGNED    NOT NULL,
              `id_product` int(10) unsigned NOT NULL,
              `id_product_attribute` int(10) unsigned NOT NULL,
              `quantity` int(10) unsigned NOT NULL,
              `quantity2` int(10) unsigned NOT NULL,
              `quantity3` int(10) unsigned NOT NULL,
              
              `price` decimal(20,4) NOT NULL DEFAULT '0.00',
              `price2` decimal(20,4) NOT NULL DEFAULT '0.00',
              `price3` decimal(20,4) NOT NULL DEFAULT '0.00',

              `production` int(10) unsigned NOT NULL,
              `production2` int(10) unsigned NOT NULL,
              `production3` int(10) unsigned NOT NULL,

              `approved_quantity` int(1) unsigned NOT NULL DEFAULT '0',
              `weight` decimal(20,2) NOT NULL DEFAULT '0.00',
              `details` text NOT NULL,
              `file` text NOT NULL,
              `artwork` varchar(500) NOT NULL,
              `status` int(1) unsigned NOT NULL DEFAULT '0',

              PRIMARY KEY (`id_quote_product`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";



            Db::getInstance()->execute($sql2);
            Db::getInstance()->execute($sql1);

            $id_tab = Tab::getIdFromClassName('AdminCatalog');
            $this->installModuleTab('AdminQuoteRequestHome', array((int)$this->context->language->id => 'Manage Quote'), 0);
            $id_tab = Tab::getIdFromClassName('AdminQuoteRequestHome');

            $this->installModuleTab('AdminQuoteRequest', array((int)$this->context->language->id => 'Quote Requests'), $id_tab);
            //$this->installModuleTab('AdminAlertMessages', array((int)$this->context->language->id => 'Alert Messages'), $id_tab);


            return true;
        }
        return false;
    }

    public function uninstall()
    {
        $id_tab = Tab::getIdFromClassName('AdminQuoteRequestHome');

        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'quote_request`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'quote_product`');

        if (parent::uninstall() && $this->uninstallModuleTab('AdminQuoteRequest', $id_tab) && $this->uninstallModuleTab('AdminQuoteRequestHome', 0)) {
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
            case 'CallPriceForm':
                $id_quote_product = Tools::getValue('id_quote_product');
                $ret = $this->CallPriceForm($id_quote_product);
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
            case 'saveQuoteSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->saveQuoteSettings();

                break;
            case 'SavePercentageSettings':
                $ret['saved'] = true;
                $ret['saved'] &=  $this->savePercentageSettings();

                break;
            case 'SavePrice':
            case 'DeleteQuoteProduct':
            case 'SaveQuoteProductPosition':
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
            case 'ShowAvailableOrderOption':
                $available_order_option = $this->getAvailableOrderOption();
                $this->context->smarty->assign(array('available_order_option' => $available_order_option));
                $html = $this->display(__FILE__, 'views/templates/admin/available_order_option.tpl');
                $ret['content'] = utf8_encode($html);
                $ret['title'] = utf8_encode($this->l('Available order option'));
                break;
            case 'ConvertToOrder':
                $keys = explode(',', Tools::getValue('keys'));
                $id_quote = Tools::getValue('id_quote');

                $html = '';
                $this->convertToOrder($id_quote);

                $ret['html'] = utf8_encode('Please wait...');
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
            case 'add_product_to_quote_list':
                $this->add_product_to_quote_list();
                break;
            case 'createOrderFromQuote':
                $id_quote = (int)Tools::getValue('id_quote');
                $id_address = (int)Tools::getValue('id_address');
                try {
                    $order_id = $this->createOrderFromQuote($id_quote, $id_address);
                    $ret = [
                        'success' => true,
                        'order_id' => $order_id
                    ];
                } catch (Exception $e) {
                    $ret = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function add_product_to_quote_list()
    {
        $id_product = Tools::getValue('id_product');
        $id_quote = Tools::getValue('id_quote');
        $product_configuration = Tools::getValue('product_configuration');
        $product_files = $_FILES['product_files'];
        $product_configuration = json_decode($product_configuration, true);

        $product_additional_information = Tools::getValue('product_additional_information');
        $product_configuration[] = ["additional_information" => $product_additional_information];

        $target_dir = dirname(__FILE__) . "/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_names = array();
        foreach ($product_files['name'] as $key => $name) {
            $tmp_name = $product_files['tmp_name'][$key];
            $target_file = $target_dir . basename($name);
            if (move_uploaded_file($tmp_name, $target_file)) {
                $file_names[] = basename($name);
            }
        }

        $quote_product = new KDQuoteProduct();
        $quote_product->id_quote = $id_quote;
        $quote_product->id_product = $id_product;
        $quote_product->id_product_attribute = 0;
        $quote_product->status = 0;
        $quote_product->details = json_encode($product_configuration);
        $quote_product->file = implode(',', $file_names);
        $quote_product->artwork = null;
        $quote_product->quantity = 1;
        $quote_product->production = 0;
        $quote_product->price = 0;
        $quote_product->weight = 0;
        $quote_product->approved_quantity = null;

        $quote_product->save();

        echo json_encode(['status' => true]);
        exit;
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

    public function saveTieredPrice()
    {

        $values = array();
        $qty = array();
        $price = array();
        $id_product = Tools::getValue('id_product');
        $row = KDQuote::getByProductId($id_product);

        $quote = new KDQuote($row['id_quote']);

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

        $quote->id_product = $id_product;

        $quote->tiered = json_encode($values);
        $quote->save();
        return $quote->id;
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

    public function saveQuoteSettings()
    {
        $id_quote = Tools::getValue('id_quote');

        $quote = new KDQuote($id_quote);

        $quote->title = Tools::getValue('title');
        $quote->email = Tools::getValue('email');
        $quote->allow_partial = Tools::getValue('allow_partial');
        $quote->current_status = Tools::getValue('current_status');
        $quote->note = Tools::getValue('note');

        $quote->save();
        return $quote->id;
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

    public function CallPriceForm($id_quote_product, $full = true)
    {
        $priceFormatter = new PriceFormatter();

        $id_lang = Context::getContext()->language->id;

        if (!$id_quote_product) {
            return false;
        }
        $quote_data_multishop = $this->db->executeS('
            SELECT p.*
            FROM ' . _DB_PREFIX_ . 'quote_product p
            WHERE p.id_quote_product = ' . (int)$id_quote_product . '
        ');
        $quote_data = [];
        foreach ($quote_data_multishop as $data) {

            if (!$quote_data) {
                $quote_data = $data;
                $quote_data['price_formted'] = $priceFormatter->format($quote_data['price']);
                $customizations_arr = [];

                $quote_customizations = json_decode($data['details']);

                foreach ($quote_customizations as $customization) {
                    $customizations_arr[] = $customization;
                }

                if (!empty($customizations_arr)) {
                    $quote_data['customizations'] = $customizations_arr;
                }

                $product = new Product($data['id_product'], false, (int)$this->context->language->id);
                $quote_data['product_name'] = $product->name;
                $cover = Image::getCover($product->id);
                if ($cover) {
                    $image_id = $cover['id_image'];
                    $quote_data['product_image'] =  $this->context->link->getImageLink($product->link_rewrite, $image_id, 'home_default');
                }

                $files_names = explode(',', $quote_data['file']);

                $files = [];
                $baseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__; // Base URL from context
                $modulePath = _PS_MODULE_DIR_ . $this->name . '/uploads/'; // Absolute path for file existence check

                foreach ($files_names as $key => $file_name) {
                    if (!empty($file_name) && file_exists($modulePath . $file_name)) {
                        $files[] = $baseUrl . 'modules/' . $this->name . '/uploads/' . $file_name;
                    }
                }

                $quote_data['file'] = $files;
                // $quote_data['minimum'] = $varObj->minimum;
                // $quote_data['maximum'] = $varObj->maximum;
            }
        }
        $this->context->smarty->assign(array(
            't' => $quote_data,
            'is_17' => $this->is_17,
        ));
        if ($full && $quote_data_multishop) {
        }
        $this->assignLanguageVariables();
        $ret = array(
            'form_html' => utf8_encode($this->display(__FILE__, 'views/templates/admin/product-form.tpl')),
            'id_quote_product' => $id_quote_product,
        );
        return $ret;
    }

    public function ajaxSavePrice()
    {
        $priceFormatter = new PriceFormatter();
        $id_quote_product = Tools::getValue('id_quote_product');
        $id_quote = Tools::getValue('id_quote');
        $price = Tools::getValue('price');
        $production = Tools::getValue('production');
        $weight = Tools::getValue('weight');


        if (!$price) {
            $errors['no_name'] = $this->l('Please add a price');
        }
        if ($errors) {
            $this->throwError($errors);
        }
        if (!$this->savePrice(
            $id_quote_product,
            $id_quote,
            $price,
            $production,
            $weight
        )) {
            $this->throwError($this->l('Price not saved'));
        }
        $quote = new KDQuote($id_quote);
        $status = $quote->current_status;
        $status_text = $this->status[$status];
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
            'price' => $priceFormatter->format($price),
            'weight' => ' : ' . $weight . ' kg',
            'production' => ' : ' . $production . ' day',
            'status' => $status_text
        );
        die(Tools::jsonEncode($ret));
    }

    public function ajaxSaveQuoteProductPosition()
    {
        $position_data = Tools::getValue('positions');
        $id_quote = Tools::getValue('id_quote');

        if (!$position_data) {
            $errors['no_options'] = $this->l('Action failed.');
        }
        if ($errors) {
            $this->throwError($errors);
        }

        $quote = new KDQuote($id_quote);

        $quote->product_position = Tools::jsonEncode($position_data);

        if (!$quote->update()) {
            $this->throwError($this->l('Product order not saved'));
        }
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
        );
        die(Tools::jsonEncode($ret));
    }

    public function ajaxDeleteQuoteProduct()
    {
        $id_quote_product = Tools::getValue('id_quote_product');
        $result = array(
            'success' => $this->deleteQuoteProduct($id_quote_product),
        );
        exit(Tools::jsonEncode($result));
    }

    public function deleteQuoteProduct($id_quote_product)
    {
        $quote_product = new KDQuoteProduct($id_quote_product);

        return $quote_product->delete();
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
        $id_quote_product = '';
        $variable = new KDVariable($id_variable);
        $variable_name = $variable->name;
        $options_data = array();
        $id_variable_tooltip = '';
        $formula_name = '';
        if (!$this->saveQuoteProduct(
            $id_quote_product,
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

    public function convertToOrder($id_quote)
    {
        $quote = new KDQuote($id_quote);
        if (!$this->createOrder(
            $quote,
        )) {
            $this->throwError($this->l('Variable not saved'));
        }
        $ret = array(
            'hasError' => false,
            'responseText' => $this->saved_txt,
        );

        return $ret;
    }

    public function createOrder($quote)
    {

        if (($id_cart = Tools::getValue('id_cart')) &&
            ($module_name = Tools::getValue('payment_module_name')) &&
            ($id_order_state = Tools::getValue('id_order_state')) && Validate::isModuleName($module_name)
        ) {
            if ($quote) {
                if (!Configuration::get('PS_CATALOG_MODE')) {
                    $payment_module = Module::getInstanceByName($module_name);
                } else {
                    $payment_module = new BoOrder();
                }

                $cart = new Cart((int)$id_cart);
                Context::getContext()->currency = new Currency((int)$cart->id_currency);
                Context::getContext()->customer = new Customer((int)$cart->id_customer);

                $bad_delivery = false;
                if (($bad_delivery = (bool)!Address::isCountryActiveById((int)$cart->id_address_delivery))
                    || !Address::isCountryActiveById((int)$cart->id_address_invoice)
                ) {
                    if ($bad_delivery) {
                        $this->errors[] = Tools::displayError('This delivery address country is not active.');
                    } else {
                        $this->errors[] = Tools::displayError('This invoice address country is not active.');
                    }
                } else {
                    $employee = new Employee((int)Context::getContext()->cookie->id_employee);
                    $payment_module->validateOrder(
                        (int)$cart->id,
                        (int)$id_order_state,
                        $cart->getOrderTotal(true, Cart::BOTH),
                        $payment_module->displayName,
                        $this->l('Manual order -- Employee:') . ' ' .
                            substr($employee->firstname, 0, 1) . '. ' . $employee->lastname,
                        array(),
                        null,
                        false,
                        $cart->secure_key
                    );
                    if ($payment_module->currentOrder) {
                        $token = Tools::getAdminTokenLite('AdminOrders');
                        Tools::redirectAdmin(self::$currentIndex . '&id_order=' . $payment_module->currentOrder . '&vieworder' . '&token=' . $token);
                    }
                }
            } else {
                $errors[] = Tools::displayError('You do not have permission to add this.');
            }
        }
    }

    function createOrderFromQuote($id_quote, $id_address = null)
    {
        // Load quote
        $quote = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "quote_request WHERE id_quote = " . (int)$id_quote);
        if (!$quote) {
            throw new Exception("Quote not found.");
        }

        // Load quote products
        $products = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "quote_product WHERE id_quote = " . (int)$id_quote);
        if (empty($products)) {
            throw new Exception("No products found in quote.");
        }

        // Load customer
        $customer = new Customer((int)$quote['id_customer']);
        if (!Validate::isLoadedObject($customer)) {
            throw new Exception("Customer not found.");
        }

        // Load or create cart
        $cart = new Cart((int)$quote['id_cart']);
        
        if (!Validate::isLoadedObject($cart)) {
            $cart = new Cart();
            $cart->id_customer = (int)$quote['id_customer'];
            $cart->id_currency = (int)$quote['id_currency'];
            $cart->id_lang = (int)$quote['id_lang'];

            // Set addresses
            if ($id_address) {
                $cart->id_address_delivery = (int)$id_address;
                $cart->id_address_invoice = (int)$id_address;
            } else {
                $default_address = (int)Address::getFirstCustomerAddressId($customer->id);
                if (!$default_address) {
                    throw new Exception("Customer does not have any address.");
                }
                $cart->id_address_delivery = $default_address;
                $cart->id_address_invoice = $default_address;
            }

            // Set default carrier (assumes at least one carrier is active)
            $carriers = Carrier::getCarriers((int)$cart->id_lang, true, false, false, null, Carrier::ALL_CARRIERS);
            if (empty($carriers)) {
                throw new Exception("No active carrier found.");
            }
            $cart->id_carrier = $carriers[0]['id_carrier'];

            $cart->save();

            $sql = "UPDATE " . _DB_PREFIX_ . "quote_request SET id_cart = " . (int)$cart->id . " WHERE id_quote = " . (int)$id_quote;
            Db::getInstance()->execute($sql);
        } else {
            // Update addresses if $id_address is provided
            if ($id_address) {
                $cart->id_address_delivery = (int)$id_address;
                $cart->id_address_invoice = (int)$id_address;
                $cart->update();
            }
        }
        
        // Add products to cart with customization
        foreach ($products as $product) {
            // if ((int)$product['approved_quantity'] > 0) {
                $customizations = json_decode($product['details'], true);
                // Add customization if available
                $id_customization = 0;
                if (!empty($customizations)) {

                    $id_customization = $cart->saveCustomization($product['id_product'], 0);

                    foreach ($customizations as $data) {
                        $var_type = Db::getInstance()->getValue("SELECT `type` FROM " . _DB_PREFIX_ . "variable WHERE id_variable = " . (int)$data['variable_id']);
        
                        if ($var_type == 2 || $var_type == 4 || $var_type == 5 ||  $var_type == 3 || $var_type == 6) {
                            $cart->addCustomizationData($id_customization, $data['product_variable_id'], Product::CUSTOMIZE_TEXTFIELD, $data['valueText'], $product['price']);

                        } elseif ($var_type == 1) {
                            $cart->addCustomizationData($id_customization, $data['product_variable_id'], Product::CUSTOMIZE_TEXTFIELD, $data['valueText'], $product['price'], $product['weight']);

                        }
                    }
                    
                }
                $quantity = (int)$product['approved_quantity'] > 0 ? (int)$product['approved_quantity'] : (int)$product['quantity'];
                $cart->updateQty(
                    $quantity,
                    (int)$product['id_product'],
                    (int)$product['id_product_attribute'],
                    $id_customization ?: false,
                    'set',
                    0,
                    null,
                    false
                );
            // }
        }
        // Debug: Check cart validity
        if (!Validate::isLoadedObject($cart)) {
            throw new Exception("Cart is not valid after creation.");
        }
        // Debug: Check cart products
        $cart_products = $cart->getProducts();
        if (empty($cart_products)) {
            throw new Exception("Cart has no products. Cart ID: " . $cart->id);
        }
        // Debug: Check address validity
        $address = new Address($cart->id_address_delivery);
        if (!Validate::isLoadedObject($address) || $address->deleted) {
            throw new Exception("Delivery address is invalid, deleted, or inactive. Address ID: " . $cart->id_address_delivery);
        }
        // Debug: Check payment module
        $payment_module = Module::getInstanceByName('ps_wirepayment');
        if (!Validate::isLoadedObject($payment_module)) {
            throw new Exception("Payment module 'ps_wirepayment' not found or not valid.");
        }
        // Debug: Check currency
        $currency = new Currency($cart->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            throw new Exception("Currency is not valid. Currency ID: " . $cart->id_currency);
        }
        // Debug: Check customer secure key
        if (empty($customer->secure_key)) {
            throw new Exception("Customer secure key is missing.");
        }

        // Validate order
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        
        try {
            $payment_module->validateOrder(
                (int)$cart->id,
                (int)Configuration::get('PS_OS_PREPARATION'),
                $total,
                'Quotation Conversion',
                null,
                [],
                (int)$cart->id_currency,
                false,
                $cart->secure_key
            );
        } catch (Exception $e) {
            throw new Exception("Order Payment Exception: " . $e->getMessage() . " " . $e->getTraceAsString() . " " . $e->getFile() . " " . $e->getLine() . " | Cart ID: $cart->id | Customer ID: $customer->id | Address ID: $cart->id_address_delivery | Total: $total | Products in cart: " . count($cart_products));
        }

        $order = new Order(Order::getOrderByCartId($cart->id));

        // Link order to quote
        Db::getInstance()->update('quote_request', [
            'id_order' => (int)$order->id,
            'current_status' => 1,
            'date_upd' => date('Y-m-d H:i:s')
        ], 'id_quote = ' . (int)$id_quote);

        return $order->id;
    }

    public function savePrice(
        $id_quote_product,
        $id_quote,
        $price = 0.00,
        $production = 0,
        $weight = 0
    ) {

        if (!$id_quote_product) {
            $quote_product = new KDQuoteProduct();
        } else {
            $quote_product = new KDQuoteProduct($id_quote_product);
        }

        $quote_product->status = 1;
        $quote_product->id_quote = $id_quote;
        $quote_product->price = $price;
        $quote_product->production = $production;
        $quote_product->weight = $weight;

        $quote_product->save();

        $quote = new KDQuote($id_quote);
        $quote->current_status = 1;
        $quote->save();

        $context = Context::getContext();
        $customer = $context->customer;
        
        $toName = $customer->firstname . ' ' . $customer->lastname;
        $from = Configuration::get('PS_SHOP_EMAIL');
        $fromName = Configuration::get('PS_SHOP_NAME');
        $subject = 'Your Quote Request Has Been Updated';
        $template = 'quote_price_update';

        $priceFormatter = new PriceFormatter();
        $price = $priceFormatter->format($price);

        $templateVars = array(
            '{customer_name}' => $toName,
            '{quote_id}' => $quote->id,
            '{date_add}' => date('Y-m-d H:i:s'),
            '{quote_title}' => $quote->title,
            '{shop_name}' => $fromName,
            '{quote_price}' => $price,
            '{quote_production_days}' => $production,
            '{quote_weight}' => $weight,
        );

        Mail::Send(
            (int)$context->language->id,
            $template,
            $subject,
            $templateVars,
            $quote->email,
            $toName,
            $from,
            $fromName,
            null,
            null,
            dirname( __FILE__ ,1) . '/mails/'  
        );

        return $quote_product->id;
    }

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
        $priceFormatter = new PriceFormatter();

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

        $id_quote = (int)Tools::getValue('id_quote');
        $id_product = (int)Tools::getValue('id_product');

        $id_shop_current = $this->context->shop->id;

        if (Tools::isSubmit('delete' . $this->name) && ($id_quote)) {
            if ($id_quote) {
                $quote = new KDQuote($id_quote);
                if (Validate::isLoadedObject($quote) && $quote->delete())
                    Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
            }

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('quote' . $this->name) && ($id_quote = Tools::getValue('id_quote'))) {
            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&id_quote=' . $id_quote . '&view' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('saveHtml' . $this->name)) {
            $content = Tools::getValue('HTML_QUOTE');

            Configuration::updateValue('HTML_QUOTE', $content, true);

            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('view' . $this->name) && $id_quote) {

            if (isset($id_quote) and $id_quote) {
                $quote = new KDQuote($id_quote);
            }

            if ($quote) {
                $quote->customer = new Customer($quote->id_customer);

                // $adminToken = Tools::getAdminTokenLite('AdminCustomers');
                // $customerViewUrl = Context::getContext()->link->getBaseLink() . basename(_PS_ADMIN_DIR_) . 
                //     '/index.php/sell/customers/' . $quote->id_customer . '/view?_token=' . $adminToken;

                $adminToken = Tools::getAdminTokenLite('AdminCustomers');

                // Get the base admin URL
                $baseAdminUrl = Context::getContext()->link->getAdminLink('AdminCustomers', false);

                // Construct the customer view URL
                $customerViewUrl = $baseAdminUrl . '&id_customer=' . (int)$quote->id_customer . '&_token=' . $adminToken;

                $quote->customer->profile_link = $customerViewUrl;
            }


            if (Validate::isLoadedObject($quote)) {
                $quote_html = '<div class=" panel">' . $this->l('Update for') . ' <a href="#">' . $quote->id . '-' . $quote->title . '[' . $quote->date_add . ']' . '</a>
                </div>';
            } else {
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
            }

            $back = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

            $quote_title = $quote->title;

            $available_quote_products = $this->db->executeS('
                SELECT p.*
                FROM ' . _DB_PREFIX_ . 'quote_product p
                WHERE p.id_quote = ' . (int)$id_quote . '
            ');

            foreach ($available_quote_products as &$data) {
                $customizations_arr = [];
                $data['price_formted'] = $priceFormatter->format($data['price']);
                $quote_customizations = json_decode($data['details']);

                foreach ($quote_customizations as $customization) {
                    $customizations_arr[] = $customization;
                }

                if (!empty($customizations_arr)) {
                    $data['customizations'] = $customizations_arr;
                }

                $product = new Product($data['id_product'], false, (int)$this->context->language->id);
                $data['product_name'] = $product->name;
                $cover = Image::getCover($product->id);
                if ($cover) {
                    $image_id = $cover['id_image'];
                    $data['product_image'] =  $this->context->link->getImageLink($product->link_rewrite, $image_id, 'home_default');
                }

                $files_names = explode(',', $data['file']);

                $files = [];
                $baseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__; // Base URL from context
                $modulePath = _PS_MODULE_DIR_ . $this->name . '/uploads/'; // Absolute path for file existence check

                foreach ($files_names as $key => $file_name) {
                    if (!empty($file_name) && file_exists($modulePath . $file_name)) {
                        $files[] = $baseUrl . 'modules/' . $this->name . '/uploads/' . $file_name;
                    }
                }

                $data['file'] = $files;
            }
            //$available_quote_products = KDQuoteProduct::getAll($id_product);

            $currency = $this->context->currency;
            $default_class = 'highlighted';

            $productsByCategory = [];

            // Get all active categories
            $categories = Category::getCategories((int)Context::getContext()->language->id, true, false);

            // Iterate over each category
            foreach ($categories as $category) {
                $categoryObj = new Category($category['id_category'], (int)Context::getContext()->language->id);

                // Get active products in this category
                $products = $categoryObj->getProducts(
                    (int)Context::getContext()->language->id,
                    0,
                    PHP_INT_MAX,
                    'id_product',
                    'ASC'
                );

                // Store the products grouped by category
                if (!empty($products)) {
                    foreach ($products as $key => $product) {
                        $productsByCategory[$categoryObj->name][] = [
                            'id_product' => $product['id_product'],
                            'product_name' => $product['name'],
                        ];
                    }
                }
            }

            $ajax_link = $this->context->link->getModuleLink('quoterequest', 'frontquoterequest');


            $addresses = [];
            if ($quote->id_customer != 0) {
                $customer = new Customer($quote->id_customer);
                $addresses = $customer->getAddresses((int)Context::getContext()->language->id);
            }

            $controller = 'AdminAddresses';
            $token = Tools::getAdminTokenLite($controller);

            // Construct the URL for adding a new address
            $domain = Tools::getShopDomainSsl();
            $add_new_address_page_link = $domain . '/backend/index.php?controller=' . $controller . '&addaddress=1&token=' . $token;


            $this->context->smarty->assign(array(
                'quote_products' => $available_quote_products,
                'quote_title' => $quote_title,
                'id_quote' => $id_quote,
                'link' => $this->context->link,
                'quote' => $quote,
                'back' => $back,
                'quote_html' => $quote_html,
                'this' => $this,
                'status' => $this->status,
                'productsByCategory' => $productsByCategory,
                'ajax_link' => $ajax_link,
                'customer' => $customer,
                'customer_addresses' => $addresses,
                'add_new_address_page_link' => $add_new_address_page_link
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

            $quotes = KDQuote::getAll(true);
            foreach ($quotes as &$quote) {
                $quote['current_status'] = $this->status[$quote['current_status']];
            }
            //$helper_start_form = $this->initStartForm();
            $html_form = $this->initHtmlForm();
            $helper = $this->initList();
            // return $this->_html . $helper_start_form->generateForm($this->fields_form)  . $helper->generateList(KDQuote::getAll((int)$this->context->language->id), $this->fields_list);
            return $this->_html . $html_form->generateForm($this->html_form) . $helper->generateList($quotes, $this->fields_list);
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
            'id_quote' => array(
                'title' => $this->l('Id'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'width' => 300,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),

            'current_status' => array(
                'title' => $this->l('Status'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),

            'date_add' => array(
                'title' => $this->l('Created'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_quote';
        $helper->actions = array('view', 'delete');
        $helper->show_toolbar = true;
        $helper->imageType = 'jpg';

        $helper->title = $this->l('Quotes');
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
                    'name' => 'id_quote',
                    'value' => Tools::getValue('id_quote'),
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
            'fields_value' => array('products' => '', 'id_product' => '', 'id_quote' => Tools::getValue('id_quote')),
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
                    'name' => 'HTML_QUOTE',
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

        $content = Configuration::get('HTML_QUOTE');

        $helper->tpl_vars = array(
            'fields_value' => array('HTML_QUOTE' => $content),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper;
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
        $price_data['total_thickness'] = $price_weight['thickness'] . ' mm';


        die(json_encode($price_data));
    }


    public function ajaxAddToCart($params)
    {
        //ini_set('display_errors', 1);
        //ini_set('display_startup_errors', 1);
        //error_reporting(E_ALL);


        $priceFormatter = new PriceFormatter();


        $quote = new KDQuote($params['id_quote']);
        $product = new Product($params['id_product'], false, (int)$this->context->language->id);

        $id_product_attribute = $params['id_product_attribute'];

        if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = (int) $this->context->cart->id;
        }

        $quote->id_cart = $this->context->cart->id;
        $quote->save();


        $id_customization = $this->context->cart->saveCustomization($product->id, $id_product_attribute);
        // $id_customization = $params['id_customization'];
        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $price_weight['price'];

        // $tax = $price_wot * ($product->tax_rate / 100);
        // $total = $price_wot + $tax;

        // $total_weight = 1.2;
        $total_weight = $price_weight['weight'];
        $total_thickness = $price_weight['thickness'];


        $available_quote_products = $this->db->executeS('
            SELECT p.*
            FROM ' . _DB_PREFIX_ . 'quote_product p
            WHERE p.id_product = ' . (int)$params['id_product'] . '
            ORDER BY p.`id_quote_product`
        ');
        $variable_position = json_decode($quote->variable_position, true);

        if ($variable_position) {
            usort($available_quote_products, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_quote_product'], $variable_position);
                $pos_b = array_search($b['id_quote_product'], $variable_position);
                return $pos_a - $pos_b;
            });
        }
        $send['error'] = false;
        $count = 1;
        foreach ($available_quote_products as $data) {
            // if(isset($params['variable_'.$data['id_quote_product']]) AND !empty($params['variable_'.$data['id_quote_product']])){
            if (isset($params['variable_' . $data['id_quote_product']])) {
                $count++;
                $value_price = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 2) {
                    $id_option = $params['variable_' . $data['id_quote_product']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    $value_weight = $option->weight;
                    $value = $option->label;

                    $options = $this->db->getValue('
                        SELECT p.options
                        FROM ' . _DB_PREFIX_ . 'quote_product p
                        WHERE p.id_quote_product = ' . (int)$data['id_quote_product'] . '
                    ');

                    $options = json_decode($options, true);
                    if (in_array($id_option, $options)) {
                        $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value);
                    } else {
                        $send['error'] = "Please select only from avalible options";
                    }
                } elseif ($varObj->type == 1) {
                    $value = $params['variable_' . $data['id_quote_product']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value, $price_wot, $total_weight);
                } elseif ($varObj->type == 3) {
                    $value = $varObj->fixed_price;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 4) {
                    $value = $params['variable_' . $data['id_quote_product']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 5) { // type 5 is for custom text input
                    $value = $params['variable_' . $data['id_quote_product']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 6) { // type 6 is for thickness text input
                    $value = $total_thickness;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_quote_product'], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } else {
                $send['error'] = "Please select all options";
            }
        }
        $send['id_customization'] = $id_customization;
        die(json_encode($send));
    }

    public function hookDisplayCustomerAccount($params)
    {
        $link = $this->context->link->getModuleLink($this->name, 'frontquotelist', array(), true);
        $this->context->smarty->assign(array(
            'link' => $link,
        ));
        return $this->display(__FILE__, 'views/templates/hook/my_account_link.tpl');
    }

    public function hookActionValidateOrder($params)
    {

        if (!Validate::isLoadedObject($params['order'])) {
            return '';
        }
        $order = $params['order'];
        // execute if possible
        try {
            $draftIds = [];
            $processingId = 0;
            if (isset($order->id_cart) && (int)$order->id_cart > 0) {
                $select_quote_to_move = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'quote_request` WHERE `id_cart` = ' . (int)$order->id_cart . ' AND current_status = "1"');

                if (!empty($select_quote_to_move)) {

                    // Update DB for active files

                    Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('UPDATE `' . _DB_PREFIX_ . 'quote_request` SET `id_order` = ' . (int)$order->id . ', `id_customer` = ' . (int)$order->id_customer . ', `id_cart` = 0 , current_status = "4" WHERE `id_cart` = ' . (int)$order->id_cart . ' AND current_status = "1"');
                }
            }
        } catch (Exception $e) {
            // Do nothing
        }
    }


    public function hookDisplayHeader()
    {
        $css = $js = '';

        $context = Context::getContext();
        $current_language_id = $context->language->id;
        if (Tools::getValue('controller') == 'quote_request') {
            //$this->loadIconFontIfRequired();

            //$this->addCustomMedia();
            Media::addJsDef(array(
                'kd_ajax_path' => $this->context->link->getModuleLink($this->name, 'ajax', array('ajax' => 1)),
                'current_controller' => Tools::getValue('controller'),
                'is_17' => (int)$this->is_17,
            ));

            $this->addJS('front.js');
            $this->addCSS('front.css');
        } else if (Tools::getValue('controller') == 'frontquoterequest') {
            $this->addCSS('file-upload-with-preview.min.css');
            $this->addJS('file-upload-with-preview.min.js');
        }
        // echo '<pre>';
        // print_r($js);
        // echo '</pre>';

        return ($css ? '<style type="text/css">' . $css . '</style>' : '')
            . ($js ? '<script type="text/javascript">' . $js . '</script>' : '');
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
        //$this->context->controller->css_files[$this->_path . 'views/css/front.css?v=' . $this->version] = 'all';

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
        $this->context->controller->css_files[$this->_path . 'views/css/file-upload-with-preview.min.css?v=' . $this->version] = 'all';
        $this->context->controller->js_files[] = $this->_path . 'views/js/file-upload-with-preview.min.js?v=' . $this->version;

        // plain js for retro-compatibility
        $js = '<script type="text/javascript">';
        foreach ($js_def as $name => $value) {
            $js .= "\nvar $name = '" . $this->escapeApostrophe($value) . "';";
        }
        $js .= "\n</script>";
        echo $this->context->controller->page_name;
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
