<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminCmsCategoriesController extends AdminCmsCategoriesControllerCore
{
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function __construct()
    {
        parent::__construct();
        $this->_select .= ', esc.key_phrase,
            esc.seo_score,
            esc.readability_score';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'ets_seo_cms_category` esc ON (a.`id_cms_category` = esc.`id_cms_category` 
                                                AND esc.`id_shop` = ' . (int) $this->context->shop->id . ' 
                                                AND esc.`id_lang` = ' . (int) $this->context->language->id . ')';
        $ets_seo = Module::getInstanceByName('ets_seo');
        $this->fields_list = array_merge($this->fields_list, $ets_seo->get_fields_list_page('esc'));
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
        $ets_seo->actionAdminCmsCategoryChangeFilter($this->_filter, 'esc');
    }
}
