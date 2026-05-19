<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminSuppliersController extends AdminSuppliersControllerCore
{
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function __construct()
    {
        parent::__construct();
        $this->_select .= ', ess.key_phrase,
            ess.seo_score,
            ess.readability_score';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'ets_seo_supplier` ess ON (a.`id_supplier` = ess.`id_supplier` 
                                                AND ess.`id_shop` = ' . (int) $this->context->shop->id . ' 
                                                AND ess.`id_lang` = ' . (int) $this->context->language->id . ')';
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('ess'));
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
        $ets_seo->actionAdminSupplierChangeFilter($this->_filter, 'ess');
    }
}
