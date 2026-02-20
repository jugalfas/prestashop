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


class ProductPriceConfigAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax         = true;
    public $content_only = true;

    public function postProcess2()
    {
        if (!$this->context->cookie->exists()) {
            exit;
        }

        $product = new Product((int)Tools::getValue('id_product'), true, $this->context->language->id);
        if (false === Validate::isLoadedObject($product)) {
            exit(json_encode('0.0'));
        }

        $qty = (int)Tools::getValue('qty');

        $price = $product->getPrice(
            false,
            (int)Tools::getValue('attribute_id'),
            6,
            null,
            false,
            true,
            $qty
        );

        $roundType = (int)Configuration::get('PS_ROUND_TYPE');
        $precision = (int)(
            Configuration::hasKey('PS_PRICE_DISPLAY_PRECISION')
            ? Configuration::get('PS_PRICE_DISPLAY_PRECISION')
            : _PS_PRICE_DISPLAY_PRECISION_
        );
        if (1 === $roundType) {
            $price = Tools::ps_round($price, $precision) * $qty;
        } else {
            $price = $price * $qty;
        }

        $price = Tools::ps_round($price, $precision);

        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            $locale          = $this->context->getCurrentLocale();

            $numberingSystem = $locale->getNumberSpecification()->getAllSymbols()['latn'];
            $decimal         = $numberingSystem->getDecimal();
            $group           = $numberingSystem->getGroup();
            $priceFormat     = $locale->getPriceSpecification($this->context->currency->iso_code)->getPositivePattern();
        } elseif (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $cldr            = $this->context->currency->cldr;
            $locale          = $cldr->getRepository()->locales[$cldr->getCulture()];
            $numberingSystem = $locale['numbers']['symbols-numberSystem-latn'];
            $decimal         = $numberingSystem['decimal'];
            $group           = $numberingSystem['group'];
            $priceFormat     = $this->context->currency->format;
        } else {
            $priceFormat  = (int)$this->context->currency->format;
            switch ($priceFormat) {
                case 2:
                    $decimal   = ',';
                    $group     = ' ';
                    $signFirst = false;
                    break;
                case 3:
                    $decimal   = ',';
                    $group     = '.';
                    $signFirst = true;
                    break;
                case 5:
                    $decimal   = '.';
                    $group     = "'";
                    $signFirst = true;
                    break;
                case 1:
                    $decimal   = '.';
                    $group     = ',';
                    $signFirst = true;
                    break;
                case 4:
                default:
                    $decimal   = '.';
                    $group     = ',';
                    $signFirst = false;
            }
        }

        if (Validate::isInt($priceFormat)) {
            $signBlank = (bool)$this->context->currency->blank;
        } else {
            $signPosition = Tools::strpos($priceFormat, '¤');
            $signFirst    = (0 === $signPosition);

            if ($signFirst) {
                $signBlank = (194 === ord(Tools::substr($priceFormat, $signPosition + 1, 1)));
            } else {
                $signBlank = (194 === ord(Tools::substr($priceFormat, $signPosition - 1, 1)));
            }
        }

        exit(json_encode(array(
            0 => Tools::displayPrice($price, $this->context->currency),
            1 => $price,
            2 => $decimal,
            3 => $group,
            4 => $this->context->currency->sign,
            5 => $signFirst,
            6 => $signBlank,
        )));
    }


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
        } elseif(Tools::getValue('action') == 'get_unit_price' && $params_string = Tools::getValue('params')){
            $params = array();
            parse_str($params_string, $params);
            $this->module->ajaxGetUnitPrice($params);
        }
       
    }
}
