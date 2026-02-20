<?php

/**
 * Bulk Discount Manager Imorting and Creating
 *
 * @author    Vipul <krupaludev@icloud.com>
 * @version   1.0.0
 */


require_once(dirname(__FILE__) . '/krupaludev/Helpers.php');
require_once(dirname(__FILE__) . '/KDOption.php');

/**
 * Class KDVariable
 */
class KDVariable extends ObjectModel
{

    public $id;
    public $name;
    public $type;
    public $minimum;
    public $maximum;
    public $fixed_price;

    public $label;
    public $required = 0;
    public $active = 1;
    public $position;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'variable',
        'primary' => 'id_variable',
        'multilang' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 254),
            'type' => array('type' => self::TYPE_STRING, 'required' => true),
            'minimum' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'maximum' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'fixed_price' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),

            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'required' =>   array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' =>     array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            /* Lang fields */
            'label' =>        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),

        ),
    );



    public function getOptions()
    {
        if (!Validate::isLoadedObject($this)) {
            return array();
        }

        $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'option WHERE id_variable = ' . (int)$this->id);
        return $result;
    }

    public function getAllOptions($id_lang)
    {
        if (!Validate::isLoadedObject($this)) {
            return array();
        }

        $result = Db::getInstance()->executeS(
            'SELECT o.*, ol.* FROM ' . _DB_PREFIX_ . 'option o
        LEFT JOIN `' . _DB_PREFIX_ . 'option_lang` ol ON (ol.`id_option`= o.`id_option`AND ol.`id_lang` = ' . (int)$id_lang . ' ) 
         WHERE id_variable = ' . (int)$this->id
        );
        return $result;
    }


    public function getAllVariables($id_lang = false)
    {
        if (!$id_lang) {
            $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
            $id_lang = $lang->id;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
				SELECT  a.`id_variable`, a.`name`
				FROM ' . _DB_PREFIX_ . 'variable a
				LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang b ON (a.id_variable = b.id_variable)
				WHERE b.id_lang = ' . (int)$id_lang
        );
    }

    public function getVariables()
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
				SELECT  a.*, b.*
				FROM ' . _DB_PREFIX_ . 'variable a
				LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang b ON (a.id_variable = b.id_variable)
				WHERE b.id_lang = ' . (int)$lang->id
        );
    }


    public static function existsRefInDatabase($reference)
    {
        $row = Db::getInstance()->getRow('
  		SELECT `id_product`,`reference`
  		FROM `' . _DB_PREFIX_ . 'product` p
  		WHERE p.reference = "' . pSQL($reference) . '"');

        return isset($row['reference']) ? $row['id_product'] : false;
    }

    /**
     * Add position to current Category
     *
     * @param int      $position Position
     * @param int|null $idShop   Shop ID
     *
     * @return bool Indicates whether the position was successfully added
     */
    public function addPosition($position)
    {
        $return = true;
        $return &= Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'variable` (`id_variable`,`position`) VALUES
            (' . (int) $this->id . ', ' . (int) $position . ')
            ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);

        return $return;
    }



    public function updatePosition($direction, $position)
    {
        $idVariable = (int) Tools::getValue('id', $this->id);
        
        if (!$res = Db::getInstance()->executeS(
            'SELECT ag.`position`, ag.`id_variable`
        FROM `' . _DB_PREFIX_ . 'variable` ag
        WHERE ag.`id_variable` = ' . $idVariable . '
        ORDER BY ag.`position` ASC'
        )) {
            return false;
        }

        $movedVariable = null;
        foreach ($res as $variable) {
            if ((int) $variable['id_variable'] === $idVariable) {
                $movedVariable = $variable;
                break;
            }
        }

        if (!isset($movedVariable) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'variable`
        SET `position` = `position` ' . ($direction ? '- 1' : '+ 1') . '
        WHERE `position` '
                . ($direction
                    ? '> ' . (int) $movedVariable['position'] . ' AND `position` <= ' . (int) $position
                    : '< ' . (int) $movedVariable['position'] . ' AND `position` >= ' . (int) $position)
        ) && Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'variable`
        SET `position` = ' . (int) $position . '
        WHERE `id_variable`=' . (int) $movedVariable['id_variable']
        );
    }

    // public function updatePosition($direction, $position)
    // {
    //     if (!$res = Db::getInstance()->executeS(
    //         '
    // 		SELECT ag.`position`, ag.`id_variable`
    // 		FROM `' . _DB_PREFIX_ . 'variable` ag
    // 		WHERE ag.`id_variable` = ' . (int) Tools::getValue('id_variable', 1) . '
    // 		ORDER BY ag.`position` ASC'
    //     )) {
    //         return false;
    //     }

    //     foreach ($res as $variable) {
    //         if ((int) $variable['id_variable'] == (int) $this->id) {
    //             $movedVariable = $variable;
    //         }
    //     }

    //     if (!isset($movedVariable) || !isset($position)) {
    //         return false;
    //     }

    //     // < and > statements rather than BETWEEN operator
    //     // since BETWEEN is treated differently according to databases
    //     return Db::getInstance()->execute(
    //         '
    // 		UPDATE `' . _DB_PREFIX_ . 'variable`
    // 		SET `position`= `position` ' . ($direction ? '- 1' : '+ 1') . '
    // 		WHERE `position`
    // 		' . ($direction
    //             ? '> ' . (int) $movedVariable['position'] . ' AND `position` <= ' . (int) $position
    //             : '< ' . (int) $movedVariable['position'] . ' AND `position` >= ' . (int) $position)
    //     ) && Db::getInstance()->execute('
    // 		UPDATE `' . _DB_PREFIX_ . 'variable`
    // 		SET `position` = ' . (int) $position . '
    // 		WHERE `id_variable`=' . (int) $movedVariable['id_variable']);
    // }

    /**
     * cleanPositions keep order of category in $id_category_variable_set,
     * but remove duplicate position. Should not be used if positions
     * are clean at the beginning !
     *
     * @param mixed $idCategoryvariable_set
     *
     * @return bool true if succeed
     */

    public static function cleanPositions()
    {
        $return = true;
        Db::getInstance()->execute('SET @i = -1', false);
        $return = Db::getInstance()->execute(
            '
				UPDATE `' . _DB_PREFIX_ . 'variable`
				SET `position` = @i:=@i+1
				ORDER BY `position`'
        );

        return $return;
    }
    public static function getLastPosition($id_variable)
    {
        if ((int) Db::getInstance()->getValue('
				SELECT COUNT(c.`id_variable`)
				FROM `' . _DB_PREFIX_ . 'variable` c
				WHERE c.`id_variable` = ' . (int) $id_variable) === 1) {
            return 0;
        } else {
            return (1 + (int) Db::getInstance()->getValue('
				SELECT MAX(c.`position`)
				FROM `' . _DB_PREFIX_ . 'variable` c
				WHERE c.`id_variable` = ' . (int) $id_variable));
        }
    }

    public static function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'variable`';
        $position = Db::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }

    public function delete()
    {

        $optionIds = Db::getInstance()->executeS(
            '
            SELECT `id_option`
            FROM `' . _DB_PREFIX_ . 'option`
            WHERE `id_variable` = ' . (int) $this->id
        );
        /* Removing attributes to the found combinations */
        if (count($optionIds)) {
            $toRemove = [];
            foreach ($optionIds as $option) {
                $toRemove[] = (int) $option['id_option'];
            }

            if (!empty($toRemove) && Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'option`
                WHERE `id_option`
                    IN (' . implode(', ', $toRemove) . ')') === false) {
                return false;
            }
            /* Also delete related options */
            if (count($toRemove)) {
                if (
                    !Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'option_lang`
                WHERE `id_option`	IN (' . implode(',', $toRemove) . ')') ||
                    !Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'option` WHERE `id_variable` = ' . (int) $this->id)
                ) {
                    return false;
                }
            }
        }
        $productVariableIds = Db::getInstance()->executeS(
            '
            SELECT `id_product_variable`
            FROM `' . _DB_PREFIX_ . 'product_variable`
            WHERE `id_variable` = ' . (int) $this->id
        );
        if (count($productVariableIds)) {
            $toRemove = [];
            foreach ($productVariableIds as $product_var) {
                $toRemove[] = (int) $product_var['id_product_variable'];
            }

            if (!empty($toRemove) && Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'product_variable`
                WHERE `id_product_variable`
                    IN (' . implode(', ', $toRemove) . ')') === false) {
                return false;
            }
            /* Also delete related options */
            if (count($toRemove)) {
                if (!Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'product_variable_lang`
                WHERE `id_product_variable`	IN (' . implode(',', $toRemove) . ')')) {
                    return false;
                }
            }
        }
        $this->cleanPositions();

        $return = parent::delete();


        return $return;
    }
}
