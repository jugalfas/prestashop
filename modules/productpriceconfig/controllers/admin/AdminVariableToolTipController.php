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

class AdminVariableToolTipController extends ModuleAdminController
{

 
  public $bootstrap = true;
	

  public function __construct()
  {
    $this->bootstrap = true;
      $this->table = 'variable_tooltip';
      $this->className = 'KDToolTip';
     // $this->lang = true;
      $this->addRowAction('edit');
      $this->addRowAction('delete');
      $this->translator = Context::getContext()->getTranslator();
      // $this->_orderWay = 'DESC';
      $this->_orderBy = 'id_variable_tooltip';

      $this->identifier = 'id_variable_tooltip';
			

      $this->context = Context::getContext();
      $this->lang = true;
      $this->show_toolbar = false;

      $this->page_header_toolbar_title = $this->l('Variable tooltip');

      $this->fields_list = array(
          'id_variable_tooltip' => array('title' => $this->trans('ID' ,array(), 'Admin.Global'), 'align' => 'center', 'class' => 'fixed-width-xs'),
          'label' => array('title' => $this->trans('Name' , array(), 'Admin.Global')),
          'text' => array('title' => $this->trans('Text' , array(), 'Admin.Global')),
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

    

  public function renderForm()
  {
      if (!$this->loadObject(true)) {
          return;
      }

      $obj = $this->loadObject(true);

      if (isset($obj->id)) {
          $this->display = 'edit';
      } else {
          $this->display = 'add';
      }

      $array_submit2 = array(
          array(
              'type' => 'text',
              'label' => $this->module->l('Name'),
              'name' => 'label',
              'required' => true,
              'col' => '4',
              'hint' => $this->module->l('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"°{}_$%:',
          ),
          array(
              'type' => 'textarea',
              'label' => $this->module->l('Text'),
              'name' => 'text',
              'required' => false,
              'autoload_rte' => true,
              'lang' => true,
              'rows' => 10,
              'cols' => 100,
              'hint' => $this->module->l('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"°{}_$%:',
          ),
          
      );

      
      $this->fields_form[0]['form'] = array(
          'legend' => array(
              'title' => $this->module->l('Save tool tips'),
          ),
          'input' => $array_submit2,
          'submit' => array(
              'title' => $this->module->l('Save'),
              'class' => 'btn btn-default',
          ),
      );

      $this->multiple_fieldsets = true;


      return parent::renderForm();
  }
   
    
}
