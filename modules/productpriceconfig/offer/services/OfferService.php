<?php
/**
 * OfferService - Core business logic for the Offer/Quotation feature.
 *
 * Orchestrates Offer, OfferProduct, OfferAttachment, OfferHistory,
 * status transitions, and delegates to ProductPriceConfig for
 * price calculation, banned combination validation, and cart logic.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferService
{
    /** @var ProductPriceConfig */
    private $module;

    /** @var int Default offer validity in days */
    private $defaultValidityDays;

    public function __construct(ProductPriceConfig $module)
    {
        $this->module = $module;
        $this->defaultValidityDays = (int) Configuration::get('PPC_OFFER_VALIDITY_DAYS', 15);
    }

    /**
     * Create a new draft offer.
     *
     * @param array $data title, id_customer, guest_email, guest_name, customer_note
     * @return Offer
     */
    public function createDraft($data)
    {
        $offer = new Offer();
        $offer->reference = Offer::generateReference();
        $offer->title = isset($data['title']) ? $data['title'] : '';
        $offer->id_customer = isset($data['id_customer']) ? (int) $data['id_customer'] : 0;
        $offer->guest_email = isset($data['guest_email']) ? $data['guest_email'] : '';
        $offer->guest_name = isset($data['guest_name']) ? $data['guest_name'] : '';
        $offer->customer_note = isset($data['customer_note']) ? $data['customer_note'] : '';
        $offer->status = Offer::STATUS_DRAFT;
        $offer->total_products = 0;
        $offer->date_add = date('Y-m-d H:i:s');
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        OfferHistoryLogger::log($offer->id, OfferHistory::ACTION_QUOTE_CREATED, [
            'id_customer' => $offer->id_customer ?: null,
            'description' => 'Quotation ' . $offer->reference . ' created.',
        ]);

        return $offer;
    }

    /**
     * Submit a draft offer (customer sends it to admin).
     *
     * @param int $id_offer
     * @param int $id_customer
     * @return array [success => bool, error => string|null]
     */
    public function submitOffer($id_offer, $id_customer)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'error' => 'Offer not found.'];
        }

        if (!$offer->belongsTo($id_customer, $this->getGuestEmail())) {
            return ['success' => false, 'error' => 'You do not own this offer.'];
        }

        $products = OfferProduct::getByOffer($id_offer);
        if (!$products) {
            return ['success' => false, 'error' => 'Cannot submit an empty quotation.'];
        }

        $offer->status = Offer::STATUS_SUBMITTED;
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        foreach ($products as $p) {
            OfferProduct::updateStatus($p['id_offer_product'], OfferProduct::STATUS_WAITING_FOR_PRICE);
        }

        $offer->status = Offer::STATUS_WAITING_FOR_PRICE;
        $offer->save();

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_QUOTE_SUBMITTED, [
            'id_customer' => $id_customer,
            'description' => 'Quotation submitted by customer.',
        ]);

        OfferEmailNotifier::sendNotification('quote_submitted', $offer);

        return ['success' => true, 'error' => null];
    }

    /**
     * Add a product to an offer.
     *
     * @param int $id_offer
     * @param int $id_product
     * @param int $id_product_attribute
     * @param string $configuration_json
     * @param int $quantity
     * @param string $customer_note
     * @return array [success, id_offer_product, error]
     */
    public function addProduct($id_offer, $id_product, $id_product_attribute, $configuration_json, $quantity, $customer_note)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'id_offer_product' => null, 'error' => 'Offer not found.'];
        }

        $product = new Product($id_product, false, Context::getContext()->language->id);
        if (!Validate::isLoadedObject($product)) {
            return ['success' => false, 'id_offer_product' => null, 'error' => 'Product not found.'];
        }

        if (!$product->active) {
            return ['success' => false, 'id_offer_product' => null, 'error' => 'Product is not active.'];
        }

        $op = new OfferProduct();
        $op->id_offer = (int) $id_offer;
        $op->id_product = (int) $id_product;
        $op->id_product_attribute = (int) $id_product_attribute;
        $op->configuration_json = $configuration_json;
        $op->quantity = max(1, (int) $quantity);
        $op->customer_note = $customer_note;
        $op->product_status = OfferProduct::STATUS_WAITING_FOR_PRICE;
        $op->id_currency = (int) Context::getContext()->currency->id;
        $op->date_add = date('Y-m-d H:i:s');
        $op->date_upd = date('Y-m-d H:i:s');
        $op->save();

        Offer::recalculateTotals($id_offer);

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_PRODUCT_ADDED, [
            'id_offer_product' => $op->id,
            'description' => 'Product "' . $product->name . '" added to quotation.',
        ]);

        return ['success' => true, 'id_offer_product' => $op->id, 'error' => null];
    }

    /**
     * Remove a product from an offer.
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @return array [success, error]
     */
    public function removeProduct($id_offer, $id_offer_product)
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Offer product not found.'];
        }

        OfferAttachment::deleteByOfferProduct($id_offer_product);

        $productName = '';
        $row = OfferProduct::getOfferProduct($id_offer_product);
        if ($row) {
            $productName = $row['product_name'];
        }

        $op->delete();
        Offer::recalculateTotals($id_offer);

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_PRODUCT_REMOVED, [
            'id_offer_product' => $id_offer_product,
            'description' => 'Product "' . $productName . '" removed from quotation.',
        ]);

        return ['success' => true, 'error' => null];
    }

    /**
     * Update product configuration.
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @param string $configuration_json
     * @param int $quantity
     * @return array [success, error]
     */
    public function updateProductConfig($id_offer, $id_offer_product, $configuration_json, $quantity)
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Offer product not found.'];
        }

        $oldConfig = $op->configuration_json;
        $op->configuration_json = $configuration_json;
        $op->quantity = max(1, (int) $quantity);
        $op->date_upd = date('Y-m-d H:i:s');
        $op->save();

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_PRODUCT_CONFIG_CHANGED, [
            'id_offer_product' => $id_offer_product,
            'previous_value' => $oldConfig,
            'new_value' => $configuration_json,
            'description' => 'Product configuration updated.',
        ]);

        return ['success' => true, 'error' => null];
    }

    /**
     * Admin offers a price for a product (new negotiation round).
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @param array $data offered_price, weight, production_time, admin_note, discounted_amount, expire_days
     * @param int $id_employee
     * @return array [success, error]
     */
    public function adminOfferPrice($id_offer, $id_offer_product, $data, $id_employee)
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Offer product not found.'];
        }

        $offer = new Offer($id_offer);

        $oldPrice = $op->offered_price;
        $oldWeight = $op->weight;
        $oldProduction = $op->production_time;
        $oldNote = $op->admin_note;

        $expireDays = isset($data['expire_days']) ? (int) $data['expire_days'] : $this->defaultValidityDays;
        $expireDate = date('Y-m-d', strtotime('+' . $expireDays . ' days'));

        OfferProduct::updateOffer($id_offer_product, [
            'offered_price' => isset($data['offered_price']) ? (float) $data['offered_price'] : null,
            'discounted_amount' => isset($data['discounted_amount']) ? (float) $data['discounted_amount'] : 0,
            'weight' => isset($data['weight']) ? (float) $data['weight'] : null,
            'production_time' => isset($data['production_time']) ? $data['production_time'] : null,
            'admin_note' => isset($data['admin_note']) ? $data['admin_note'] : null,
            'product_status' => OfferProduct::STATUS_PRICE_OFFERED,
        ]);

        $offer->expire_date = $expireDate;
        $offer->status = Offer::STATUS_PRICE_OFFERED;
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_PRICE_CHANGED, [
            'id_offer_product' => $id_offer_product,
            'id_employee' => $id_employee,
            'previous_value' => $oldPrice !== null ? (string) $oldPrice : '',
            'new_value' => isset($data['offered_price']) ? (string) $data['offered_price'] : '',
            'description' => 'Admin offered price: ' . (isset($data['offered_price']) ? $data['offered_price'] : 'N/A'),
        ]);

        if (isset($data['weight']) && $data['weight'] != $oldWeight) {
            OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_WEIGHT_CHANGED, [
                'id_offer_product' => $id_offer_product,
                'id_employee' => $id_employee,
                'previous_value' => $oldWeight !== null ? (string) $oldWeight : '',
                'new_value' => (string) $data['weight'],
            ]);
        }

        if (isset($data['production_time']) && $data['production_time'] != $oldProduction) {
            OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_PRODUCTION_TIME_CHANGED, [
                'id_offer_product' => $id_offer_product,
                'id_employee' => $id_employee,
                'previous_value' => $oldProduction ?? '',
                'new_value' => $data['production_time'],
            ]);
        }

        if (isset($data['admin_note']) && $data['admin_note'] != $oldNote) {
            OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_NOTE_CHANGED, [
                'id_offer_product' => $id_offer_product,
                'id_employee' => $id_employee,
                'previous_value' => $oldNote ?? '',
                'new_value' => $data['admin_note'],
            ]);
        }

        OfferEmailNotifier::sendNotification('price_offered', $offer);

        return ['success' => true, 'error' => null];
    }

    /**
     * Customer accepts a product offer.
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @param int $id_customer
     * @return array [success, error]
     */
    public function customerAccept($id_offer, $id_offer_product, $id_customer)
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Offer product not found.'];
        }

        $offer = new Offer($id_offer);
        if (!$offer->belongsTo($id_customer, $this->getGuestEmail())) {
            return ['success' => false, 'error' => 'You do not own this offer.'];
        }

        if ($offer->isExpired()) {
            return ['success' => false, 'error' => 'This offer has expired.'];
        }

        if ($op->product_status !== OfferProduct::STATUS_PRICE_OFFERED) {
            return ['success' => false, 'error' => 'This product is not in a price-offered state.'];
        }

        OfferProduct::updateStatus($id_offer_product, OfferProduct::STATUS_ACCEPTED);

        $newStatus = OfferProduct::calculateOfferStatus($id_offer);
        Offer::updateStatus($id_offer, $newStatus);

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_CUSTOMER_ACCEPTED, [
            'id_offer_product' => $id_offer_product,
            'id_customer' => $id_customer,
            'description' => 'Customer accepted the offered price.',
        ]);

        OfferEmailNotifier::sendNotification('offer_accepted', $offer);

        return ['success' => true, 'error' => null];
    }

    /**
     * Customer rejects a product offer.
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @param int $id_customer
     * @param string $reason
     * @return array [success, error]
     */
    public function customerReject($id_offer, $id_offer_product, $id_customer, $reason = '')
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Offer product not found.'];
        }

        $offer = new Offer($id_offer);
        if (!$offer->belongsTo($id_customer, $this->getGuestEmail())) {
            return ['success' => false, 'error' => 'You do not own this offer.'];
        }

        OfferProduct::updateStatus($id_offer_product, OfferProduct::STATUS_REJECTED);

        $newStatus = OfferProduct::calculateOfferStatus($id_offer);
        Offer::updateStatus($id_offer, $newStatus);

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_CUSTOMER_REJECTED, [
            'id_offer_product' => $id_offer_product,
            'id_customer' => $id_customer,
            'description' => 'Customer rejected the offer. Reason: ' . $reason,
        ]);

        OfferEmailNotifier::sendNotification('offer_rejected', $offer);

        return ['success' => true, 'error' => null];
    }

    /**
     * Admin cancels an offer.
     *
     * @param int $id_offer
     * @param int $id_employee
     * @return array [success, error]
     */
    public function adminCancel($id_offer, $id_employee)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'error' => 'Offer not found.'];
        }

        $offer->status = Offer::STATUS_CANCELLED;
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        $products = OfferProduct::getByOffer($id_offer);
        foreach ($products as $p) {
            if ($p['product_status'] !== OfferProduct::STATUS_ACCEPTED
                && $p['product_status'] !== OfferProduct::STATUS_CONVERTED_TO_ORDER) {
                OfferProduct::updateStatus($p['id_offer_product'], OfferProduct::STATUS_CANCELLED);
            }
        }

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_CANCELLED, [
            'id_employee' => $id_employee,
            'description' => 'Admin cancelled the quotation.',
        ]);

        OfferEmailNotifier::sendNotification('offer_cancelled', $offer);

        return ['success' => true, 'error' => null];
    }

    /**
     * Extend the expiry date of an offer.
     *
     * @param int $id_offer
     * @param int $days
     * @param int $id_employee
     * @return array [success, error]
     */
    public function extendExpiry($id_offer, $days, $id_employee)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'error' => 'Offer not found.'];
        }

        $oldExpiry = $offer->expire_date;
        $newExpiry = date('Y-m-d', strtotime('+' . (int) $days . ' days'));

        $offer->expire_date = $newExpiry;
        if ($offer->status === Offer::STATUS_EXPIRED) {
            $offer->status = Offer::STATUS_PRICE_OFFERED;
        }
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_QUOTE_REOPENED, [
            'id_employee' => $id_employee,
            'previous_value' => $oldExpiry,
            'new_value' => $newExpiry,
            'description' => 'Expiry extended by ' . $days . ' days.',
        ]);

        return ['success' => true, 'error' => null];
    }

    /**
     * Check and expire offers that are past their expiry date.
     *
     * @return int Number of offers expired
     */
    public function expireOutdated()
    {
        $sql = 'SELECT id_offer FROM ' . _DB_PREFIX_ . 'offer
                WHERE expire_date IS NOT NULL
                AND expire_date < CURDATE()
                AND status NOT IN ("' . Offer::STATUS_EXPIRED . '", "' . Offer::STATUS_CANCELLED . '",
                    "' . Offer::STATUS_CONVERTED_TO_ORDER . '", "' . Offer::STATUS_CLOSED . '")';

        $expired = Db::getInstance()->executeS($sql) ?: [];
        $count = 0;

        foreach ($expired as $row) {
            $offer = new Offer($row['id_offer']);
            $offer->status = Offer::STATUS_EXPIRED;
            $offer->date_upd = date('Y-m-d H:i:s');
            $offer->save();

            $products = OfferProduct::getByOffer($row['id_offer']);
            foreach ($products as $p) {
                if ($p['product_status'] === OfferProduct::STATUS_PRICE_OFFERED) {
                    OfferProduct::updateStatus($p['id_offer_product'], OfferProduct::STATUS_EXPIRED);
                }
            }

            OfferHistoryLogger::log($row['id_offer'], OfferHistory::ACTION_QUOTE_EXPIRED, [
                'description' => 'Offer expired automatically.',
            ]);

            OfferEmailNotifier::sendNotification('offer_expired', $offer);
            $count++;
        }

        return $count;
    }

    /**
     * Duplicate an offer into a new draft.
     *
     * @param int $id_offer
     * @param int $id_customer
     * @return array [success, id_new_offer, error]
     */
    public function duplicateOffer($id_offer, $id_customer)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'id_new_offer' => null, 'error' => 'Offer not found.'];
        }

        if (!$offer->belongsTo($id_customer, $this->getGuestEmail())) {
            return ['success' => false, 'id_new_offer' => null, 'error' => 'You do not own this offer.'];
        }

        $newOffer = new Offer();
        $newOffer->reference = Offer::generateReference();
        $newOffer->title = $offer->title . ' (Copy)';
        $newOffer->id_customer = $offer->id_customer;
        $newOffer->guest_email = $offer->guest_email;
        $newOffer->guest_name = $offer->guest_name;
        $newOffer->customer_note = $offer->customer_note;
        $newOffer->status = Offer::STATUS_DRAFT;
        $newOffer->total_products = 0;
        $newOffer->date_add = date('Y-m-d H:i:s');
        $newOffer->date_upd = date('Y-m-d H:i:s');
        $newOffer->save();

        $products = OfferProduct::getByOffer($id_offer);
        foreach ($products as $p) {
            $op = new OfferProduct();
            $op->id_offer = (int) $newOffer->id;
            $op->id_product = (int) $p['id_product'];
            $op->id_product_attribute = (int) $p['id_product_attribute'];
            $op->configuration_json = $p['configuration_json'];
            $op->quantity = (int) $p['quantity'];
            $op->customer_note = $p['customer_note'];
            $op->product_status = OfferProduct::STATUS_WAITING_FOR_PRICE;
            $op->id_currency = (int) $p['id_currency'];
            $op->date_add = date('Y-m-d H:i:s');
            $op->date_upd = date('Y-m-d H:i:s');
            $op->save();

            $attachments = OfferAttachment::getByOfferProduct($p['id_offer_product']);
            foreach ($attachments as $att) {
                $oldPath = OfferAttachment::getUploadDir() . $att['saved_name'];
                $newSavedName = OfferAttachment::generateSavedName($att['original_name']);
                $newPath = OfferAttachment::getUploadDir() . $newSavedName;

                if (file_exists($oldPath)) {
                    @copy($oldPath, $newPath);
                }

                $newAtt = new OfferAttachment();
                $newAtt->id_offer_product = (int) $op->id;
                $newAtt->original_name = $att['original_name'];
                $newAtt->saved_name = $newSavedName;
                $newAtt->extension = $att['extension'];
                $newAtt->mime_type = $att['mime_type'];
                $newAtt->size = (int) $att['size'];
                $newAtt->width = (int) $att['width'];
                $newAtt->height = (int) $att['height'];
                $newAtt->uploaded_by = (int) $id_customer;
                $newAtt->date_add = date('Y-m-d H:i:s');
                $newAtt->save();
            }
        }

        Offer::recalculateTotals($newOffer->id);

        OfferHistoryLogger::log($newOffer->id, OfferHistory::ACTION_OFFER_DUPLICATED, [
            'id_customer' => $id_customer,
            'description' => 'Quotation duplicated from ' . $offer->reference . '.',
        ]);

        return ['success' => true, 'id_new_offer' => $newOffer->id, 'error' => null];
    }

    /**
     * Convert accepted offer products to cart.
     *
     * @param int $id_offer
     * @param int $id_customer
     * @return array [success, added, errors]
     */
    public function convertToCart($id_offer, $id_customer)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'added' => [], 'errors' => ['Offer not found.']];
        }

        if (!$offer->belongsTo($id_customer, $this->getGuestEmail())) {
            return ['success' => false, 'added' => [], 'errors' => ['You do not own this offer.']];
        }

        if ($offer->isExpired()) {
            return ['success' => false, 'added' => [], 'errors' => ['This offer has expired.']];
        }

        $accepted = OfferProduct::getAcceptedProducts($id_offer);
        if (!$accepted) {
            return ['success' => false, 'added' => [], 'errors' => ['No accepted products to add to cart.']];
        }

        $added = [];
        $errors = [];

        foreach ($accepted as $p) {
            $config = json_decode($p['configuration_json'], true);
            if (!$config) {
                $errors[] = 'Invalid configuration for "' . $p['product_name'] . '".';
                continue;
            }

            $params = $this->buildParamsFromConfig($config, $p['id_product'], $p['quantity']);

            $result = $this->module->processAddToCart($params);
            if (isset($result['error']) && $result['error']) {
                $errors[] = $p['product_name'] . ': ' . $result['error'];
            } else {
                $added[] = $p['product_name'];
            }
        }

        return ['success' => empty($errors), 'added' => $added, 'errors' => $errors];
    }

    /**
     * Convert an offer directly to a PrestaShop order (admin action).
     *
     * @param int $id_offer
     * @param int $id_employee
     * @param array $data id_address_delivery, id_address_invoice, id_carrier, payment_module_name
     * @return array [success, id_order, error]
     */
    public function convertToOrder($id_offer, $id_employee, $data)
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return ['success' => false, 'id_order' => null, 'error' => 'Offer not found.'];
        }

        $accepted = OfferProduct::getAcceptedProducts($id_offer);
        if (!$accepted) {
            return ['success' => false, 'id_order' => null, 'error' => 'No accepted products to convert.'];
        }

        if (!$offer->id_customer) {
            return ['success' => false, 'id_order' => null, 'error' => 'Guest customers must be converted to registered customers first.'];
        }

        $id_address_delivery = (int) ($data['id_address_delivery'] ?? 0);
        $id_address_invoice = (int) ($data['id_address_invoice'] ?? 0);
        $id_carrier = (int) ($data['id_carrier'] ?? 0);
        $paymentModule = $data['payment_module_name'] ?? 'cheque';

        if (!$id_address_delivery || !$id_address_invoice) {
            return ['success' => false, 'id_order' => null, 'error' => 'Delivery and invoice addresses are required.'];
        }

        $cart = new Cart();
        $cart->id_customer = (int) $offer->id_customer;
        $cart->id_address_delivery = $id_address_delivery;
        $cart->id_address_invoice = $id_address_invoice;
        $cart->id_currency = (int) Context::getContext()->currency->id;
        $cart->id_lang = (int) Context::getContext()->language->id;
        $cart->id_carrier = $id_carrier;
        $cart->recyclable = 0;
        $cart->gift = 0;
        $cart->add();

        foreach ($accepted as $p) {
            $config = json_decode($p['configuration_json'], true);
            if (!$config) {
                continue;
            }

            $params = $this->buildParamsFromConfig($config, $p['id_product'], $p['quantity']);
            $result = $this->module->processAddToCart($params);
            if (isset($result['error']) && $result['error']) {
                continue;
            }

            $cart->updateQty($p['quantity'], $p['id_product'], $p['id_product_attribute'] ?? 0, false, 'up', 0, null, true);
        }

        $cart->update();

        $paymentModuleObj = Module::getInstanceByName($paymentModule);
        if (!Validate::isLoadedObject($paymentModuleObj)) {
            $paymentModuleObj = Module::getInstanceByName('cheque');
        }

        $paymentModuleObj->validateOrder(
            (int) $cart->id,
            (int) Configuration::get('PS_OS_PAYMENT'),
            $cart->getOrderTotal(true, Cart::BOTH),
            $paymentModuleObj->displayName,
            'Order created from Offer ' . $offer->reference,
            [],
            null,
            false,
            $cart->secure_key
        );

        $id_order = (int) Order::getOrderByCartId($cart->id);
        if (!$id_order) {
            return ['success' => false, 'id_order' => null, 'error' => 'Order creation failed.'];
        }

        $offer->id_order = $id_order;
        $offer->id_cart = (int) $cart->id;
        $offer->status = Offer::STATUS_CONVERTED_TO_ORDER;
        $offer->date_upd = date('Y-m-d H:i:s');
        $offer->save();

        foreach ($accepted as $p) {
            OfferProduct::updateStatus($p['id_offer_product'], OfferProduct::STATUS_CONVERTED_TO_ORDER);
        }

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_CONVERTED_TO_ORDER, [
            'id_employee' => $id_employee,
            'new_value' => (string) $id_order,
            'description' => 'Offer converted to Order #' . $id_order,
        ]);

        OfferEmailNotifier::sendNotification('converted_to_order', $offer);

        return ['success' => true, 'id_order' => $id_order, 'error' => null];
    }

    /**
     * Upload an attachment for an offer product.
     *
     * @param int $id_offer
     * @param int $id_offer_product
     * @param array $file $_FILES entry
     * @param int $uploaded_by
     * @return array [success, id_attachment, error]
     */
    public function uploadAttachment($id_offer, $id_offer_product, $file, $uploaded_by)
    {
        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op) || (int) $op->id_offer !== (int) $id_offer) {
            return ['success' => false, 'id_attachment' => null, 'error' => 'Offer product not found.'];
        }

        $validation = OfferAttachment::validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'id_attachment' => null, 'error' => $validation['error']];
        }

        $savedName = OfferAttachment::generateSavedName($file['name']);
        $uploadDir = OfferAttachment::getUploadDir();
        $destPath = $uploadDir . $savedName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'id_attachment' => null, 'error' => 'Failed to save uploaded file.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $width = 0;
        $height = 0;

        if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
            $imageInfo = @getimagesize($destPath);
            if ($imageInfo) {
                $width = (int) $imageInfo[0];
                $height = (int) $imageInfo[1];
            }
        }

        $att = new OfferAttachment();
        $att->id_offer_product = (int) $id_offer_product;
        $att->original_name = $file['name'];
        $att->saved_name = $savedName;
        $att->extension = $extension;
        $att->mime_type = $file['type'];
        $att->size = (int) $file['size'];
        $att->width = $width;
        $att->height = $height;
        $att->uploaded_by = (int) $uploaded_by;
        $att->date_add = date('Y-m-d H:i:s');
        $att->save();

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ARTWORK_UPLOADED, [
            'id_offer_product' => $id_offer_product,
            'new_value' => $file['name'],
            'description' => 'Artwork file "' . $file['name'] . '" uploaded.',
        ]);

        return ['success' => true, 'id_attachment' => $att->id, 'error' => null];
    }

    /**
     * Remove an attachment.
     *
     * @param int $id_offer
     * @param int $id_attachment
     * @return array [success, error]
     */
    public function removeAttachment($id_offer, $id_attachment)
    {
        $row = Db::getInstance()->getRow(
            'SELECT a.*, op.id_offer FROM ' . _DB_PREFIX_ . 'offer_attachment a
             INNER JOIN ' . _DB_PREFIX_ . 'offer_product op ON op.id_offer_product = a.id_offer_product
             WHERE a.id_offer_attachment = ' . (int) $id_attachment
        );

        if (!$row || (int) $row['id_offer'] !== (int) $id_offer) {
            return ['success' => false, 'error' => 'Attachment not found.'];
        }

        OfferAttachment::deleteAttachment($id_attachment);

        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ARTWORK_REMOVED, [
            'id_offer_product' => $row['id_offer_product'],
            'previous_value' => $row['original_name'],
            'description' => 'Artwork file "' . $row['original_name'] . '" removed.',
        ]);

        return ['success' => true, 'error' => null];
    }

    /**
     * Get the full offer detail with products, attachments, and history.
     *
     * @param int $id_offer
     * @return array|false
     */
    public function getOfferDetail($id_offer)
    {
        $offer = Offer::getOffer($id_offer);
        if (!$offer) {
            return false;
        }

        $products = OfferProduct::getByOffer($id_offer);

        foreach ($products as &$p) {
            $p['attachments'] = OfferAttachment::getByOfferProduct($p['id_offer_product']);
            $p['configuration_summary'] = $this->buildConfigSummary($p['configuration_json']);
        }

        $history = OfferHistory::getByOffer($id_offer);

        return [
            'offer' => $offer,
            'products' => $products,
            'history' => $history,
        ];
    }

    /**
     * Build a human-readable summary from configuration JSON.
     *
     * @param string $json
     * @return string
     */
    private function buildConfigSummary($json)
    {
        $config = json_decode($json, true);
        if (!$config || !is_array($config)) {
            return '';
        }

        $parts = [];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $parts[] = $key . ': ' . $value;
        }

        return implode(' | ', $parts);
    }

    /**
     * Convert stored configuration JSON back into the variable_X params format
     * expected by ProductPriceConfig::processAddToCart().
     *
     * @param array $config
     * @param int $id_product
     * @param int $quantity
     * @return array
     */
    private function buildParamsFromConfig($config, $id_product, $quantity)
    {
        $params = [
            'id_product' => (int) $id_product,
            'qty' => (int) $quantity,
        ];

        foreach ($config as $key => $value) {
            if (strpos($key, 'variable_') === 0) {
                $params[$key] = $value;
            } else {
                $params['variable_' . $key] = $value;
            }
        }

        return $params;
    }

    /**
     * Get guest email from context (cookie or session).
     *
     * @return string
     */
    private function getGuestEmail()
    {
        if ($this->context->customer->isLogged()) {
            return '';
        }

        return Context::getContext()->cookie->pc_guest_email ?? '';
    }
}
