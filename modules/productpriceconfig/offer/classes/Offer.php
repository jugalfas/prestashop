<?php
/**
 * Offer - ObjectModel for quotations.
 *
 * Stores quotation header data: reference, customer, status, expiry, notes.
 *
 * Table: ps_offer
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Offer extends ObjectModel
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_WAITING_FOR_PRICE = 'waiting_for_price';
    const STATUS_PRICE_OFFERED = 'price_offered';
    const STATUS_CUSTOMER_REVIEWING = 'customer_reviewing';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CONVERTED_TO_ORDER = 'converted_to_order';
    const STATUS_CLOSED = 'closed';

    /** @var int */
    public $id_offer;

    /** @var string */
    public $reference;

    /** @var string */
    public $title;

    /** @var int */
    public $id_customer;

    /** @var int */
    public $id_cart;

    /** @var int */
    public $id_order;

    /** @var string */
    public $guest_email;

    /** @var string */
    public $guest_name;

    /** @var string */
    public $customer_note;

    /** @var string */
    public $admin_note;

    /** @var string */
    public $status = self::STATUS_DRAFT;

    /** @var string */
    public $expire_date;

    /** @var int */
    public $total_products = 0;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = array(
        'table' => 'offer',
        'primary' => 'id_offer',
        'multilang' => false,
        'fields' => array(
            'reference'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
            'title'           => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'id_customer'    => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'id_cart'         => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'id_order'        => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'guest_email'     => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => false, 'size' => 255),
            'guest_name'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'customer_note'   => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'admin_note'      => array('type' => self::TYPE_HTML,   'validate' => 'isCleanHtml', 'required' => false),
            'status'          => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'expire_date'     => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
            'total_products'  => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'date_add'        => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
            'date_upd'        => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
        ),
    );

    /**
     * Get all statuses as a label => value array (translated).
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_DRAFT             => 'Draft',
            self::STATUS_SUBMITTED         => 'Submitted',
            self::STATUS_WAITING_FOR_PRICE => 'Waiting For Price',
            self::STATUS_PRICE_OFFERED     => 'Price Offered',
            self::STATUS_CUSTOMER_REVIEWING => 'Customer Reviewing',
            self::STATUS_ACCEPTED          => 'Accepted',
            self::STATUS_REJECTED          => 'Rejected',
            self::STATUS_EXPIRED           => 'Expired',
            self::STATUS_CANCELLED         => 'Cancelled',
            self::STATUS_CONVERTED_TO_ORDER => 'Converted To Order',
            self::STATUS_CLOSED            => 'Closed',
        );
    }

    /**
     * Get a single offer by ID.
     *
     * @param int $id_offer
     * @return array|false
     */
    public static function getOffer($id_offer)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'offer WHERE id_offer = ' . (int) $id_offer;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get an offer by reference.
     *
     * @param string $reference
     * @return array|false
     */
    public static function getByReference($reference)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'offer
                WHERE reference = "' . pSQL($reference) . '"';

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get offers for a customer (or guest email).
     *
     * @param int $id_customer
     * @param string $guest_email
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getByCustomer($id_customer, $guest_email = '', $limit = 50, $offset = 0)
    {
        $where = '';
        if ($id_customer) {
            $where = 'id_customer = ' . (int) $id_customer;
        } elseif ($guest_email) {
            $where = 'guest_email = "' . pSQL($guest_email) . '"';
        } else {
            return [];
        }

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'offer
                WHERE ' . $where . '
                ORDER BY date_add DESC
                LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Count offers for a customer (or guest email).
     *
     * @param int $id_customer
     * @param string $guest_email
     * @return int
     */
    public static function countByCustomer($id_customer, $guest_email = '')
    {
        $where = '';
        if ($id_customer) {
            $where = 'id_customer = ' . (int) $id_customer;
        } elseif ($guest_email) {
            $where = 'guest_email = "' . pSQL($guest_email) . '"';
        } else {
            return 0;
        }

        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'offer WHERE ' . $where
        );
    }

    /**
     * Get offers for admin listing with filters.
     *
     * @param array $filters search, status, id_customer, date_from, date_to, expired_only
     * @param int $limit
     * @param int $offset
     * @param string $order_by
     * @param string $order_way
     * @return array
     */
    public static function getListing($filters = [], $limit = 50, $offset = 0, $order_by = 'date_add', $order_way = 'DESC')
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $search = pSQL($filters['search']);
            $where[] = '(o.reference LIKE "%' . $search . '%"
                OR o.title LIKE "%' . $search . '%"
                OR o.guest_email LIKE "%' . $search . '%"
                OR o.guest_name LIKE "%' . $search . '%"
                OR c.firstname LIKE "%' . $search . '%"
                OR c.lastname LIKE "%' . $search . '%"
                OR c.email LIKE "%' . $search . '%")';
        }

        if (!empty($filters['status'])) {
            $where[] = 'o.status = "' . pSQL($filters['status']) . '"';
        }

        if (!empty($filters['id_customer'])) {
            $where[] = 'o.id_customer = ' . (int) $filters['id_customer'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'o.date_add >= "' . pSQL($filters['date_from']) . ' 00:00:00"';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'o.date_add <= "' . pSQL($filters['date_to']) . ' 23:59:59"';
        }

        if (!empty($filters['expired_only'])) {
            $where[] = 'o.expire_date < NOW()';
            $where[] = 'o.status NOT IN ("' . self::STATUS_EXPIRED . '", "' . self::STATUS_CANCELLED . '", "' . self::STATUS_CONVERTED_TO_ORDER . '")';
        }

        $whereSql = '';
        if ($where) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $validOrderBy = ['id_offer', 'reference', 'title', 'status', 'expire_date', 'date_add', 'date_upd'];
        if (!in_array($order_by, $validOrderBy)) {
            $order_by = 'date_add';
        }
        $order_way = strtoupper($order_way) === 'ASC' ? 'ASC' : 'DESC';

        $sql = 'SELECT o.*, c.firstname, c.lastname, c.email,
                    (SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'offer_product op WHERE op.id_offer = o.id_offer) AS product_count
                FROM ' . _DB_PREFIX_ . 'offer o
                LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer = o.id_customer
                ' . $whereSql . '
                ORDER BY o.' . $order_by . ' ' . $order_way . '
                LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Count offers for admin listing with filters.
     *
     * @param array $filters
     * @return int
     */
    public static function countListing($filters = [])
    {
        $where = [];

        if (!empty($filters['search'])) {
            $search = pSQL($filters['search']);
            $where[] = '(o.reference LIKE "%' . $search . '%"
                OR o.title LIKE "%' . $search . '%"
                OR o.guest_email LIKE "%' . $search . '%"
                OR o.guest_name LIKE "%' . $search . '%"
                OR c.firstname LIKE "%' . $search . '%"
                OR c.lastname LIKE "%' . $search . '%"
                OR c.email LIKE "%' . $search . '%")';
        }

        if (!empty($filters['status'])) {
            $where[] = 'o.status = "' . pSQL($filters['status']) . '"';
        }

        if (!empty($filters['id_customer'])) {
            $where[] = 'o.id_customer = ' . (int) $filters['id_customer'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'o.date_add >= "' . pSQL($filters['date_from']) . ' 00:00:00"';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'o.date_add <= "' . pSQL($filters['date_to']) . ' 23:59:59"';
        }

        if (!empty($filters['expired_only'])) {
            $where[] = 'o.expire_date < NOW()';
            $where[] = 'o.status NOT IN ("' . self::STATUS_EXPIRED . '", "' . self::STATUS_CANCELLED . '", "' . self::STATUS_CONVERTED_TO_ORDER . '")';
        }

        $whereSql = '';
        if ($where) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'offer o
            LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer = o.id_customer
            ' . $whereSql
        );
    }

    /**
     * Generate a unique reference number.
     *
     * @return string
     */
    public static function generateReference()
    {
        $prefix = 'OFF';
        $year = date('Y');
        $random = strtoupper(Tools::passwdGen(6));
        $reference = $prefix . '-' . $year . '-' . $random;

        while (self::getByReference($reference)) {
            $random = strtoupper(Tools::passwdGen(6));
            $reference = $prefix . '-' . $year . '-' . $random;
        }

        return $reference;
    }

    /**
     * Update status and optionally set expire_date.
     *
     * @param int $id_offer
     * @param string $status
     * @param string|null $expire_date
     * @return bool
     */
    public static function updateStatus($id_offer, $status, $expire_date = null)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'offer
                SET status = "' . pSQL($status) . '",
                    date_upd = NOW()';

        if ($expire_date !== null) {
            $sql .= ', expire_date = "' . pSQL($expire_date) . '"';
        }

        $sql .= ' WHERE id_offer = ' . (int) $id_offer;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Update total_products count from child offer_product rows.
     *
     * @param int $id_offer
     * @return bool
     */
    public static function recalculateTotals($id_offer)
    {
        $count = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'offer_product WHERE id_offer = ' . (int) $id_offer
        );

        return Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'offer
             SET total_products = ' . $count . ', date_upd = NOW()
             WHERE id_offer = ' . (int) $id_offer
        );
    }

    /**
     * Check if offer is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        if (empty($this->expire_date)) {
            return false;
        }

        return strtotime($this->expire_date) < time();
    }

    /**
     * Check if an offer belongs to a customer or guest email.
     *
     * @param int $id_customer
     * @param string $guest_email
     * @return bool
     */
    public function belongsTo($id_customer, $guest_email = '')
    {
        if ($this->id_customer && (int) $this->id_customer === (int) $id_customer) {
            return true;
        }

        if (!$this->id_customer && $guest_email && $this->guest_email === $guest_email) {
            return true;
        }

        return false;
    }
}
