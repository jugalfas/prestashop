<?php
/**
 * TableCombz: module for PrestaShop 1.6-1.7
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2010-2020 Maksim T.
 * @license   https://addons.prestashop.com/en/content/12-terms-and-conditions-of-use Terms and conditions of use (EULA)
 * @link      https://prestashop.modulez.ru/en/ Modules for PrestaShop CMS
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class QuoteRequestAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax         = true;
    public $content_only = true;

    
    public function initContent()
    {
        if (Tools::getValue('action') == 'calculate' and $params_string = Tools::getValue('params')) {
            $params = array();
            parse_str($params_string, $params);
            $this->module->ajaxGetFilteredVariables($params);
        } elseif (Tools::getValue('action') == 'checkBanedComb') {
            $this->module->ajaxCalcuateTotals();
        } elseif (Tools::getValue('action') == 'addtocart' and $params_string = Tools::getValue('params') ) {
            $params = array();
            parse_str($params_string, $params);
            $this->module->ajaxAddToCart($params);
        }
       
    }
}
