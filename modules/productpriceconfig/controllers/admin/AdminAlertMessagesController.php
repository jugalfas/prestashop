<?php

/**
 * 2017-2018 Krupaludev
 *
 * Bulk Discount Manager for Imorting and Creating
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

require_once dirname(__FILE__) . '/../../libraries/KDToolTip.php';
require_once dirname(__FILE__) . '/../../libraries/KDOption.php';
require_once dirname(__FILE__) . '/../../libraries/KDVariable.php';
require_once dirname(__FILE__) . '/../../libraries/KDProductVariable.php';
require_once dirname(__FILE__) . '/../../libraries/KDAlertMessage.php';

class AdminAlertMessagesController extends ModuleAdminController
{
    public $bootstrap = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'alert_messages';
        $this->className = 'KDAlertMessage';
        // $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->translator = Context::getContext()->getTranslator();
        // $this->_orderWay = 'DESC';
        $this->_orderBy = 'id_alert_messages';

        $this->identifier = 'id_alert_messages';


        $this->context = Context::getContext();
        $this->show_toolbar = false;

        $this->page_header_toolbar_title = $this->l('Alert messages');

        $this->fields_list = array(
            'id_alert_messages' => array('title' => $this->trans('ID', array(), 'Admin.Global'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'product' => array('title' => $this->trans('Product', array(), 'Admin.Global')),
            'variable' => array('title' => $this->trans('Variable', array(), 'Admin.Global')),
            'option' => array('title' => $this->trans('Option', array(), 'Admin.Global')),
            'message' => array('title' => $this->trans('Message', array(), 'Admin.Global')),
        );

        parent::__construct();

        $this->_path = _MODULE_DIR_ . $this->module->name;

        $this->context->smarty->assign(array(
            'module_name'         => $this->module->name,
            'module_display_name' => $this->module->displayName,
            'module_version'      => $this->module->version,
            'module_views_dir'    => _PS_ROOT_DIR_ . '/modules/' . $this->module->name . '/views/',
            'module_path'         => _MODULE_DIR_ . $this->module->name . '/views/',
        ));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme = false);
        Media::addJsDef(['alertAjaxUrl' => $this->context->link->getAdminLink('AdminAlertMessages', true) . "&ajax=1"]);
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/back.js');
    }

    public function init()
    {
        parent::init();

        if (Tools::getValue('ajax')) {
            $this->ajaxProcess();
        }
    }

    protected function ajaxProcess()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'getOptions':
                $this->ajaxProcessGetOptions();
                break;
        }
    }

    public function renderForm()
    {
        // $query1 = "DROP TABLE IF EXISTS " . _DB_PREFIX_ . "alert_messages";
        // $query3 = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "alert_messages` (
        //     `id_alert_messages` int(10) unsigned NOT NULL AUTO_INCREMENT,
        //     `product_id` int(10) unsigned NOT NULL,
        //     `variable_id` int(10) unsigned NOT NULL,
        //     `option_id` int(10) unsigned NOT NULL,
        //     `message` text NOT NULL,
        //     PRIMARY KEY (`id_alert_messages`)
        // ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8";

        // // Execute queries
        // Db::getInstance()->execute($query1);
        // Db::getInstance()->execute($query3);

        if (!$this->loadObject(true)) {
            return;
        }

        $obj = $this->loadObject(true);

        $form_action = 'Add';
        $this->display = 'add';
        if (isset($obj->id)) {
            $this->display = 'edit';
            $form_action = 'Edit';

            $sql = "SELECT * FROM " . _DB_PREFIX_ . "alert_messages am WHERE am.id_alert_messages = $obj->id";

            $result = Db::getInstance()->executeS($sql);
            $product_id = $result[0]['product_id'];
            $variable_id = $result[0]['variable_id'];
            $option_id = $result[0]['option_id'];
            $message = $result[0]['message'];
        }

        if(!isset($variable_id)){
            $variable_id = 1;
        }
        $variable_obj = new KDVariable($variable_id);
        $options = $variable_obj->getAllOptions($this->context->language->id);


        $array_submit2 = array(
            array(
                'type' => 'select',
                'label' => $this->module->l('Product'),
                'name' => 'product_id',
                'options' => array(
                    'query' => $this->getProducts(),
                    'id' => 'id',
                    'name' => 'label',
                ),
                'required' => true,
                'default_value' => $product_id
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Variable'),
                'name' => 'variable_id',
                'options' => array(
                    'query' => $this->getVariables(),
                    'id' => 'id',
                    'name' => 'label',
                ),
                'required' => true,
                'default_value' => $variable_id
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Option'),
                'name' => 'option_id',
                'options' => array(
                    'query' => $options,
                    'id' => 'id',
                    'name' => 'label',
                ),
                'required' => true,
                'default_value' => $option_id
            ),
            array(
                'type' => 'textarea',
                'label' => $this->module->l('Message'),
                'name' => 'message',
                'required' => true,
                'class' => 'fixed-width-xl',
                'default_value' => $message,
            ),

        );

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->module->l('Save alert messages'),
            ),
            'input' => $array_submit2,
            'submit' => array(
                'title' => $this->module->l('Save'),
                'class' => 'btn btn-default',
                'name' => 'submit' . $form_action . $this->table
            ),
            'buttons' => [
                'save-and-stay' => array(
                    'title' => $this->module->l('Save and add another alert'),
                    'name' => 'submit' . $form_action . $this->table . 'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                )
            ],
        );

        $this->multiple_fieldsets = true;

        $this->setMedia();
        error_log('Form Data: ' . print_r($_POST, true));

        return parent::renderForm();
    }

    public function renderList()
    {
        $this->getList($this->context->language->id);
        if (array_key_exists('active', $this->fields_list) && $this->fields_list['active'] == true) {
            if (!is_array($this->bulk_actions)) {
                $this->bulk_actions = [];
            }

            $this->bulk_actions = array_merge([
                'enableSelection' => [
                    'text' => $this->trans('Enable selection', [], 'Admin.Actions'),
                    'icon' => 'icon-power-off text-success',
                ],
                'disableSelection' => [
                    'text' => $this->trans('Disable selection', [], 'Admin.Actions'),
                    'icon' => 'icon-power-off text-danger',
                ],
                'divider' => [
                    'text' => 'divider',
                ],
            ], $this->bulk_actions);
        }

        $helper = new HelperList();

        // Empty list is ok
        if (!is_array($this->_list)) {
            $this->displayWarning($this->trans('Bad SQL query', [], 'Admin.Notifications.Error') . '<br />' . htmlspecialchars($this->_list_error));

            return false;
        }

        $this->setHelperDisplay($helper);
        $helper->_default_pagination = $this->_default_pagination;
        $helper->_pagination = $this->_pagination;
        $helper->tpl_vars = $this->getTemplateListVars();
        $helper->tpl_delete_link_vars = $this->tpl_delete_link_vars;

        // For compatibility reasons, we have to check standard actions in class attributes
        foreach ($this->actions_available as $action) {
            if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action) {
                $this->actions[] = $action;
            }
        }

        $helper->is_cms = $this->is_cms;
        $helper->sql = $this->_listsql;
        $list = $helper->generateList($this->_list, $this->fields_list);
        return $list;
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        foreach ($this->_list as &$row) {
            $product_id = $row['product_id'];
            $variable_id = $row['variable_id'];
            $option_id = $row['option_id'];

            $product = $this->get_product_from_product_id($product_id);
            $variable = $this->get_variable_from_variable_id($variable_id);
            $option = $this->get_option_from_option_id($option_id);
            $row['product'] = $product->name;
            $row['variable'] = $variable['label'];
            $row['option'] = $option['label'];
        }

        return $this->_list;
    }

    public function postProcess()
    {
        error_log('postProcess Data: ' . print_r($_POST, true));

        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $this->processAddAlertMessage();
        } elseif (Tools::isSubmit('submitEdit' . $this->table)) {
            $id_alert_messages = Tools::getValue('id_alert_messages');
            $this->processEditAlertMessage($id_alert_messages);
        } elseif (Tools::isSubmit('delete' . $this->table)) {
            $id_alert_message = Tools::getValue('id_alert_messages');
            $this->processDeleteAlertMessage($id_alert_message);
        }
        parent::postProcess();
    }

    protected function processAddAlertMessage()
    {
        $product_id = Tools::getValue('product_id');
        $variable_id = Tools::getValue('variable_id');
        $option_id = Tools::getValue('option_id');
        $message = Tools::getValue("message");

        $insert_sql = "INSERT INTO `" . _DB_PREFIX_ . "alert_messages` (`product_id`, `variable_id`, `option_id` , `message`) VALUES ('$product_id','$variable_id','$option_id','$message')";

        if (Db::getInstance()->execute($insert_sql)) {
            $this->context->controller->confirmations[] = $this->module->l('Record inserted successfully.');
        } else {
            $this->context->controller->errors[] = $this->module->l('Failed to insert record.');
        }
    }

    protected function processEditAlertMessage($id_alert_messages)
    {
        $product_id = Tools::getValue('product_id');
        $variable_id = Tools::getValue('variable_id');
        $option_id = Tools::getValue('option_id');
        $message = Tools::getValue('message');

        $update_sql = "UPDATE `" . _DB_PREFIX_ . "alert_messages` 
            SET `product_id`='$product_id', 
                `variable_id`='$variable_id', 
                `option_id`='$option_id', 
                `message`='$message'
            WHERE `id_alert_messages`='$id_alert_messages'";

        if (Db::getInstance()->execute($update_sql)) {
            $this->context->controller->confirmations[] = $this->module->l('Record updated successfully.');
        } else {
            $this->context->controller->errors[] = $this->module->l('Failed to update record.');
        }
    }

    protected function processDeleteAlertMessage($id_alert_message)
    {
        $table_name = 'alert_messages';
        $condition = 'id_alert_messages = ' . $id_alert_message;

        $result = Db::getInstance()->delete($table_name, $condition);
        if ($result) {
            $this->context->controller->confirmations[] = $this->module->l('Record deleted successfully.');
        } else {
            $this->context->controller->errors[] = $this->module->l('Failed to delete record.');
        }
    }

    public function getProducts()
    {
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');

        $products_arr = [];
        foreach ($products as $key => $product) {
            $products_arr[] = ['id' => $product['id_product'], 'label' => $product['name']];
        }
        return $products_arr;
    }

    public function get_product_from_product_id($product_id)
    {
        $product = new Product($product_id, false, Context::getContext()->language->id);

        if (Validate::isLoadedObject($product)) {
            return $product;
        }
    }
    protected function getVariables()
    {
        $variables = array();

        $sql = 'SELECT v.id_variable AS id, vl.label
            FROM ' . _DB_PREFIX_ . 'variable v
            LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang vl ON v.id_variable = vl.id_variable
            WHERE vl.id_lang = 3';

        $result = Db::getInstance()->executeS($sql);

        if ($result) {
            foreach ($result as $row) {
                $variables[] = $row;
            }
        }

        return $variables;
    }

    public function ajaxProcessGetOptions()
    {
        $variable_id = Tools::getValue('variable_id');
        $id_alert_messages = Tools::getValue('id_alert_messages');
        $option_value = "";
        if (isset($id_alert_messages) && $id_alert_messages != "") {
            $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'alert_messages
                WHERE id_alert_messages = ' . $id_alert_messages;

            $alert_messages = Db::getInstance()->executeS($sql)[0];
            $option_value = $alert_messages['option_id'];
        }

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'variable WHERE id_variable = ' . $variable_id;
        $variable = Db::getInstance()->executeS($sql)[0];
        $variable_type = $variable['type'];

        $options = array();

        $sql = 'SELECT o.id_option AS id, ol.label
            FROM ' . _DB_PREFIX_ . 'option o
            LEFT JOIN ' . _DB_PREFIX_ . 'option_lang ol ON o.id_option = ol.id_option
            WHERE ol.id_lang = 3 AND o.id_variable = ' . $variable_id;

        $result = Db::getInstance()->executeS($sql);

        $html = "";
        if ($variable_type == 1 || $variable_type == 4) {
            $html .= "<input type='number' class='form-control fixed-width-xl' id='option_id' value='$option_value' name='option_id'>";
        } else if ($variable_type == 5) {
            $html .= "<input type='text' class='form-control fixed-width-xl' id='option_id' value='$option_value' name='option_id'>";
        } else {
            if ($result) {
                $html .= "<select name='option_id' class='fixed-width-xl' id='option_id'>";
                foreach ($result as $row) {
                    $options[] = $row;
                    $selected = "";
                    if (isset($option_value) && $row['id'] == $option_value) {
                        $selected = "selected";
                    }
                    $html .=  "<option value='" . $row['id'] . "' $selected>" . $row['label'] . "</option>";
                }
                $html .= "</select>";
            }
        }

        echo $html;
        exit;
    }

    public function get_variable_from_variable_id($variable_id)
    {
        $language_id = $this->context->language->id;
        $sql = 'SELECT v.*, vl.*
            FROM ' . _DB_PREFIX_ . 'variable v
            LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang vl ON v.id_variable = vl.id_variable
            WHERE v.id_variable = ' . $variable_id . ' AND vl.id_lang = ' . $language_id;

        $result = Db::getInstance()->executeS($sql);

        return $result[0];
    }

    public function get_option_from_option_id($option_id)
    {
        $language_id = $this->context->language->id;
        $sql = 'SELECT v.*, vl.*
            FROM ' . _DB_PREFIX_ . 'option v
            LEFT JOIN ' . _DB_PREFIX_ . 'option_lang vl ON v.id_option = vl.id_option
            WHERE v.id_option = ' . $option_id . ' AND vl.id_lang = ' . $language_id;

        $result = Db::getInstance()->executeS($sql);

        return $result[0];
    }

    public function getDataFromOptionId($option_id)
    {
        $language_id = 3;
        // $language_id = $this->context->language->id;
        $sql = 'SELECT v.*, vl.*
            FROM ' . _DB_PREFIX_ . 'option v
            LEFT JOIN ' . _DB_PREFIX_ . 'option_lang vl ON v.id_option = vl.id_option
            WHERE v.id_option = ' . $option_id . ' AND vl.id_lang = ' . $language_id;

        $result = Db::getInstance()->executeS($sql);

        return $result[0];
    }
}
