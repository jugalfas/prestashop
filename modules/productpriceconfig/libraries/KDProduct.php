<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SurveyCore extends ObjectModel
{
    /** @var string Name */
    public $name;
    public $position;

    /** @var bool Status */
    public $active = true;

    /** @var bool Status */
    public $date_expire = 0;

    /** @var bool True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public $groupBox;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'survey',
        'primary' => 'id_survey',
        'multilang' => true,
        'fields' => array(
            'position' =>    array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'active' =>                    array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'deleted' =>                    array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
          //  'date_expire' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_add' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),

            /* Lang fields */
            'name' =>        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
        ),
    );


    protected $webserviceParameters = array(
        'objectsNodeName' => 'customer_surveys',
        'objectNodeName' => 'customer_survey',
        'fields' => array(),
    );

    protected static $_survey_customers = array();

    /**
     * Get a survey data for a given id_survey and id_lang
     *
     * @param int $id_lang Language id
     * @param int $id_survey survey id
     * @return array Array with survey's data
     */
    public static function getSurvey($id_lang, $id_survey)
    {
        return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'survey` f
			LEFT JOIN `'._DB_PREFIX_.'survey_lang` fl
				ON ( f.`id_survey` = fl.`id_survey` AND fl.`id_lang` = '.(int)$id_lang.')
			WHERE f.`id_survey` = '.(int)$id_survey
        );
    }

    /**
     * Get all surveys for a given language
     *
     * @param int $id_lang Language id
     * @return array Multiple arrays with survey's data
     */
    public static function getSurveys($id_lang, $with_shop = false)
    {
        return Db::getInstance()->executeS('
		SELECT DISTINCT f.id_survey, f.*, fl.*
		FROM `'._DB_PREFIX_.'survey` f
		'.($with_shop ? Shop::addSqlAssociation('survey', 'f') : '').'
		LEFT JOIN `'._DB_PREFIX_.'survey_lang` fl ON (f.`id_survey` = fl.`id_survey` AND fl.`id_lang` = '.(int)$id_lang.')
		ORDER BY f.`position` ASC');
    }

    /**
     * Delete several objects from database
     *
     * @param array $selection Array with items to delete
     * @return bool Deletion result
     */
    public function deleteSelection($selection)
    {
        /* Also delete Attributes */
        foreach ($selection as $value) {
            $obj = new Survey($value);
            if (!$obj->delete()) {
                return false;
            }
        }
        return true;
    }

    public function add($autodate = true, $nullValues = false)
    {
        if ($this->position <= 0) {
            $this->position = Survey::getHigherPosition() + 1;
        }

        $return = parent::add($autodate, true);

        $this->updateCustomer($this->groupBox);

        return $return;
    }

    public function delete()
    {
        /* Also delete related variables */
        Db::getInstance()->execute('
			DELETE
				`'._DB_PREFIX_.'survey_variable_lang`
			FROM
				`'._DB_PREFIX_.'survey_variable_lang`
				JOIN `'._DB_PREFIX_.'survey_variable`
					ON (`'._DB_PREFIX_.'survey_variable_lang`.id_survey_variable = `'._DB_PREFIX_.'survey_variable`.id_survey_variable)
			WHERE
				`'._DB_PREFIX_.'survey_variable`.`id_survey` = '.(int)$this->id.'
		');
        Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'survey_variable`
			WHERE `id_survey` = '.(int)$this->id
        );
        /* Also delete related users */
        Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'survey_customer`
			WHERE `id_survey` = '.(int)$this->id
        );

        $return = parent::delete();


        /* Reinitializing position */
        $this->cleanPositions();

        return $return;
    }

    public function update($nullValues = false)
    {
        $this->clearCache();

        $result = 1;
        $fields = $this->getFieldsLang();
        foreach ($fields as $field) {
            foreach (array_keys($field) as $key) {
                if (!Validate::isTableOrIdentifier($key)) {
                    die(Tools::displayError());
                }
            }

            $sql = 'SELECT `id_lang` FROM `'.pSQL(_DB_PREFIX_.$this->def['table']).'_lang`
					WHERE `'.$this->def['primary'].'` = '.(int)$this->id.'
						AND `id_lang` = '.(int)$field['id_lang'];
            $mode = Db::getInstance()->getRow($sql);
            $result &= (!$mode) ? Db::getInstance()->insert($this->def['table'].'_lang', $field) :
            Db::getInstance()->update(
                $this->def['table'].'_lang',
                $field,
                '`'.$this->def['primary'].'` = '.(int)$this->id.' AND `id_lang` = '.(int)$field['id_lang']
            );
        }

        if ($result) {
            $result &= parent::update($nullValues);

        }

        if (isset(Context::getContext()->controller) && Context::getContext()->controller->controller_type == 'admin') {
            $this->updateCustomer($this->groupBox);
        }

        return $result;
    }



    /**
    * Count number of surveys for a given language
    *
    * @param int $id_lang Language id
    * @return int Number of survey
    */
    public static function nbSurveys($id_lang)
    {
        return Db::getInstance()->getValue('
		SELECT COUNT(*) as nb
		FROM `'._DB_PREFIX_.'survey` ag
		LEFT JOIN `'._DB_PREFIX_.'survey_lang` agl
		ON (ag.`id_survey` = agl.`id_survey` AND `id_lang` = '.(int)$id_lang.')
		');
    }

    /**
     * Get customer survey number
     *
     * @param int $id_customer Customer id
     * @return array Customer survey number
     */
    public static function getCustomerNbSurveys($id_customer)
    {
        $sql = 'SELECT COUNT(`id_survey`) AS nb
        FROM `'._DB_PREFIX_.'survey_customer`
        WHERE `id_customer` = '.(int)$id_customer;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return isset($result['nb']) ? $result['nb'] : 0;
    }


    public static function getSurveysForComparison($list_ids_product, $id_lang)
    {
        if (!Survey::issurveyActive()) {
            return false;
        }

        $ids = '';
        foreach ($list_ids_product as $id) {
            $ids .= (int)$id.',';
        }

        $ids = rtrim($ids, ',');

        if (empty($ids)) {
            return false;
        }

        return Db::getInstance()->executeS('
			SELECT f.*, fl.*
			FROM `'._DB_PREFIX_.'survey` f
			LEFT JOIN `'._DB_PREFIX_.'survey_customer` fp
				ON f.`id_survey` = fp.`id_survey`
			LEFT JOIN `'._DB_PREFIX_.'survey_lang` fl
				ON f.`id_survey` = fl.`id_survey`
			WHERE fp.`id_product` IN ('.$ids.')
			AND `id_lang` = '.(int)$id_lang.'
			GROUP BY f.`id_survey`
			ORDER BY f.`position` ASC
		');
    }

    /**
     * This metohd is allow to know if a survey is used or active
     * @since 1.5.0.1
     * @return bool
     */
    public static function isSurveyActive()
    {
        return true;
    }

    /**
     * Move a survey
     * @param bool $way Up (1)  or Down (0)
     * @param int $position
     * @return bool Update result
     */
    public function updatePosition($way, $position, $id_survey = null)
    {
        if (!$res = Db::getInstance()->executeS('
			SELECT `position`, `id_survey`
			FROM `'._DB_PREFIX_.'survey`
			WHERE `id_survey` = '.(int)($id_survey ? $id_survey : $this->id).'
			ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $survey) {
            if ((int)$survey['id_survey'] == (int)$this->id) {
                $moved_survey = $survey;
            }
        }

        if (!isset($moved_survey) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'survey`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
                ? '> '.(int)$moved_survey['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$moved_survey['position'].' AND `position` >= '.(int)$position))
        && Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'survey`
			SET `position` = '.(int)$position.'
			WHERE `id_survey`='.(int)$moved_survey['id_survey']));
    }

    /**
     * Reorder survey position
     * Call it after deleting a survey.
     *
     * @return bool $return
     */
    public static function cleanPositions()
    {
        Db::getInstance()->execute('SET @i = -1', false);
        $sql = 'UPDATE `'._DB_PREFIX_.'survey` SET `position` = @i:=@i+1 ORDER BY `position` ASC';
        return (bool)Db::getInstance()->execute($sql);
    }

    /**
     * getHigherPosition
     *
     * Get the higher survey position
     *
     * @return int $position
     */
    public static function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`)
				FROM `'._DB_PREFIX_.'survey`';
        $position = DB::getInstance()->getValue($sql);
        return (is_numeric($position)) ? $position : - 1;
    }

    public function addSurveyToCustomer($id_customer)
    {

        $row = array('id_survey' => (int)$this->id, 'id_customer' => (int)$id_customer);
        Db::getInstance()->insert('survey_customer', $row);
    }

    public static function addSurveyCustomerImport($id_product, $id_feature, $id_feature_value)
    {
        return Db::getInstance()->execute('
      INSERT INTO `'._DB_PREFIX_.'survey_customer` (`id_survey`, `id_customer`)
      VALUES ('.(int)$id_survey.', '.(int)$id_customer.')'
        );
    }

    /**
     * Update customer customers associated to the object
     *
     * @param array $list customers
     */
    public function updateCustomer($list)
    {
        if ($list && !empty($list)) {
            $this->cleanCustomers();
            $this->addCustomers($list);
        }
    }

    public function cleanCustomers()
    {
    	return Db::getInstance()->delete('survey_customer', 'id_survey = '.(int)$this->id);
    }

    public function addCustomers($customers)
    {
        foreach ($customers as $customer) {
            $row = array('id_survey' => (int)$this->id, 'id_customer' => (int)$customer);
            Db::getInstance()->insert('survey_customer', $row, false, true, Db::INSERT_IGNORE);
        }
    }

    public static function getCustomersStatic($id_survey)
    {
      $result = Db::getInstance()->executeS('
			SELECT cg.`id_customer`
			FROM '._DB_PREFIX_.'survey_customer cg
			WHERE cg.`id_survey` = '.(int)$id_survey);

        return $result;
    }

    public function getCustomers()
    {
        return Survey::getCustomersStatic((int)$this->id);
    }


}
