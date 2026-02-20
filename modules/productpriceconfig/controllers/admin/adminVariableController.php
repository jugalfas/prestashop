<?php

require_once dirname(__FILE__) . '/../../libraries/KDVariable.php';
require_once dirname(__FILE__) . '/../../libraries/KDOption.php';

class AdminVariableController extends ModuleAdminController
{
	public $bootstrap = true;
	public $tpl_form;
	protected $position_identifier = 'id_variable';
	public function __construct()
	{
			$this->bootstrap = true;
			$this->table = 'variable';
			$this->className = 'KDVariable';
			$this->lang = true;
			$this->display = 'list';
			$this->_defaultOrderBy = 'position';
			
			$this->identifier = 'id_variable';
			
			
			parent::__construct();
			
			$this->fields_list = array(
				'id_variable' => array(
					'title' => $this->l('ID'),
					'align' => 'center',
					'width' => 25
				),
				'name' => array(
					'title' => $this->l('Name'),
					'filter_key' => 'b!name'
				),
				'position' => [
					'title' => $this->trans('Position', [], 'Admin.Global'),
					'filter_key' => 'a!position',
					'position' => 'position',
					'align' => 'center',
					'class' => 'fixed-width-xs',
				],
				'type' => array('title' => $this->l('Type'),  'callback' => 'printType'),
				'active' => array(
					'title' => $this->l('Active'),
					'active' => 'active',
					'type' => 'bool',
					'class' => 'fixed-width-xs',
					'align' => 'center',
					'orderby' => false
				),
			);
			
		    parent::__construct();
		}

		public function printType($id_object, $tr)
		{
			$types  = array(
				array('value' => 1, 'name' => 'Quantity input'),
				array('value' => 2, 'name' => 'Dropdown list'),
				array('value' => 3, 'name' => 'Fixed value'),
				array('value' => 4, 'name' => 'Custom input'),
				array('value' => 5, 'name' => 'Custom Text input'),
				array('value' => 6, 'name' => 'Spine thickness formula'),
				array('value' => 7, 'name' => 'Shipping type'),
			);

			foreach ($types as $type) {
				if($tr['type'] == $type['value']){
				return $type['name'];
				}
			}
		}
		
		public function renderList()
		{
			if (Tools::getIsset($this->_filter) && trim($this->_filter) == ''){
					$this->_filter = $this->original_filter;
			}
				
			$this->addRowAction('edit');
			$this->addRowAction('view');
			$this->addRowAction('delete');
	
			return parent::renderList();
		}
		
		
		
		public function init()
			{
				if (Tools::isSubmit('updateoption'))
					$this->display = 'editoption';
				elseif (Tools::isSubmit('submitAddoption'))
					$this->display = 'editoption';
				elseif (Tools::isSubmit('add_variable'))
					$this->display = 'add';
		
				parent::init();
			}
		
		public function processSave()
			{
				if ($this->display == 'add' || $this->display == 'edit')
					$this->identifier = 'id_variable';
		
				if (!$this->id_object)
					return $this->processAdd();
				else
					return $this->processUpdate();
			}
		
		
		
		public function processAdd()
			{
				$object = parent::processAdd();
				
				if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && !count($this->errors))
				{
					if ($this->display == 'add')
						$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&conf=3&update'.$this->table.'&token='.$this->token;	
					else
						$this->redirect_after = self::$currentIndex.'&id_variable='.(int)Tools::getValue('id_variable').'&conf=3&update'.$this->table.'&token='.$this->token;
				}
				else
				{
					$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&id_variable='.(int)Tools::getValue('id_variable').'&conf=3&viewvariable&token='.$this->token;
				}
		
				if (count($this->errors)) {
					$this->setTypeValues();
					
				}
						
				
				return $object;
			}
		
		public function processUpdate()
			{
				$object = parent::processUpdate();
		
				if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && !count($this->errors))
				{
					if ($this->display == 'add')
						$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&conf=3&update'.$this->table.'&token='.$this->token;
					else
						$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&id_variable='.(int)Tools::getValue('id_variable').'&conf=3&update'.$this->table.'&token='.$this->token;
				}
				else
				{
					$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&id_variable='.(int)Tools::getValue('id_variable').'&conf=3&viewvariable&token='.$this->token;
				}
		
				if (count($this->errors)) {
					$this->setTypeValues();
					
				}
		
				if (Tools::isSubmit('updateoption') || Tools::isSubmit('deleteoption') || Tools::isSubmit('submitAddoption') || Tools::isSubmit('submitBulkdeleteoption'))
		
				return $object;
			}
		
		public function processPosition()
			{
				if (Tools::getIsset('viewvariable'))
				{
					$object = new KDVariable((int)Tools::getValue('id_variable'));
					self::$currentIndex = self::$currentIndex.'&viewvariable';
				}
				else
					$object = new $this->className();
		
				if (!Validate::isLoadedObject($object))
				{
					$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').
						' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
				}
				elseif (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position'))){
					$this->errors[] = Tools::displayError('Failed to update the position.');
				}else
				{
					$id_identifier_str = ($id_identifier = (int)Tools::getValue($this->identifier)) ? '&'.$this->identifier.'='.$id_identifier : '';
					$redirect = self::$currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.$id_identifier_str.'&token='.$this->token;
					$this->redirect_after = $redirect;
				}
				return $object;
			}
			
		public function initContent()
			{
				
				// toolbar (save, cancel, new, ..)
				$this->initTabModuleList();
				$this->initToolbar();
				$this->initPageHeaderToolbar();
				if ($this->display == 'edit' || $this->display == 'add')
				{
					if (!($this->object = $this->loadObject(true)))
						return;
					$this->content .= $this->renderForm();
				}
				elseif ($this->display == 'editoption')
				{
					if (!$this->object = new KDOption((int)Tools::getValue('id_option')))
						return;
		
					$this->content .= $this->renderFormValues();
				}
				elseif ($this->display != 'view' && !$this->ajax)
				{
					$this->content .= $this->renderList();
					$this->content .= $this->renderOptions();
				}
				elseif ($this->display == 'view' && !$this->ajax)
					$this->content = $this->renderView();
		
				$this->context->smarty->assign(array(
					'table' => $this->table,
					'current' => self::$currentIndex,
					'token' => $this->token,
					'content' => $this->content,
					'url_post' => self::$currentIndex.'&token='.$this->token,
					'show_page_header_toolbar' => $this->show_page_header_toolbar,
					'page_header_toolbar_title' => $this->page_header_toolbar_title,
					'page_header_toolbar_btn' => $this->page_header_toolbar_btn
				));
			}
		
		public function initPageHeaderToolbar()
			{
				if (empty($this->display))
				{
					$this->page_header_toolbar_btn['new_variable'] = array(
						'href' => self::$currentIndex.'&addvariable&token='.$this->token,
						'desc' => $this->l('Add new variable', null, null, false),
						'icon' => 'process-icon-new'
					);
					$this->page_header_toolbar_btn['new_value'] = array(
						'href' => self::$currentIndex.'&updateoption&id_variable='.(int)Tools::getValue('id_variable').'&token='.$this->token,
						'desc' => $this->l('Add new option', null, null, false),
						'icon' => 'process-icon-new'
					);
				}
		
				if ($this->display == 'view')
					$this->page_header_toolbar_btn['new_value'] = array(
						'href' => self::$currentIndex.'&updateoption&id_variable='.(int)Tools::getValue('id_variable').'&token='.$this->token,
						'desc' => $this->l('Add new option', null, null, false),
						'icon' => 'process-icon-new'
					);
		
				parent::initPageHeaderToolbar();
			}
			
		public function initToolbar()
			{
				switch ($this->display)
				{
					// @todo defining default buttons
					case 'add':
					case 'edit':
					case 'editoption':
						// Default save button - action dynamically handled in javascript
						$this->toolbar_btn['save'] = array(
							'href' => '#',
							'desc' => $this->l('Save')
						);
		
						if ($this->display == 'editoption' && !$this->id_option)
							$this->toolbar_btn['save-and-stay'] = array(
								'short' => 'SaveAndStay',
								'href' => '#',
								'desc' => $this->l('Save then add another option', null, null, false),
								'force_desc' => true,
							);
		
						$this->toolbar_btn['back'] = array(
							'href' => self::$currentIndex.'&token='.$this->token,
							'desc' => $this->l('Back to list', null, null, false)
						);
						break;
					case 'view':
						$this->toolbar_btn['newoption'] = array(
								'href' => self::$currentIndex.'&updateoption&id_variable='.(int)Tools::getValue('id_variable').'&token='.$this->token,
								'desc' => $this->l('Add New Variable', null, null, false),
								'class' => 'toolbar-new'
							);
		
						$this->toolbar_btn['back'] = array(
							'href' => self::$currentIndex.'&token='.$this->token,
							'desc' => $this->l('Back to list', null, null, false)
						);
						break;
					default: // list
						$this->toolbar_btn['new'] = array(
							'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
							'desc' => $this->l('Add New Variable', null, null, false)
						);
				}
					
			}
		
		
			
			
		public function initToolbarTitle()
			{
				$bread_extended = $this->breadcrumbs;
		
				switch ($this->display)
				{
					case 'edit':
						$bread_extended[] = $this->l('Edit Option');
						break;
		
					case 'add':
						$bread_extended[] = $this->l('Add New Option');
						break;
		
					case 'view':
						if (Tools::getIsset('viewvariable'))
						{
							if (($id = Tools::getValue('id_variable')))
								if (Validate::isLoadedObject($obj = new KDVariable((int)$id)))
									$bread_extended[] = $obj->name[$this->context->employee->id_lang];
						}
						else
							$bread_extended[] = $this->value[$this->context->employee->id_lang];
						break;
		
					case 'editoption':
						if ($this->id_option)
						{
							if (($id = Tools::getValue('id_variable')))
							{
								if (Validate::isLoadedObject($obj = new KDVariable((int)$id)))
									$bread_extended[] = '<a href="'.Context::getContext()->link->getAdminLink('AdminNdkCustomFields').'&id_variable='.$id.'&viewvariable">'.$obj->name[$this->context->employee->id_lang].'</a>';
								if (Validate::isLoadedObject($obj = new KDOption((int)$this->id_option)))
									$bread_extended[] =  sprintf($this->l('Edit: %s'), $obj->label[$this->context->employee->id_lang]);
							}
							else
								$bread_extended[] = $this->l('Edit Option');
						}
						else
							$bread_extended[] = $this->l('Add New Option');
						break;
				}
		
				if (count($bread_extended) > 0)
					$this->addMetaTitle($bread_extended[count($bread_extended) - 1]);
		
				$this->toolbar_title = $bread_extended;
			}
		
		
		public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
			{
				parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
		
				
					$nb_items = count($this->_list);
					for ($i = 0; $i < $nb_items; $i++)
					{
						$item = &$this->_list[$i];
		
						$query = new DbQuery();
						$query->select('COUNT(a.id_option) as count_values');
						$query->from('option', 'a');
						$query->where('a.id_variable ='.(int)$item['id_variable']);
						$query->orderBy('count_values DESC');
						$item['count_values'] = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
						unset($query);
					}
				
			}
		
		
		public function processBulkDelete()
			{
				if (Tools::getIsset('valueBox'))
				{
					$this->className = 'KDOption';
					$this->table = 'option';
					$this->boxes = Tools::getValue($this->table.'Box');
				}
		
				$result = parent::processBulkDelete();
				// Restore vars
				$this->className = 'KDVariable';
				$this->table = 'variable';
				
		
				return $result;
			}
		
		
		public function renderFormValues()
			{
				$variables = KDVariable::getAllVariables();
				// Override var of Controller
				$this->table = 'option';
				$this->className = 'KDOption';
				$this->lang = true;
				
				$additionnals = '';
				$this->show_form_cancel_button = true;
				$this->fields_form = array(
					'legend' => array(
						'title' => $this->l('Options'),
						'icon' => 'icon-info-sign'
					),
					'input' => array(
						array(
							'type' => 'select',
							'label' => $this->l('Variable'),
							'name' => 'id_variable',
							'required' => true,
							'options' => array(
								'query' => $variables,
								'id' => 'id_variable',
								'name' => 'name'
							),
							'hint' => $this->l('Choose the parent variable for this option.')
						),
						
					)
				);
				
				
				$obj = $this->loadObject(true);
				$parent = new KDVariable((int)$obj->id_variable);
				
				
				$this->fields_form['input'][] = array(
					'type' => 'text',
					'label' => $this->l('Name'),
					'name' => 'label',
					'size' => 48,
					'lang' => true,
				);


				$this->fields_form['input'][] = array(
					'type' => 'text',
					'label' => $this->l('Price (€)'),
					'name' => 'price',
					'size' => 48,
				);
				
				$this->fields_form['input'][] = array(
					'type' => 'text',
					'label' => $this->l('Weight (g)'),
					'name' => 'weight',
					'size' => 48,
				);

				$this->fields_form['input'][] = array(
					'type' => 'text',
					'label' => $this->l('Thickness (μm)'),
					'name' => 'thickness',
					'size' => 48,
				);

				$this->fields_form['input'][] = array(
					'type' => 'switch',
					'label' => $this->l('Global Disable'),
					'name' => 'active',
					'required' => false,
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					)
				);
				
				
					
				$this->fields_form['submit'] = array(
					'title' => $this->l('Save'),
				);
		
				$this->fields_form['buttons'] = array(
					'save-and-stay' => array(
						'title' => $this->l('Save then add another step'),
						'name' => 'submitAdd'.$this->table.'AndStay',
						'type' => 'submit',
						'class' => 'btn btn-default pull-right',
						'icon' => 'process-icon-save'
					)
				);
		
				$this->fields_value['id_variable'] = (int)Tools::getValue('id_variable');
		
				// Override var of Controller
				$this->table = 'option';
				$this->className = 'KDOption';
				$this->identifier = 'id_option';
				$this->lang = true;
		
				// Create object Field
				if (!$obj = new KDOption((int)Tools::getValue($this->identifier)))
					return;
				
				$parent = new KDVariable((int)Tools::getValue('id_variable'));
				
				return parent::renderForm();
			}
		
		public function renderView()
			{
				if (($id = Tools::getValue('id_variable')))
				{
					$this->table      = 'option';
					$this->className  = 'KDOption';
					$this->identifier = 'id_option';
					$this->position_identifier = 'id_option';
					$this->position_group_identifier = 'id_variable';
					$this->list_id    = 'id_option';
					$this->lang       = true;
		
					$this->_defaultOrderBy = 'position';
		
					$this->context->smarty->assign(array(
						'current' => self::$currentIndex.'&id_variable='.(int)$id.'&viewvariable'
					));
		
					if (!Validate::isLoadedObject($obj = new KDVariable((int)$id)))
					{
						$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
						return;
					}
		
					$this->name = $obj->name;
					$this->fields_list = array(
						'id_option' => array(
							'title' => $this->l('ID'),
							'align' => 'center',
							'class' => 'fixed-width-xs'
						),
						'label' => array(
							'title' => $this->l('Name'),
							'width' => 'auto',
							'filter_key' => 'a!name',
							'lang' => true
						),
						'position' => [
							'title' => $this->trans('Position', [], 'Admin.Global'),
							'filter_key' => 'a!position',
							'position' => 'position',
							'align' => 'center',
							'class' => 'fixed-width-xs',
						],
						'price' => array(
							'title' => $this->l('Price'),
							'align' => 'center',
							'class' => 'fixed-width-xs'
						),
						'weight' => array(
							'title' => $this->l('Weight (g)'),
							'align' => 'center',
							'class' => 'fixed-width-xs'
						),
						'thickness' => array(
							'title' => $this->l('Thickness (μm)'),
							'align' => 'center',
							'class' => 'fixed-width-xs'
						),
						'active' => array(
							'title' => $this->l('Active'),
							'active' => 'active',
							'type' => 'bool',
							'class' => 'fixed-width-xs',
							'align' => 'center',
							'orderby' => false
						),
					);
					
					
					
		
					$this->addRowAction('edit');
					$this->addRowAction('delete');
		
					$this->_where = 'AND a.`id_variable` = '.(int)$id;
					$this->_orderBy = 'position';
		
					self::$currentIndex = self::$currentIndex.'&id_variable='.(int)$id.'&viewvariable';
					//$this->processFilter();
					return parent::renderList();
				}
			}
		
		protected function afterUpdate2($object)
		{
			if ($this->table == 'option')
					{
						$parent = new KDVariable($object->id_variable);
						if($parent->type == 1)
							$this->setCartRule($object, $parent);
							
							
					}
					
			if ($this->table == 'variable') {
				$languages = Language::getLanguages(false);
				$cart_rule = new CartRule($object->id_cart_rule);
				$cart_rule->reduction_amount = $object->reduction_amount;
				$cart_rule->reduction_percent = $object->reduction_percent;
				$cart_rule->product_restriction = 1;
				$cart_rule->date_from = date("Y-m-d H:i:s");
				$cart_rule->date_to = '2020-01-01 10:10:10';
				$cart_rule->date_add = date("Y-m-d H:i:s");
				$cart_rule->date_upd = date("Y-m-d H:i:s");
				foreach ($languages as $lang)
				{
					$cart_rule->name[$lang['id_lang']] = $object->name[$lang['id_lang']];
				}
				$cart_rule->update();
				if($object->type == 1)
					$object->createProductPack($object);
			}
			
			
			return parent::afterUpdate($object);
		}
		
		protected function afterAdd2($object)
		{
			if ($this->table == 'option')
					{
						$parent = new KDVariable($object->id_variable);
						if($parent->type == 0)
							$this->setCartRule($object, $parent);
					}
			if ($this->table == 'variable') {
				$languages = Language::getLanguages(false);
				
				$cart_rule = new CartRule();
				$cart_rule->reduction_amount = $object->reduction_amount;
				$cart_rule->reduction_percent = $object->reduction_percent;
				$cart_rule->product_restriction = 1;
				$cart_rule->date_from = date("Y-m-d H:i:s");
				$cart_rule->date_to = '2020-01-01 10:10:10';
				$cart_rule->date_add = date("Y-m-d H:i:s");
				$cart_rule->date_upd = date("Y-m-d H:i:s");
				foreach ($languages as $lang)
				{
					$cart_rule->name[$lang['id_lang']] = Tools::getValue('name['.$lang['id_lang'].']');
				}
				$cart_rule->save();
				$object->id_cart_rule = $cart_rule->id;
				$object->update();
				if($object->type == 1)
					$object->createProductPack($object);
				
			}
			return parent::afterAdd($object);
		}
		
		
		protected function afterImageUpload2()
		{
			/* Generate image with differents size */
			if (!($obj = $this->loadObject(true)))
				return;
	
			if ($obj->id && (isset($_FILES['image'])))
			{
				$base_img_path = _PS_IMG_DIR_.'scenes/'.'ndksp/'.$obj->id.'.jpg';
				//ImageManager::resize($base_img_path, _PS_IMG_DIR_.'scenes/'.'ndkcf/thumbs/'.$obj->id.'.jpg', 458, 458);
				
				$images_types = ImageType::getImagesTypes('products');
				
				foreach ($images_types as $k => $image_type)
				{
					ImageManager::resize(
						$base_img_path,
						_PS_IMG_DIR_.'scenes/'.'ndksp/thumbs/'.$obj->id.'-'.Tools::stripslashes($image_type['name']).'.jpg',
						(int)$image_type['width'],
						(int)$image_type['height']);
				
				}
			}
	
			return true;
		}
	
	public function renderForm()
		{
			$this->table = 'variable';
			$this->identifier = 'id_variable';
			$link = new Link();
			
			$types = array(
				array('id_type'=>1, 'name' =>$this->l('Quantity input')),
				array('id_type'=>2, 'name' =>$this->l('Dropdown list')),
				array('id_type'=>3, 'name' =>$this->l('Fixed value')),
				array('id_type'=>4, 'name' =>$this->l('Custom input')),
				array('id_type'=>5, 'name' =>$this->l('Custom Text input')),
				array('id_type'=>6, 'name' =>$this->l('Spine thickness formula')),
				array('id_type'=>7, 'name' => $this->l('Shipping type')),
			);
			if (!$obj = new KDVariable((int)Tools::getValue($this->identifier)))
				return;
			
			
			$this->fields_form = array(
				'legend' => array(
					'title' => $this->l('Variable'),
					),
				'submit' => array(
					'title' => $this->l('Save'),
				),
				'input' => array(
					
					array(
						'type' => 'text',
						'label' => $this->l('Public Name'),
						'name' => 'label',
						'lang' => true,
						'size' => 48,
						'required' => false,
						'hint' => $this->l('Invalid characters:').' <>;=#{}',
					),

					array(
						'type' => 'text',
						'label' => $this->l('Name'),
						'name' => 'name',
						'lang' => false,
						'size' => 48,
						'required' => true,
						'hint' => $this->l('Invalid characters:').' <>;=#{}',
					),
					
					array(
					'type' => 'select',
					'label' => $this->l('Type'),
					'name' => 'type',
					'hint' => $this->l('Select the type of option.'),
					'required' => true,
					'options' => array(
						'query' => $types,
						'id' => 'id_type',
						'name' => 'name'
						),
					
					),
					
					array(
							'type' => 'switch',
							'label' => $this->l('Active'),
							'name' => 'active',
							'required' => false,
							'is_bool' => true,
							'values' => array(
								array(
									'id' => 'active_on',
									'value' => 1,
									'label' => $this->l('Yes')
								),
								array(
									'id' => 'active_off',
									'value' => 0,
									'label' => $this->l('No')
								)
							)
						),
					
					array(
						'type' => 'text',
						'label' => $this->l('Fixed value'),
						'name' => 'fixed_price',
						'size' => 3,
						'hint' => $this->l('Invalid characters:').' <>;=#{}',
						
					),
				),
			);
			
			
	
			if (Shop::isFeatureActive())
					{
						// We get all associated shops for all variable groups, because we will disable group shops
						// for variables that the selected variable group don't support
						$sql = 'SELECT id_variable, id_shop FROM '._DB_PREFIX_.'variable';
						$associations = array();
						foreach (Db::getInstance()->executeS($sql) as $row)
							$associations[$row['id_variable']][] = $row['id_shop'];
			
						$this->fields_form['input'][] = array(
							'type' => 'shop',
							'label' => $this->l('Shop association'),
							'name' => 'checkBoxShopAsso',
							'values' => Shop::getTree()
						);
					}
					else
						$associations = array();
			
			$this->fields_form['shop_associations'] = Tools::jsonEncode($associations);


			
			
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Minimum'),
				'name' => 'minimum',
				'size' => 48,
			);
			
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Maximum'),
				'name' => 'maximum',
				'size' => 48,
			);

			// $this->fields_form['input'][] = array(
			// 	'type' => 'text',
			// 	'label' => $this->l('Height'),
			// 	'name' => 'height',
			// 	'size' => 48,
			// );
			
			// $this->fields_form['input'][] = array(
			// 	'type' => 'text',
			// 	'label' => $this->l('Weight'),
			// 	'name' => 'weight',
			// 	'size' => 48,
			// );
	
			
	
			$this->fields_form['submit'] = array(
				'title' => $this->l('Save'),
			);
	
			if (!($obj = $this->loadObject(true)))
				return;
								
			return parent::renderForm();
		}
		
		
		public function postProcess()
			{
				
				$selected_cat = array();
				   
				if (!Tools::getValue($this->identifier) && Tools::getValue('id_option') && !Tools::getValue('optionOrderby'))
				{
					// Override var of Controller
					$this->table = 'option';
					$this->className = 'KDOption';
					$this->identifier = 'id_option';
				}
		
				// If it's an variable, load object Attribute()
				if (Tools::getValue('updateoption') || Tools::getValue('deleteoption') || Tools::getValue('submitAddoption'))
				{
					/*if ($this->tabAccess['edit'] !== '1')
						$this->errors[] = Tools::displayError('You do not have permission to edit this.');
					elseif (!$object = new KDOption((int)Tools::getValue($this->identifier)))
						$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');*/
						
					
					if (Tools::getValue('deletoption') && Tools::getValue('id_option'))
					{
						if (!$object->delete())
							$this->errors[] = Tools::displayError('Failed to delete the option.');
						else
							Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.Tools::getAdminTokenLite('AdminNdkCustomFields'));
					}
					elseif (Tools::isSubmit('submitAddoption'))
					{
						$this->action = 'save';
						$id_option = (int)Tools::getValue('id_option');
						// Adding last position to the variable if not exist
						if ($id_option <= 0) {
							$sql = 'SELECT `position`+1
									FROM `' . _DB_PREFIX_ . 'option`
									WHERE `id_variable` = ' . (int) Tools::getValue('id_variable') . '
									ORDER BY position DESC';
							// set the position of the new group variable in $_POST for postProcess() method
							$_POST['position'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
						}
						$_POST['id_parent'] = 0;

						$this->processSave($this->token);
					}
		
				}
				else
				{
					if(Tools::getValue('way'))
						$_POST['id_variable'] = Tools::getValue('id');
					if (Tools::getValue('submitDel'.$this->table))
					{
						if ($this->tabAccess['delete'] === '1')
						{
							if (Tools::getValue($this->table.'Box'))
							{
								$object = new $this->className();
								if ($object->deleteSelection(Tools::getValue($this->table.'Box')))
									Tools::redirectAdmin(self::$currentIndex.'&conf=2'.'&token='.$this->token);
								$this->errors[] = Tools::displayError('An error occurred while deleting this selection.');
							}
							else
								$this->errors[] = Tools::displayError('You must select at least one element to delete.');
						}
						else
							$this->errors[] = Tools::displayError('You do not have permission to delete this.');
						// clean position after delete
					}
					elseif (Tools::isSubmit('submitAdd'.$this->table))
					{
						$id_variable = (int)Tools::getValue('id_variable');
						// Adding last position to the variable if not exist
						if ($id_variable <= 0)
						{
							$sql = 'SELECT `position`+1
									FROM `'._DB_PREFIX_.'variable`
									ORDER BY position DESC';
							// set the position of the new group variable in $_POST for postProcess() method
							$_POST['position'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
						}
						//$_POST['id_parent'] = 0;
						$this->processSave($this->token);
						
						// clean \n\r characters
						foreach ($_POST as $key => $value)
							if (preg_match('/^name_/Ui', $key))
								$_POST[$key] = str_replace ('\n', '', str_replace('\r', '', $value));
						//parent::postProcess();
					}
					elseif (Tools::getIsset('active'.$this->table))
					{
						
						$id_variable = (int)Tools::getValue('id_variable');
						// Adding last position to the variable if not exist
						if ($id_variable > 0)
						{
							//$sql = 'UPDATE '._DB_PREFIX_.$this->table . ' SET active = 1 WHERE active = 0 AND id_variable = '.$id_variable;
							
							Db::getInstance()->execute('
							UPDATE '._DB_PREFIX_.$this->table . ' 
							SET active = case
							   when active = 0 then 1
							   else 0
							   end
							WHERE id_variable = '.$id_variable);	
						}
					}
					elseif (Tools::getIsset('submitBulkdisableSelection'.$this->table))
					{
						foreach(Tools::getValue($this->table.'Box') as $key=>$id_variable)
						{
							if ($id_variable > 0)
							{
								//$sql = 'UPDATE '._DB_PREFIX_.$this->table . ' SET active = 1 WHERE active = 0 AND id_variable = '.$id_variable;
								
								Db::getInstance()->execute('
								UPDATE '._DB_PREFIX_.$this->table . ' 
								SET active = 0 WHERE id_variable = '.$id_variable);	
							}
						}
					}
					elseif (Tools::getIsset('submitBulkenableSelection'.$this->table))
					{
						foreach(Tools::getValue($this->table.'Box') as $key=>$id_variable)
						{
							if ($id_variable > 0)
							{
								//$sql = 'UPDATE '._DB_PREFIX_.$this->table . ' SET active = 1 WHERE active = 0 AND id_variable = '.$id_variable;
								
								Db::getInstance()->execute('
								UPDATE '._DB_PREFIX_.$this->table . ' 
								SET active = 1 WHERE id_variable = '.$id_variable);	
							}
						}
					}
					else
						parent::postProcess();
				}
			}
			
		// public function ajaxProcessUpdatePositions()
		public function ajaxProcessUpdateVariablePositions()
		{
			$way = (int)Tools::getValue('way');
			$id_variable = (int)Tools::getValue('id_variable');
			$positions = Tools::getValue('variable');
			
			$new_positions = array();
			
			foreach ($positions as $v)
				if (count(explode('_', $v)) == 4)
					$new_positions[] = $v;
	
			//print_r($new_positions);
			
			foreach ($new_positions as $position => $value)
			{
				$pos = explode('_', $value);
				
				if (isset($pos[2]) && (int)$pos[2] == $id_variable)
				{
					
					// echo $id_variable;
					if ($variable = new KDVariable((int)$pos[2])){
						if ($variable->updatePosition($way, $position)){
							echo 'ok position '.(int)$position.' for field group '.(int)$pos[2].'\r\n';
						}else{
							echo '{"hasError" : true, "errors" : "Can not update the '.(int)$variable.' field group to position '.(int)$position.' "}';
						}
					}else{
						echo '{"hasError" : true, "errors" : "The ('.(int)$id_variable.') field group cannot be loaded."}';
					}
					break;
				}
			}
		}
		public function ajaxProcessUpdateOptionsPositions()
		{
			$way = (int) Tools::getValue('way');
			$id_option = (int) Tools::getValue('id_option');
			$id_variable = (int)Tools::getValue('id_variable');
			$positions = Tools::getValue('option');
	
			if (is_array($positions)) {
				foreach ($positions as $position => $value) {
					$pos = explode('_', $value);
	
					if ((isset($pos[1], $pos[2])) && (int) $pos[2] === $id_option) {
						if ($option = new KDOption((int) $pos[2])) {
							if (isset($position) && $option->updatePosition($way, $position)) {
								echo 'ok position ' . (int) $position . ' for option ' . (int) $pos[2] . '\r\n';
							} else {
								echo '{"hasError" : true, "errors" : "Can not update the ' . (int) $id_option . ' variable to position ' . (int) $position . ' "}';
							}
						} else {
							echo '{"hasError" : true, "errors" : "The (' . (int) $id_option . ') variable cannot be loaded"}';
						}
						
						break;
					}
				}
			}
		}
			
		public function ajaxProcessUpdatePositions(){
			$way = (int)$_POST['way'];
			if(isset($_POST['option'])){
				$positions = $_POST['option'];
				$id_option = $_POST['id'];
				$new_positions = array();
				foreach ($positions as $v){
					if (count(explode('_', $v)) == 4){
						$new_positions[] = $v;
					}
				}
				
				if (is_array($new_positions)) {
					foreach ($new_positions as $position => $value) {
						$pos = explode('_', $value);
		
						if ((isset($pos[2], $pos[2])) && (int) $pos[2] == (int) $id_option) {
							if ($option = new KDOption((int) $pos[2])) {
								
								if (isset($position) && $option->updatePosition($way, $position)) {
									echo 'ok position ' . (int) $position . ' for option ' . (int) $pos[2] . '\r\n';
								} else {
									echo '{"hasError" : true, "errors" : "Can not update the ' . (int) $id_option . ' variable to position ' . (int) $position . ' "}';
								}
							} else {
								echo '{"hasError" : true, "errors" : "The (' . (int) $id_option . ') variable cannot be loaded"}';
							}
		
							break;
						}
					}
				}
			}elseif(isset($_POST['variable'])){
				$positions = $_POST['variable'];
				$new_positions = array();
				$id_variable = $_POST['id'];
				foreach ($positions as $v){
					if (count(explode('_', $v)) == 4){
						$new_positions[] = $v;
					}
				}
				
				foreach ($new_positions as $position => $value){
					$pos = explode('_', $value);
					if (isset($pos[2]) && (int)$pos[2] == $id_variable){
						if ($variable = new KDVariable((int)$pos[2])){
							if ($variable->updatePosition($way, $position)){
								echo 'ok position '.(int)$position.' for field group '.(int)$pos[2].'\r\n';
							}else{
								echo '{"hasError" : true, "errors" : "Can not update the '.(int)$variable.' field group to position '.(int)$position.' "}';
							}
						}else{
							echo '{"hasError" : true, "errors" : "The ('.(int)$id_variable.') field group cannot be loaded."}';
						}
						break;
					}
				}
			}
		}
			
		public function initProcess()
			{
				$this->setTypeValues();
		
				if (Tools::getIsset('viewvariable'))
				{
					$this->list_id = 'option';
		
					/*if (Tools::getIsset($_POST['submitReset'.$this->list_id]))
						$this->processResetFilters();*/
				}
				else
					$this->list_id = 'variable';
		
				parent::initProcess();
		
				if ($this->table == 'option')
				{
					$this->display = 'editoption';
					$this->id_option = (int)Tools::getValue('id_option');
				}
			}
			
		protected function setTypeValues()
			{
				if (Tools::isSubmit('updateoption') || Tools::isSubmit('deleteoption') || Tools::isSubmit('submitAddoption') || Tools::isSubmit('submitBulkdeletevalue'))
				{
					$this->table = 'option';
					$this->className = 'KDOption';
					$this->identifier = 'id_option';
		
					
				}
			}
		
		public function addMetaTitle($entry)
		{
			// Only add entry if the meta title was not forced.
			if (is_array($this->meta_title))
				$this->meta_title[] = $entry;
		}
		
				
		public function processDelete2()
		{
			$object = parent::processDelete();
			
		}
		
	
}