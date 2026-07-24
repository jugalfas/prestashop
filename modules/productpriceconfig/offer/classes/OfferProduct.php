<?php
/**
 * OfferProduct - ObjectModel for products inside a quotation.
 *
 * Each row stores one configured product with its configuration JSON,
 * offered price, weight, production time, and per-product status.
 *
 * Table: ps_offer_product
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferProduct extends ObjectModel
{
    const STATUS_DRAFT = 'draft';
    const STATUS_WAITING_FOR_PRICE = 'waiting_for_price';
    const STATUS_PRICE_OFFERED = 'price_offered';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CONVERTED_TO_ORDER = 'converted_to_order';

    /** @var int */
    public $id_offer_product;

    /** @var int */
    public $id_offer;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_product_attribute;

    /** @var string JSON configuration */
    public $configuration_json;

    /** @var string uploaded file path (legacy single-file field) */
    public $uploaded_file;

    /** @var float|null */
    public $offered_price;

    /** @var float|null */
    public $discounted_amount;

    /** @var float|null */
    public $weight;

    /** @var string|null */
    public $production_time;

    /** @var int */
    public $id_currency;

    /** @var string */
    public $product_status = self::STATUS_WAITING_FOR_PRICE;

    /** @var string */
    public $admin_note;

    /** @var string */
    public $customer_note;

    /** @var int */
    public $quantity = 1;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = array(
        'table' => 'offer_product',
        'primary' => 'id_offer_product',
        'multilang' => false,
        'fields' => array(
            'id_offer'             => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'id_product'           => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'configuration_json'   => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'uploaded_file'         => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 500),
            'offered_price'        => array('type' => self::TYPE_FLOAT,  'validate' => 'isFloat', 'required' => false),
            'discounted_amount'    => array('type' => self::TYPE_FLOAT,  'validate' => 'isFloat', 'required' => false),
            'weight'               => array('type' => self::TYPE_FLOAT,  'validate' => 'isFloat', 'required' => false),
            'production_time'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'id_currency'          => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'product_status'       => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'admin_note'           => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'customer_note'        => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'quantity'             => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'date_add'             => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
            'date_upd'             => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
        ),
    );

    /**
     * Get all products for an offer.
     *
     * @param int $id_offer
     * @return array
     */
    public static function getByOffer($id_offer)
    {
        $sql = 'SELECT op.*, pl.name AS product_name, pl.link_rewrite, pl.description_short,
                    p.reference AS product_reference, p.id_image,
                    i.id_image AS image_id
                FROM ' . _DB_PREFIX_ . 'offer_product op
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = op.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = op.id_product
                    AND pl.id_lang = ' . (int) Context::getContext()->language->id . '
                LEFT JOIN ' . _DB_PREFIX_ . 'image i ON i.id_product = op.id_product AND i.cover = 1
                WHERE op.id_offer = ' . (int) $id_offer . '
                ORDER BY op.date_add ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Get a single offer product by ID.
     *
     * @param int $id_offer_product
     * @return array|false
     */
    public static function getOfferProduct($id_offer_product)
    {
        $sql = 'SELECT op.*, pl.name AS product_name, pl.link_rewrite,
                    p.reference AS product_reference
                FROM ' . _DB_PREFIX_ . 'offer_product op
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = op.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = op.id_product
                    AND pl.id_lang = ' . (int) Context::getContext()->language->id . '
                WHERE op.id_offer_product = ' . (int) $id_offer_product;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get accepted products for an offer.
     *
     * @param int $id_offer
     * @return array
     */
    public static function getAcceptedProducts($id_offer)
    {
        $sql = 'SELECT op.*, pl.name AS product_name, pl.link_rewrite,
                    p.reference AS product_reference
                FROM ' . _DB_PREFIX_ . 'offer_product op
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = op.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = op.id_product
                    AND pl.id_lang = ' . (int) Context::getContext()->language->id . '
                WHERE op.id_offer = ' . (int) $id_offer . '
                AND op.product_status = "' . self::STATUS_ACCEPTED . '"
                ORDER BY op.date_add ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Update product status.
     *
     * @param int $id_offer_product
     * @param string $status
     * @return bool
     */
    public static function updateStatus($id_offer_product, $status)
    {
        return Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'offer_product
             SET product_status = "' . pSQL($status) . '", date_upd = NOW()
             WHERE id_offer_product = ' . (int) $id_offer_product
        );
    }

    /**
     * Update offered price and related fields.
     *
     * @param int $id_offer_product
     * @param array $data offered_price, weight, production_time, admin_note, discounted_amount
     * @return bool
     */
    public static function updateOffer($id_offer_product, $data)
    {
        $sets = ['date_upd = NOW()'];

        if (isset($data['offered_price'])) {
            $sets[] = 'offered_price = ' . (float) $data['offered_price'];
        }
        if (isset($data['discounted_amount'])) {
            $sets[] = 'discounted_amount = ' . (float) $data['discounted_amount'];
        }
        if (isset($data['weight'])) {
            $sets[] = 'weight = ' . (float) $data['weight'];
        }
        if (isset($data['production_time'])) {
            $sets[] = 'production_time = "' . pSQL($data['production_time']) . '"';
        }
        if (isset($data['admin_note'])) {
            $sets[] = 'admin_note = "' . pSQL($data['admin_note']) . '"';
        }
        if (isset($data['product_status'])) {
            $sets[] = 'product_status = "' . pSQL($data['product_status']) . '"';
        }

        return Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'offer_product SET ' . implode(', ', $sets) .
            ' WHERE id_offer_product = ' . (int) $id_offer_product
        );
    }

    /**
     * Delete all products for an offer.
     *
     * @param int $id_offer
     * @return bool
     */
    public static function deleteByOffer($id_offer)
    {
        return Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'offer_product WHERE id_offer = ' . (int) $id_offer
        );
    }

    /**
     * Get all statuses as a label => value array.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_DRAFT             => 'Draft',
            self::STATUS_WAITING_FOR_PRICE => 'Waiting For Price',
            self::STATUS_PRICE_OFFERED     => 'Price Offered',
            self::STATUS_ACCEPTED          => 'Accepted',
            self::STATUS_REJECTED          => 'Rejected',
            self::STATUS_EXPIRED           => 'Expired',
            self::STATUS_CANCELLED         => 'Cancelled',
            self::STATUS_CONVERTED_TO_ORDER => 'Converted To Order',
        );
    }

    /**
     * Calculate the parent offer status from child product statuses.
     *
     * @param int $id_offer
     * @return string
     */
    public static function calculateOfferStatus($id_offer)
    {
        $products = Db::getInstance()->executeS(
            'SELECT product_status FROM ' . _DB_PREFIX_ . 'offer_product
             WHERE id_offer = ' . (int) $id_offer
        );

        if (!$products) {
            return Offer::STATUS_DRAFT;
        }

        $statuses = array_column($products, 'product_status');
        $allAccepted = true;
        $anyWaiting = false;
        $anyOffered = false;
        $allCancelled = true;

        foreach ($statuses as $s) {
            if ($s !== self::STATUS_ACCEPTED) {
                $allAccepted = false;
            }
            if ($s === self::STATUS_WAITING_FOR_PRICE) {
                $anyWaiting = true;
            }
            if ($s === self::STATUS_PRICE_OFFERED) {
                $anyOffered = true;
            }
            if ($s !== self::STATUS_CANCELLED) {
                $allCancelled = false;
            }
        }

        if ($allCancelled) {
            return Offer::STATUS_CANCELLED;
        }
        if ($allAccepted) {
            return Offer::STATUS_ACCEPTED;
        }
        if ($anyOffered) {
            return Offer::STATUS_PRICE_OFFERED;
        }
        if ($anyWaiting) {
            return Offer::STATUS_WAITING_FOR_PRICE;
        }

        return Offer::STATUS_CUSTOMER_REVIEWING;
    }
}
