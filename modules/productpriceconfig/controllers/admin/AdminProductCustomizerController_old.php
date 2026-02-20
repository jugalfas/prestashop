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

require_once dirname(__FILE__) . '/../../libraries/krupaludev/Products.php';
require_once dirname(__FILE__) . '/../../libraries/KDQuestionSet.php';
require_once dirname(__FILE__) . '/../../libraries/KDQuestion.php';
require_once dirname(__FILE__) . '/../../libraries/KDOption.php';
require_once dirname(__FILE__) . '/../../libraries/KDUploadHandler.php';

/**
 * Class AdminProductCustomizerController
 */
class AdminProductCustomizerController extends ModuleAdminController
{

  public $_path = '';

  protected $ssl = 'http://';

  public function __construct()
  {
      $this->bootstrap = true;
      $this->table = 'question';
      $this->className = 'KDQuestion';
      //$this->lang = true;
      $this->addRowAction('edit');
      $this->addRowAction('delete');
      $this->_orderWay = 'DESC';
      $this->_orderBy = 'id_question';

      $this->bulk_actions = array('delete' => array('text' => $this->trans('Delete selected' ,array(), 'Admin'),
      'icon' => 'icon-trash', 'confirm' => $this->trans('Delete selected items?',array(), 'Admin')));

      $this->fields_list = array(
          'id_question' => array('title' => $this->trans('ID' ,array(), 'Admin.Orders'), 'align' => 'center', 'class' => 'fixed-width-xs'),
          'name' => array('title' => $this->trans('Name' , array(), 'Admin.Orders')),
          'type' => array('title' => $this->trans('Type', array(), 'Admin.Orders'),  'callback' => 'printType'),
          'active' => array('title' => $this->trans('Status', array(), 'Admin.Orders'),
          'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-xs', 'orderby' => false),
      );

      parent::__construct();

      $this->_path = _MODULE_DIR_. $this->module->name ;

      $this->context->smarty->assign(array(
          'module_name'         => $this->module->name,
          'module_display_name' => $this->module->displayName,
          'module_version'      => $this->module->version,
          'module_views_dir'    => _PS_ROOT_DIR_ . '/modules/' . $this->module->name . '/views/',
          'module_path'         => _MODULE_DIR_. $this->module->name . '/views/',
      ));

  }

    public function init()
    {
        parent::init();

        if (Tools::isSubmit('addquestion_set') || Tools::isSubmit('editquestion_set')) {
            $this->display = 'editquestion_set';
        } elseif (Tools::isSubmit('updatequestion_set')) {
            $this->display = 'editquestion_set';
        } elseif (Tools::isSubmit('question_set')) {
            $this->display = 'question_set';
        } elseif (Tools::isSubmit('submitQuestion_set')) {
            $this->action = 'save';
        } elseif (Tools::isSubmit('deletequestion_set')) {
            $this->action = 'delete';
        }

        if (!Shop::getContextShopID()) {
            $controller = 'AdminProductCustomizer';
            $id_lang = $this->context->language->id;
            $params = array(
                'token'          => Tools::getAdminTokenLite($controller),
                'setShopContext' => 's-' . current(Shop::getContextListShopID())
            );
            $link = Dispatcher::getInstance()->createUrl($controller, $id_lang, $params, false);
            Tools::redirectAdmin($link);
            die();
        }

        $this->bootstrap = true;
    }

    public function initProcess()
    {
        if (Tools::isSubmit('submitQuestion_set') || Tools::isSubmit('deletequestion_set') || Tools::isSubmit('submitBulkdeletequestion_set') || Tools::isSubmit('exportquestion_set')) {
            $this->table = 'question_set';
            $this->className = 'KDQuestionSet';
            $this->identifier = 'id_question_set';
            $this->fields_list = $this->getQuestionSetFieldsList();
        }
        parent::initProcess();
    }

    public function processSave()
    {
        if (Tools::isSubmit('submitQuestion_set')) {
            $this->display = 'editquestion_set';
        }

        return parent::processSave();
    }

    public function setMedia()
    {
        parent::setMedia();

        Media::addJsDefL('theme_url', $this->context->link->getAdminLink('AdminProductCustomizer'));
        Media::addJsDefL('question_warning', $this->trans('All selected products will cleared', array(), 'Admin.Orders'));

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/dataTables.bootstrap.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/select2.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery.fileupload.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery.fileupload-ui.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/bootstrap-editable.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');

        $this->addJquery();
        $this->addJqueryUI('ui.widget');
        $this->addJqueryUI('ui.tabs');
        $this->addJqueryUI('ui.sortable');

        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/select2.full.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tmpl.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.iframe-transport.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-process.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-validate.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-ui.min.js');

        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.serializejson.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.adminproductcustomizer.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.adminselectoptions.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/form.js');
    }

    public function initContent()
    {

        $ajax = (bool)Tools::getValue('ajax', false);
        if ($ajax) {
            return;
        }

        if ($this->display == 'editquestion_set' || $this->display == 'question_set') {
            $this->content .= $this->renderFormQuestionSet();
        } elseif ($this->display == 'edit' || $this->display == 'add') {
            if (!$this->loadObject(true)) {
                return;
            }
            $this->content .= $this->renderForm();
        } elseif (!$this->ajax) {
            $this->content .= $this->renderList();
            $this->content .= $this->renderOptions();
        }

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));

        //parent::initContent();

        //$this->setTemplate('configuration.tpl');
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_question'] = array(
                'href' => self::$currentIndex.'&addquestion&token='.$this->token,
                'desc' => $this->trans('Add new question',array(), 'Admin.Orders'),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['new_question_set'] = array(
                'href' => self::$currentIndex.'&addquestion_set&token='.$this->token,
                'desc' => $this->trans('Add new question set', array(), 'Admin.Orders'),
                'icon' => 'process-icon-new'
            );
        } elseif ($this->display == 'editquestion_set' || $this->display == 'addquestion_set') {
            // Default cancel button - like old back link
            if (!isset($this->no_back) || $this->no_back == false) {
                $back = Tools::safeOutput(Tools::getValue('back', ''));
                if (empty($back)) {
                    $back = self::$currentIndex.'&token='.$this->token;
                }

                $this->page_header_toolbar_btn['cancel'] = array(
                    'href' => $back,
                    'desc' => $this->trans('Cancel', array(), 'Admin.Actions')
                );
            }
        }

        parent::initPageHeaderToolbar();
    }

    public function initListQuestion()
    {
        //$this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->context->smarty->assign('title_list', $this->trans('List of questions', array(), 'Admin.Orders'));

        $this->content .= parent::renderList();
    }

    protected function getQuestionSetFieldsList()
    {

        return array(
            'id_question_set' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->trans('Title', array(), 'Admin.Global'),
                'width' => 'auto',
                'filter_key' => 'question_set_name'
            )
        );
    }

    public function initListQuestionSet()
    {
        $this->toolbar_title = $this->trans('Question Set', array(), 'Admin.Orders');
        // reset actions and query vars
        $this->actions = array();
        unset($this->fields_list, $this->_select, $this->_join, $this->_group, $this->_filterHaving, $this->_filter);

        $this->table = 'question_set';
        $this->list_id = 'question_set';
        $this->identifier = 'id_question_set';

        $this->_defaultOrderBy = 'id_question_set';
        $this->_defaultOrderWay = 'ASC';

        $this->_orderBy = null;

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // test if a filter is applied for this list
        if (Tools::isSubmit('submitFilter'.$this->table) || $this->context->cookie->{'submitFilter'.$this->table} !== false) {
            $this->filter = true;
        }

        // test if a filter reset request is required for this list
        $this->action = (isset($_POST['submitReset'.$this->table]) ? 'reset_filters' : '');

        $this->fields_list = $this->getQuestionSetFieldsList();
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(),'Admin.Actions' ),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(),'Admin.Notifications.Warning' )
            )
        );


        $this->context->smarty->assign('title_list', $this->trans('Question Set', array(), 'Admin.Orders'));

        // call postProcess() for take care about actions and filters
        $this->postProcess();

        $this->initToolbar();

        $this->content .= parent::renderList();
    }

    public function renderList()
    {
        $this->initListQuestion();
        $this->initListQuestionSet();
    }


    public function postProcess()
    {
        if (Tools::isSubmit('submitAddquestion')) {

            if (in_array(Tools::getValue('type'), array(3,4)) && !Tools::getValue('selected_options')) {
                $this->errors[] = Tools::displayError('Please select the options');
            }

            if (in_array(Tools::getValue('type'), array(5)) && !Tools::getValue('fee_amount')) {
                $this->errors[] = Tools::displayError('Amount cannot be lower than zero.');
            }

        }
        return parent::postProcess();
    }

    protected function afterAdd($currentObject)
    {
      if (Tools::isSubmit('submitQuestion_set')) {
        Db::getInstance()->delete('question_set_question', '`id_question_set` = '.(int)$currentObject->id);
        $type = 'question';
        if (is_array($array = Tools::getValue('question_select')) && count($array)) {
            $values = array();
            foreach ($array as $id) {
                $values[] = '('.(int)$currentObject->id.','.(int)$id.')';
            }

            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'question_set_question` (`id_question_set`, `id_question`) VALUES '.implode(',', $values));
        }
      }

    }

    public function processUpdate2()
    {
        /* Checking fields validity */
        $this->validateRules();
        if (empty($this->errors)) {
            $id = (int)Tools::getValue($this->identifier);

            /* Object update */
            if (isset($id) && !empty($id)) {
                /** @var ObjectModel $object */
                $object = new $this->className($id);
                if (Validate::isLoadedObject($object)) {
                  $this->beforeUpdate($object);
                }
            }
        }

        return parent::processUpdate();

    }

    protected function beforeUpdate2($currentObject)
    {
      if($currentObject->type == 1 || $currentObject->type == 2){
        $options = Tools::jsonDecode($currentObject->selected_options, true);
        if(count($options)){
          foreach ($options as $id_option) {
            SpecificPrice::deleteByProductId($id_option);
          }
        }
      }elseif(in_array($currentObject->type, array(3,4,5)) ){
        $cart_rule = new CartRule($currentObject->id_cart_rule);
        $cart_rule->delete();
      }

    }

    protected function afterUpdate($currentObject)
    {
      $this->afterAdd($currentObject);
    }

    public function processDelete()
    {
        $res = parent::processDelete();
        if (Tools::isSubmit('delete'.$this->table)) {
            $back = urldecode(Tools::getValue('back', ''));
            if (!empty($back)) {
                $this->redirect_after = $back;
            }
        }
        return $res;
    }

    protected function beforeDelete($currentObject)
    {
        return true;
    }


    public function renderForm()
    {
        $limit = 40;

        /** @var  $current_object */
        $current_object = $this->loadObject(true);

        $currencies = Currency::getCurrencies(false, true, true);
        $languages = Language::getLanguages();

        $types  = array(
          array('value' => 1, 'name' => 'Text'),
          array('value' => 2, 'name' => 'File upload'),
          array('value' => 3, 'name' => 'Radio'),
          array('value' => 4, 'name' => 'Checkbox'),
          array('value' => 5, 'name' => 'Additional fee')
        );

        $this->context->smarty->assign(
            array(
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'title' => array('Question'),
                'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
                'languages' => $languages,
                'currencies' => $currencies,
                'types' => $types,
                'currentIndex' => self::$currentIndex,
                'currentToken' => $this->token,
                'currentObject' => $current_object,
                'currentTab' => $this,
                'selected_options' => array(
                    'json' => Tools::getValue('selected_options', $current_object->selected_options),
                    'options' => $this->getOptionsConfig(Tools::jsonDecode(Tools::getValue('selected_options', $current_object->selected_options)))
                ),
            )
        );
        Media::addJsDef(array('baseHref' => $this->context->link->getAdminLink('AdminProductCustomizer').'&ajaxMode=1&ajax=1&id_question='.
                                     (int)Tools::getValue('id_question').'&action=loadCartRules&limit='.(int)$limit.'&count=0'));

        $this->addJqueryPlugin(array('jscroll', 'typewatch'));
        $this->content .= $this->createTemplate('form.tpl')->fetch();

        return parent::renderForm();

    }

    public function renderFormQuestionSet()
    {
        // Change table and className for addresses
        $this->table = 'question_set';
        $this->className = 'KDQuestionSet';
        $id_question_set = Tools::getValue('id_question_set');

        // Create Object Address
        $question_set = new KDQuestionSet($id_question_set);

        $this->initToolbar();


        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex.'&token='.$this->token;
        }
        if (!Validate::isCleanHtml($back)) {
            die(Tools::displayError());
        }

        $languages = Language::getLanguages();

        $questions = $question_set->getAssociatedQuestions('question');

        $this->context->smarty->assign(
            array(
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'title' => array('Question Set'),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
                'languages' => $languages,
                'currentIndex' => self::$currentIndex,
                'currentToken' => $this->token,
                'currentObject' => $question_set,
                'questions' => $questions,
                'currentTab' => $this,
            )
        );

        $this->content .= $this->createTemplate('form_set.tpl')->fetch();

        return parent::renderForm();

    }

    public function printType($id_object, $tr)
    {
      $types  = array(
        array('value' => 1, 'name' => 'Text'),
        array('value' => 2, 'name' => 'File upload'),
        array('value' => 3, 'name' => 'Radio'),
        array('value' => 4, 'name' => 'Checkbox'),
        array('value' => 5, 'name' => 'Additional fee')
      );

      foreach ($types as $type) {
        if($tr['type'] == $type['value']){
          return $type['name'];
        }
      }


    }

    public function displayAjax()
    {
        $method = Tools::getValue('method', false);
        $header = Tools::getValue('header', 'json');

        if ($method !== false && method_exists($this, $method)) {
            if ($header == 'json') {
                header('Content-Type: application/json');
                echo Tools::jsonEncode($this->$method());
            } else {
                echo $this->$method();
            }
        }
        die();
    }

    public function getAjaxOptions()
    {

        $options = $this->getOptions(Tools::getValue('search'));
        if (!$selected_options = Tools::jsonDecode(Tools::getValue('selected_options'))) {
            $content = $this->renderOptionList($options);
            die(Tools::jsonEncode(array('status' => 'true', 'content' => $content)));
        }

        foreach ($options as $key => $option) {
            if (is_numeric(array_search($option['id_option'], $selected_options))) {
                unset($options[$key]);
            }
        }

        if (count($options)) {
            $content = $this->renderOptionList($options);
        } else {
            $content = $this->trans('No options to select',array(), 'Admin.Orders');
        }


        die(Tools::jsonEncode(array('status' => 'true', 'content' => $content)));
    }


    public function deleteItem()
    {
        if (Tools::getValue('configure') == $this->name) {
            $tab = new KDQuestion((int)Tools::getValue('id_tab'));
            if (!$tab->delete()) {
                $this->_errors[] = $this->trans('Can\'t delete item',array(), 'Admin.Orders');
            } else {
                $this->_confirmations[] = $this->trans('Item deleted', array(), 'Admin.Orders');
            }
            $this->clearCache();
        }

        return true;
    }

    public function updateStatus()
    {
        $item = new KDQuestion(Tools::getValue('id_tab'));

        if (!$item->toggleStatus()) {
            $this->_errors[] = $this->trans('Item status can\'t be updated', array(), 'Admin.Orders');
        } else {
            $this->_confirmations[] = $this->trans('Item status updated' ,array(), 'Admin.Orders');
        }
        $this->clearCache();
    }


    protected function validateTabFields()
    {

    }


    protected function getImageLink($id_option)
    {
        $image = null;
        $option = new KDOption($id_option, $this->context->language->id);

        $image = $this->_path.'/views/img/upload/'.$option->image;

        return $image;

    }

    public function getOptions($search)
    {
        $options_list = array();
        //$options_ids = KDOption::searchByName((int)$this->context->language->id, $search);
        $options_ids = KDOption::getOptionsIDs();

       if(count($options_ids) > 0){
         foreach ($options_ids as $key => $option_id) {
             $options_list = array_merge($options_list, $this->getOption($option_id['id_option']));
         }
       }

        return $options_list;
    }

    protected function getOption($id_option)
    {
        $option_list = array();

        $option = new KDOption($id_option, $this->context->language->id);
        $option_list[$id_option]['id_option'] = $option->id;
        $option_list[$id_option]['name'] = $option->label;
        $option_list[$id_option]['image'] = $this->getImageLink($option->id);
        $option_list[$id_option]['price'] = $option->price;

        return $option_list;
    }

    protected function getOptionsConfig($options_ids)
    {
        if (count($options_ids) > 0) {
            $options_list = array();
            foreach ($options_ids as $key => $option_id) {
                $options_list = array_merge($options_list, $this->getOption($option_id));
            }

            return $options_list;
        }

        return array();
    }

    protected function getKDQuestion($question, $options_ids)
    {
        $result = array();
        $options = $question->getOptions((int)$this->context->language->id, 1, 10000);
        if (count($options_ids) > 0 && count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($options_ids) > 0) {
                    if (is_numeric($id = array_search($option['id_option'], $options_ids))) {
                        $result[$id] = $option;
                        unset($options_ids[$id]);
                    }
                } else {
                    break;
                }
            }
        }

        ksort($result, SORT_NUMERIC);
        return $result;
    }

    protected function deleteProduct($id_option, $id_shop)
    {
        $question_options = new KDQuestion();
        $categories = $question_options->getAllItems($id_shop);

        foreach ($categories as $question) {
            $options = Tools::jsonDecode($question['selected_options'], true);
            if (count($options) > 0) {
                if (is_numeric($id = array_search($id_option, $options))) {
                    unset($options[$id]);
                    $question_obj = new KDQuestion($question['id_tab']);
                    $question_obj->selected_options = Tools::jsonEncode($options);

                    $question_obj->save();
                }
            }
        }
    }

    public function renderOptionList($options)
    {
        $this->context->smarty->assign(array(
            'options' => $options
        ));

        return $this->createTemplate('option_list.tpl')->fetch();

        //return $this->display($this->_path, '/views/templates/admin/option_list.tpl');
    }


    public function import()
    {
        $file = Tools::getValue('file', '');
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/' . $file;
        $csv_separator = Tools::getValue('csv_separator', ',');
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        return KDQuestion::import($file, $csv_separator, $id_lang, $id_shop);
    }


    public function processAttachment()
    {
        $id_employee = $this->context->cookie->id_employee;
        $token = Tools::getAdminToken('AdminProductCustomizer' . (int)Tab::getIdFromClassName('AdminProductCustomizer') . (int)$id_employee);
        $url = 'index.php?controller=AdminProductCustomizer&token=' . $token . '&ajax=1';

        $uploads = new KDUploadHandler(
            array(
                'upload_dir' => _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/',
                'script_url' => $url . '&method=processAttachment',
                'upload_url' => $url . '&method=processAttachment&header=csv&download=1&file=',
                'param_name' => 'files'
            )
        );

        return $uploads->getResponse();
    }


    public function getImportStatus()
    {
        $import_status = Tools::jsonDecode(Tools::file_get_contents(_PS_MODULE_DIR_ . $this->module->name . '/data/ImportStatus.json'));
        if (is_object($import_status) && isset($import_status->rowImported)) {
            return $import_status;
        } else {
            return (object)array('rowImported' => 'Wait..');
        }
    }


}
