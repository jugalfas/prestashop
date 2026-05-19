<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminManufacturersController extends AdminManufacturersControllerCore
{
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function __construct()
    {
        parent::__construct();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('esm'));
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function initListManufacturer()
    {
        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->_select = '
			COUNT(`id_product`) AS `products`, (
				SELECT COUNT(ad.`id_manufacturer`) as `addresses`
				FROM `' . _DB_PREFIX_ . 'address` ad
				WHERE ad.`id_manufacturer` = a.`id_manufacturer`
					AND ad.`deleted` = 0
				GROUP BY ad.`id_manufacturer`) as `addresses`';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (a.`id_manufacturer` = p.`id_manufacturer`)';
        $this->_group = 'GROUP BY a.`id_manufacturer`';
        $this->_select .= ', esm.key_phrase,
            esm.seo_score,
            esm.readability_score';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'ets_seo_manufacturer` esm ON (a.`id_manufacturer` = esm.`id_manufacturer` 
                                                AND esm.`id_shop` = ' . (int) $this->context->shop->id . ' 
                                                AND esm.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->context->smarty->assign('title_list', $this->trans('List of brands', [], 'Admin.Catalog.Feature'));
        $this->content .= AdminController::renderList();
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function processFilter()
    {
        parent::processFilter();
        $ets_seo = Module::getInstanceByName('ets_seo');
        $ets_seo->actionAdminManufacturerChangeFilter($this->_filter, 'esm');
    }
}
