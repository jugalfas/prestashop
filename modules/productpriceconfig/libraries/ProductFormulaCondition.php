<?php
/**
 * ProductFormulaCondition - ObjectModel for Formula Conditions.
 *
 * Allows administrators to create conditional surcharges (percentage or fixed)
 * that are automatically applied during price calculation, without editing
 * the pricing formula manually.
 *
 * Table: ps_product_formula_condition
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductFormulaCondition extends ObjectModel
{
    /** @var int */
    public $id_formula_condition;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_variable;

    /** @var int id_option (0 for non-select variables) */
    public $id_option;

    /** @var string surcharge_type: 'percentage' or 'fixed' */
    public $surcharge_type;

    /** @var float */
    public $amount;

    /** @var string apply_type: 'total' or 'per_unit' */
    public $apply_type;

    /** @var string */
    public $notes;

    /** @var int */
    public $active = 1;

    /** @var int */
    public $position = 0;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = array(
        'table' => 'product_formula_condition',
        'primary' => 'id_formula_condition',
        'multilang' => false,
        'fields' => array(
            'id_product'      => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'id_variable'     => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'id_option'       => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'surcharge_type'  => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'values' => array('percentage', 'fixed')),
            'amount'          => array('type' => self::TYPE_FLOAT,  'validate' => 'isFloat', 'required' => true),
            'apply_type'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'values' => array('total', 'per_unit')),
            'notes'           => array('type' => self::TYPE_HTML,    'validate' => 'isCleanHtml', 'required' => false),
            'active'          => array('type' => self::TYPE_BOOL,   'validate' => 'isBool', 'required' => false),
            'position'        => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'date_add'        => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
            'date_upd'        => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
        ),
    );

    /**
     * Get all active conditions for a product, ordered by position.
     *
     * @param int $id_product
     * @return array
     */
    public static function getActiveConditions($id_product)
    {
        $sql = '
            SELECT *
            FROM ' . _DB_PREFIX_ . 'product_formula_condition
            WHERE id_product = ' . (int) $id_product . '
            AND active = 1
            ORDER BY position ASC, id_formula_condition ASC
        ';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Get all conditions for a product (including inactive), ordered by position.
     *
     * @param int $id_product
     * @return array
     */
    public static function getAllConditions($id_product)
    {
        $sql = '
            SELECT *
            FROM ' . _DB_PREFIX_ . 'product_formula_condition
            WHERE id_product = ' . (int) $id_product . '
            ORDER BY position ASC, id_formula_condition ASC
        ';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Get a single condition by ID.
     *
     * @param int $id_formula_condition
     * @return array|false
     */
    public static function getCondition($id_formula_condition)
    {
        $sql = '
            SELECT *
            FROM ' . _DB_PREFIX_ . 'product_formula_condition
            WHERE id_formula_condition = ' . (int) $id_formula_condition
        ;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Toggle active status for a condition.
     *
     * @param int $id_formula_condition
     * @param int $active (0 or 1)
     * @return bool
     */
    public static function FCtoggleStatus($id_formula_condition, $active)
    {
        return Db::getInstance()->execute('
            UPDATE ' . _DB_PREFIX_ . 'product_formula_condition
            SET active = ' . (int) $active . ', date_upd = NOW()
            WHERE id_formula_condition = ' . (int) $id_formula_condition
        );
    }
}
