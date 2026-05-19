<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class CartRule extends CartRuleCore
{
    public function checkValidity(
        Context $context,
        $alreadyInCart = false,
        $displayError = true,
        $checkCartRuleRestrictions = true,
        $alreadyInCartQuantity = 0,
        $checkCarrier = true
    ) {
        $enabled = (int) Db::getInstance()->getValue(
            'SELECT `new_customer_only` FROM `' . _DB_PREFIX_ . 'onlyprint_cart_rule` WHERE `id_cart_rule` = ' . (int) $this->id
        );
        if (!$enabled) {
            return parent::checkValidity($context, $alreadyInCart, $displayError, $checkCartRuleRestrictions, $alreadyInCartQuantity, $checkCarrier);
        }
$cust_id = empty($context->customer) ? 0 : (int) $context->customer->id;
$lang_iso = Context::getContext()->language->iso_code; // 'de', 'nl', 'en'
if (!$cust_id) {
    if ($lang_iso == 'de') {
        return 'Dieser Gutschein ist nur für registrierte Neukunden gültig. Bitte einloggen oder Account erstellen.';
    } elseif ($lang_iso == 'nl') {
        return 'Deze kortingscode is alleen geldig voor geregistreerde nieuwe klanten. Log in of maak een account aan.';
    } else {
        return 'This voucher is only for registered new customers. Please log in or create an account.';
    }
}
$ordersCount = (int) Order::getCustomerNbOrders($cust_id);
if ($ordersCount > 0) {
    if ($lang_iso == 'de') {
        return 'Dieser Gutschein ist nur für Neukunden ohne bisherige Bestellungen gültig. Sie haben bereits ' . $ordersCount . ' Bestellung(en).';
    } elseif ($lang_iso == 'nl') {
        return 'Deze kortingscode is alleen geldig voor nieuwe klanten zonder eerdere bestellingen. U heeft al ' . $ordersCount . ' bestelling(en).';
    } else {
        return 'This voucher is only for new customers without previous orders. You already have ' . $ordersCount . ' order(s).';
    }
}
        return parent::checkValidity($context, $alreadyInCart, $displayError, $checkCartRuleRestrictions, $alreadyInCartQuantity, $checkCarrier);
    }
    
    /*
    * module: onlyprint_discounts
    * date: 2026-04-13 14:05:27
    * version: 2.0.0
    */
    public function getContextualValue($use_tax, Context $context = null, $filter = null, $package = null, $use_cache = true)
    {
        $value = parent::getContextualValue($use_tax, $context, $filter, $package, $use_cache);
        if ($this->reduction_percent > 0 && $value > 0) {
            $maxAmount = $this->getOnlyprintMaxDiscountAmount();
            if ($maxAmount > 0 && $value > $maxAmount) {
                $value = $maxAmount;
            }
        }
        return $value;
    }
    
    /*
    * module: onlyprint_discounts
    * date: 2026-04-13 14:05:27
    * version: 2.0.0
    */
    private function getOnlyprintMaxDiscountAmount(): float
    {
        $classFile = _PS_MODULE_DIR_ . 'onlyprint_discounts/classes/DiscountRule.php';
        if (file_exists($classFile)) {
            require_once $classFile;
            return OnlyprintDiscountRule::getMaxDiscountAmount((int) $this->id);
        }
        return 0.0;
    }
}
