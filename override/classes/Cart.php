<?php
/**
 * ============================================================================
 *  PrestaShop Cart override — merged file
 * ============================================================================
 *
 *  IMPORTANT — READ BEFORE EDITING OR RE-DEPLOYING
 *  -----------------------------------------------------------------
 *  This file is the MERGED override from several modules. PrestaShop's
 *  native module-install machinery normally merges per-method into this
 *  file; do NOT overwrite the whole file with a single module's override
 *  again, or you will un-merge everyone else's methods and the
 *  frontend will 500 on add-to-cart (UndefinedMethodException).
 *
 *  Contents by provenance:
 *    [A] onlyprint_special_prices  -> getProducts(), onlyprintResolveStatus()
 *    [B] legacy customization set  -> saveCustomization() and friends,
 *                                     required by module productpriceconfig
 *                                     (ajaxAddToCart calls $cart->saveCustomization
 *                                     at modules/productpriceconfig/productpriceconfig.php:2831)
 *    [C] pshowadvancedstock        -> ids_carriers field, getTotalShippingCost
 *                                     ** NOT INCLUDED HERE **
 *                                     re-add only after verifying that the
 *                                     DB column `ids_carriers` exists in
 *                                     ps_cart and pshowadvancedstock is
 *                                     installed on this environment.
 *
 *  Deployment policy:
 *    - Do not rsync/scp a module's override/classes/Cart.php on top of
 *      this file. Always install modules via Module::install() so the
 *      merge happens per-method. If you must edit this file by hand, do
 *      an additive edit only.
 *
 *  Last merge: 2026-04-23 — reason: restore saveCustomization() which
 *  had been wiped by a full-file deploy of onlyprint_special_prices'
 *  override.
 * ============================================================================
 */

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class Cart extends CartCore
{
    // ------------------------------------------------------------------------
    // [A] From module: onlyprint_special_prices
    // Applies per-customer surcharges into the unit/total prices returned
    // by Cart::getProducts(). See original module doc block for details.
    // ------------------------------------------------------------------------

    /** @var int|null 1 = active, 0 = inactive, null = not yet checked */
    protected static $onlyprintStatus = null;

    /** @var bool  Re-entrancy guard */
    protected static $onlyprintApplying = false;

    public function getProducts($refresh = false, $id_product = false, $id_country = null, $fullInfos = true, $keepOrderPrices = false)
    {
        $products = parent::getProducts($refresh, $id_product, $id_country, $fullInfos, $keepOrderPrices);

        if (self::$onlyprintApplying) {
            return $products;
        }
        if (empty($products) || !$this->id_customer) {
            return $products;
        }

        // Only touch the active session cart. Historical carts loaded by
        // the admin customer-view render pass are left alone.
        $contextCart = Context::getContext()->cart;
        if (!$contextCart || (int) $contextCart->id !== (int) $this->id) {
            return $products;
        }

        if (self::$onlyprintStatus === null) {
            self::$onlyprintStatus = self::onlyprintResolveStatus();
        }
        if (self::$onlyprintStatus !== 1) {
            return $products;
        }

        self::$onlyprintApplying = true;
        try {
            $products = OnlyprintSpecialPriceCalculator::apply($products, (int) $this->id_customer);
        } catch (Throwable $e) {
            // Catch Throwable (not just Exception) so that PHP 7+ Error
            // instances (TypeError, DivisionByZeroError, etc.) still land in
            // the finally block below — otherwise the $onlyprintApplying
            // flag would stay stuck at true for the remainder of the request.
            if (class_exists('PrestaShopLogger')) {
                PrestaShopLogger::addLog(
                    'OnlyPrint Special Prices Cart override failed: '.$e->getMessage(),
                    2
                );
            }
        } finally {
            self::$onlyprintApplying = false;
        }

        return $products;
    }

    /**
     * Direct-DB check — avoids Module::isInstalled/isEnabled which each
     * cascade into Configuration::get() calls and trigger the 1.7.8.0
     * ShopConstraint deprecation spam.
     *
     * Returns 1 if the module is installed for the current shop AND active.
     */
    protected static function onlyprintResolveStatus()
    {
        $base = _PS_MODULE_DIR_.'onlyprint_special_prices/classes/';
        if (!is_file($base.'OnlyprintSpecialPriceRule.php')
            || !is_file($base.'OnlyprintSpecialPriceCalculator.php')) {
            return 0;
        }

        try {
            $idShop = (int) Context::getContext()->shop->id;
            $active = Db::getInstance()->getValue('
                SELECT m.`active`
                FROM `'._DB_PREFIX_.'module` m
                INNER JOIN `'._DB_PREFIX_.'module_shop` ms
                    ON ms.`id_module` = m.`id_module`
                WHERE m.`name` = \'onlyprint_special_prices\'
                AND ms.`id_shop` = '.$idShop.'
                LIMIT 1
            ');
            if (!$active) {
                return 0;
            }
        } catch (Throwable $e) {
            return 0;
        }

        if (!class_exists('OnlyprintSpecialPriceRule')) {
            require_once $base.'OnlyprintSpecialPriceRule.php';
        }
        if (!class_exists('OnlyprintSpecialPriceCalculator')) {
            require_once $base.'OnlyprintSpecialPriceCalculator.php';
        }
        if (!class_exists('OnlyprintSpecialPriceCalculator')) {
            return 0;
        }

        return 1;
    }

    // ------------------------------------------------------------------------
    // [B] Legacy customization suite — required by:
    //       modules/productpriceconfig/productpriceconfig.php (ajaxAddToCart)
    //       calls $this->context->cart->saveCustomization() at line 2831
    //
    // Recovered from the live-shop override on 2026-04-23 after it was
    // accidentally wiped by a full-file deploy of onlyprint_special_prices.
    //
    // NOTE: The sibling method saveCustomization2() (never called anywhere,
    // and containing an undefined $customize_price variable) was intentionally
    // dropped during the merge. If some code path ever needs it, re-add with
    // the bug fixed.
    // ------------------------------------------------------------------------

    public function saveCustomization($id_product, $id_product_attribute = 0)
    {
        $in_cart = 1;
        $id_shop = 1;
        $id_address_delivery = 0;
        if (Context::getContext()->customer->id) {
            if ($id_address_delivery == 0 && (int) $this->id_address_delivery) {
                // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = $this->id_address_delivery;
            } elseif ($id_address_delivery == 0) {
                // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int) Address::getFirstCustomerAddressId((int) Context::getContext()->customer->id);
            } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) {
                // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
            }
        }

        $cartProductQuantity = 1; // $this->getProductQuantity($id_product, $id_product_attribute);
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'customization` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`, `in_cart`, `id_address_delivery`)
            VALUES ('.(int) $this->id.', '.(int) $id_product.', '.(int) $id_product_attribute.', '.(int) $cartProductQuantity.', '.$in_cart.', '.$id_address_delivery.')'
        );
        $id_customization = Db::getInstance()->Insert_ID();
        $qry = 'INSERT INTO `'._DB_PREFIX_.'cart_product` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`, `id_customization`, `id_address_delivery`, `id_shop`)
        VALUES ('.(int) $this->id.', '.(int) $id_product.', '.(int) $id_product_attribute.', '.(int) $cartProductQuantity.', '.$id_customization.',  '.$id_address_delivery.',  '.$id_shop.')';
        Db::getInstance()->execute($qry);

        return $id_customization;
    }

    public function addCustomizationData($id_customization, $index, $type, $field, $price = 0, $weight = 0)
    {
        $customization = Db::getInstance()->execute('
            SELECT * FROM `'._DB_PREFIX_.'customized_data`
            WHERE id_customization = '.(int) $id_customization.'
            AND type = '.(int) $type.'
            AND `index` = '.(int) $index);
        if ($type == Product::CUSTOMIZE_FILE) {
            @unlink(_PS_UPLOAD_DIR_.$customization['value']);
            @unlink(_PS_UPLOAD_DIR_.$customization['value'].'_small');
        }
        Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'customized_data`
            WHERE id_customization = '.(int) $id_customization.'
            AND type = '.(int) $type.'
            AND `index` = '.(int) $index);
        $query = 'INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`, `price`, `weight`)
            VALUES ('.(int) $id_customization.', '.(int) $type.', '.(int) $index.', \''.pSQL($field).'\', '.(float) $price.', '.(float) $weight.')';

        if (!Db::getInstance()->execute($query)) {
            return false;
        }
        return true;
    }

    public function getProductCustomizationData($id_product, $id_product_attribute, $type = null, $not_in_cart = false)
    {
        if (!Customization::isFeatureActive()) {
            return array();
        }

        $result = Db::getInstance()->executeS(
            'SELECT cu.id_customization, cd.index, cd.value, cd.type, cu.in_cart, cu.quantity
            FROM `'._DB_PREFIX_.'customization` cu
            LEFT JOIN `'._DB_PREFIX_.'customized_data` cd ON (cu.`id_customization` = cd.`id_customization`)
            WHERE cu.id_cart = '.(int) $this->id.'
            AND cu.id_product = '.(int) $id_product.
            (!empty($id_product_attribute) ? ' AND cu.`id_product_attribute` = '.(int) $id_product_attribute : '').
            ($type === Product::CUSTOMIZE_FILE ? ' AND type = '.(int) Product::CUSTOMIZE_FILE : '').
            ($type === Product::CUSTOMIZE_TEXTFIELD ? ' AND type = '.(int) Product::CUSTOMIZE_TEXTFIELD : '').
            ($not_in_cart ? ' AND in_cart = 0' : '')
        );
        return $result;
    }

    public function deleteCustomizationToProduct($id_product, $index, $id_product_attribute = 0)
    {
        $result = true;
        $cust_data = Db::getInstance()->getRow(
            'SELECT cu.`id_customization`, cd.`index`, cd.`value`, cd.`type` FROM `'._DB_PREFIX_.'customization` cu
            LEFT JOIN `'._DB_PREFIX_.'customized_data` cd
            ON cu.`id_customization` = cd.`id_customization`
            WHERE cu.`id_cart` = '.(int) $this->id.'
            AND cu.`id_product` = '.(int) $id_product.
            (!empty($id_product_attribute) ? ' AND cu.`id_product_attribute` = '.(int) $id_product_attribute : '').'
            AND `index` = '.(int) $index.'
            AND `in_cart` = 1'
        );
        if ($cust_data['type'] == 0) {
            $result &= (@unlink(_PS_UPLOAD_DIR_.$cust_data['value']) && @unlink(_PS_UPLOAD_DIR_.$cust_data['value'].'_small'));
        }
        if ($cust_data['type'] == 2) {
            $result &= (@unlink(_PS_UPLOAD_DIR_.$cust_data['value']));
        }
        $result &= Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'customized_data`
            WHERE `id_customization` = '.(int) $cust_data['id_customization'].'
            AND `index` = '.(int) $index
        );
        return $result;
    }

    private function getOrderPrices(
        array $productRow,
        int $orderId,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): array {
        $orderPrices = [];
        $orderPrices['price_without_reduction'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            true,
            false,
            true,
            $productRow['id_customization']
        );

        $orderPrices['price_without_reduction_without_tax'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            false,
            false,
            true,
            $productRow['id_customization']
        );

        $orderPrices['price_with_reduction'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            true,
            true,
            true,
            $productRow['id_customization']
        );

        $orderPrices['price'] = $orderPrices['price_with_reduction_without_tax'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            false,
            true,
            true,
            $productRow['id_customization']
        );

        // If the product price was not found in the order, use cart prices as fallback
        if (false !== array_search(null, $orderPrices)) {
            $cartPrices = $this->getCartPrices(
                $productRow,
                $productQuantity,
                $addressId,
                $shopContext,
                $specificPriceOutput
            );
            foreach ($orderPrices as $orderPrice => $value) {
                if (null === $value) {
                    $orderPrices[$orderPrice] = $cartPrices[$orderPrice];
                }
            }
        }

        return $orderPrices;
    }

    private function getCartPrices(
        array $productRow,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): array {
        $cartPrices = [];
        $cartPrices['price_without_reduction'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            true,
            false,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price_without_reduction_without_tax'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            false,
            false,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price_with_reduction'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            true,
            true,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price'] = $cartPrices['price_with_reduction_without_tax'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            false,
            true,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        return $cartPrices;
    }

    private function getCartPriceFromCatalog(
        int $productId,
        int $combinationId,
        int $customizationId,
        bool $withTaxes,
        bool $useReduction,
        bool $withEcoTax,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): ?float {
        return Product::getPriceStatic(
            $productId,
            $withTaxes,
            $combinationId,
            6,
            null,
            false,
            $useReduction,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $addressId,
            $specificPriceOutput,
            $withEcoTax,
            true,
            $shopContext,
            true,
            $customizationId
        );
    }

    // ------------------------------------------------------------------------
    // [C] pshowadvancedstock block
    //     NOT INCLUDED IN THIS MERGE.
    //
    //     The live override adds a `public $ids_carriers` field plus a
    //     self::$definition['fields']['ids_carriers'] = TYPE_STRING
    //     entry in the constructor. That definition makes ObjectModel write
    //     to the `ids_carriers` column on every Cart save. If that column
    //     does NOT exist in ps_cart on this environment, add-to-cart will
    //     die on "Unknown column 'ids_carriers'".
    //
    //     Before adding it back, verify on this environment:
    //       SHOW COLUMNS FROM ps_cart LIKE 'ids_carriers';     -- must return a row
    //       SELECT active FROM ps_module WHERE name='pshowadvancedstock';
    //
    //     If both yes, paste the block from the live override here.
    // ------------------------------------------------------------------------
}
