<?php
/**
 * OfferHistory - ObjectModel for the audit trail of every offer action.
 *
 * History is append-only. Never update or delete rows.
 *
 * Table: ps_offer_history
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferHistory extends ObjectModel
{
    /** @var int */
    public $id_offer_history;

    /** @var int */
    public $id_offer;

    /** @var int|null */
    public $id_offer_product;

    /** @var int|null */
    public $id_customer;

    /** @var int|null */
    public $id_employee;

    /** @var string */
    public $action;

    /** @var string */
    public $previous_value;

    /** @var string */
    public $new_value;

    /** @var string */
    public $description;

    /** @var string */
    public $date_add;

    public static $definition = array(
        'table' => 'offer_history',
        'primary' => 'id_offer_history',
        'multilang' => false,
        'fields' => array(
            'id_offer'          => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'id_offer_product'  => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'id_customer'       => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'id_employee'       => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'action'            => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
            'previous_value'    => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'new_value'         => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'description'       => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'date_add'          => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
        ),
    );

    // Action constants
    const ACTION_QUOTE_CREATED = 'quote_created';
    const ACTION_PRODUCT_ADDED = 'product_added';
    const ACTION_PRODUCT_REMOVED = 'product_removed';
    const ACTION_PRODUCT_CONFIG_CHANGED = 'product_config_changed';
    const ACTION_ARTWORK_UPLOADED = 'artwork_uploaded';
    const ACTION_ARTWORK_REMOVED = 'artwork_removed';
    const ACTION_ADMIN_VIEWED = 'admin_viewed';
    const ACTION_ADMIN_NOTE_CHANGED = 'admin_note_changed';
    const ACTION_ADMIN_PRODUCTION_TIME_CHANGED = 'admin_production_time_changed';
    const ACTION_ADMIN_WEIGHT_CHANGED = 'admin_weight_changed';
    const ACTION_ADMIN_PRICE_CHANGED = 'admin_price_changed';
    const ACTION_CUSTOMER_ACCEPTED = 'customer_accepted';
    const ACTION_CUSTOMER_REJECTED = 'customer_rejected';
    const ACTION_ADMIN_CANCELLED = 'admin_cancelled';
    const ACTION_QUOTE_EXPIRED = 'quote_expired';
    const ACTION_QUOTE_REOPENED = 'quote_reopened';
    const ACTION_REOFFER_CREATED = 'reoffer_created';
    const ACTION_CONVERTED_TO_ORDER = 'converted_to_order';
    const ACTION_OFFER_DUPLICATED = 'offer_duplicated';
    const ACTION_CUSTOMER_NOTE_CHANGED = 'customer_note_changed';
    const ACTION_QUOTE_SUBMITTED = 'quote_submitted';

    /**
     * Get history for an offer, newest first.
     *
     * @param int $id_offer
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getByOffer($id_offer, $limit = 100, $offset = 0)
    {
        $sql = 'SELECT h.*, e.firstname AS employee_firstname, e.lastname AS employee_lastname,
                    c.firstname AS customer_firstname, c.lastname AS customer_lastname,
                    pl.name AS product_name
                FROM ' . _DB_PREFIX_ . 'offer_history h
                LEFT JOIN ' . _DB_PREFIX_ . 'employee e ON e.id_employee = h.id_employee
                LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer = h.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'offer_product op ON op.id_offer_product = h.id_offer_product
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = op.id_product
                    AND pl.id_lang = ' . (int) Context::getContext()->language->id . '
                WHERE h.id_offer = ' . (int) $id_offer . '
                ORDER BY h.date_add DESC, h.id_offer_history DESC
                LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Count history records for an offer.
     *
     * @param int $id_offer
     * @return int
     */
    public static function countByOffer($id_offer)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'offer_history WHERE id_offer = ' . (int) $id_offer
        );
    }

    /**
     * Get history for a specific offer product.
     *
     * @param int $id_offer_product
     * @param int $limit
     * @return array
     */
    public static function getByOfferProduct($id_offer_product, $limit = 50)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'offer_history
                WHERE id_offer_product = ' . (int) $id_offer_product . '
                ORDER BY date_add DESC
                LIMIT ' . (int) $limit;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Delete all history entries for an offer.
     * Used when duplicating an offer from admin to reset the history.
     *
     * @param int $id_offer
     * @return bool
     */
    public static function deleteByOffer($id_offer)
    {
        return Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'offer_history WHERE id_offer = ' . (int) $id_offer
        );
    }
}
