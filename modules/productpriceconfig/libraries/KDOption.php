<?php

/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class KDOption extends ObjectModel
{
	public $weight;

	public $thickness;
	public $label;
	public $price;
	public $active;
	public $position;
	public $id_variable;


	public static $definition = array(
		'table' => 'option',
		'primary' => 'id_option',
		'multilang' => true,
		'fields' => array(
			'id_variable' =>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'weight' =>	array('type' => self::TYPE_FLOAT, 'lang' => false, 'validate' => 'isPrice', 'required' => false),
			'thickness' =>	array('type' => self::TYPE_FLOAT, 'lang' => false, 'validate' => 'isPrice', 'required' => false),
			'price' =>                array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false),
			'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'active' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'label' =>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255, 'required' => true),
		)
	);

	public	function __construct($id_option = null, $id_lang = null)
	{
		parent::__construct($id_option, $id_lang);
	}

	public function delete()
	{
		Db::getInstance()->delete('product_option', ' id_option = ' . (int)$this->id);
		$sql = true;
		$sql &= parent::delete();
		return $sql;
	}



	public function getOptions()
	{
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			'
				SELECT  a.`id_option`, a.`price` , a.`weight`, a.`thickness`, b.`label`
				FROM ' . _DB_PREFIX_ . 'option a
				LEFT JOIN ' . _DB_PREFIX_ . 'option_lang b ON (a.id_option = b.id_option)
				WHERE b.id_lang = ' . (int)$lang->id . '
				ORDERBY a.`position`'
		);
	}

	static public function getOptionsIDs()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			'
				SELECT  `id_option`
				FROM ' . _DB_PREFIX_ . 'option'
		);
	}

	public function optionExists($id_option)
	{
		$req = 'SELECT `id_option`
				FROM `' . _DB_PREFIX_ . 'option`
				WHERE `id_option` = ' . (int)$id_option;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

		return ($row);
	}

	public function getStages()
	{
		if (!Validate::isLoadedObject($this)) {
			return array();
		}

		$result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'option_price WHERE id_option = ' . (int)$this->id);
		return $result;
	}

	public function getPriceByQty($qty)
	{
		if (!Validate::isLoadedObject($this)) {
			return array();
		}

		$res = array();

		$result = Db::getInstance()->executeS('SELECT from_quantity, price  FROM ' . _DB_PREFIX_ . 'option_price WHERE id_option = ' . (int)$this->id . ' AND  from_quantity <=' . $qty);
		if (count($result)) {
			foreach ($result as $value) {
				$res[$value['price']] = $value['from_quantity'];
			}
			$val = max($res);
			$option_price = array_search($val, $res);
		} else {
			$option_price = $this->price;
		}

		return $option_price;
	}

	public function updatePosition2($direction, $position)
	{
		$id_variable = (int) $this->id_variable;

		$db = Db::getInstance();
		$sql = '
			SELECT a.`id_option`, a.`position`, a.`id_variable`
			FROM `' . _DB_PREFIX_ . 'option` a
			WHERE a.`id_variable` = ' . (int) $id_variable . '
			ORDER BY a.`position` ASC';
		$res = $db->executeS($sql);

		if (!$res) {
			return false;
		}

		$movedOption = null;
		foreach ($res as $option) {
			if ((int) $option['id_option'] == (int) $this->id) {
				$movedOption = $option;
				break;
			}
		}

		if ($movedOption === null || $position === null) {
			return false;
		}

		$direction = ($position < $movedOption['position']) ? 1 : 0;

		$sql1 = 'UPDATE `' . _DB_PREFIX_ . 'option`
			SET `position` = `position` ' . ($direction ? '-1' : '+1') . '
			WHERE `position` ' . ($direction ? '> ' : '< ') . (int) $movedOption['position'] . '
			AND `position` ' . ($direction ? '<= ' : '>= ') . (int) $position . '
			AND `id_variable` = ' . (int) $movedOption['id_variable'];

		$sql2 = 'UPDATE `' . _DB_PREFIX_ . 'option`
			SET `position` = ' . (int) $position . '
			WHERE `id_option` = ' . (int) $movedOption['id_option'] . '
			AND `id_variable` = ' . (int) $movedOption['id_variable'];



		return $db->execute($sql1) && $db->execute($sql2);
	}
	public function updatePosition($direction, $position)
	{
		// $id_variable = (int) Tools::getValue('id_variable', $this->id_variable);
		$id_variable = (int) $this->id_variable;

		$db = Db::getInstance();
		$sql = '
			SELECT a.`id_option`, a.`position`, a.`id_variable`
			FROM `' . _DB_PREFIX_ . 'option` a
			WHERE a.`id_variable` = ' . (int) $id_variable . '
			ORDER BY a.`position` ASC';
		$res = $db->executeS($sql);

		if (!$res) {
			return false;
		}

		$movedOption = null;
		foreach ($res as $option) {
			if ((int) $option['id_option'] == (int) $this->id) {
				$movedOption = $option;
				break;
			}
		}

		if ($movedOption === null || $position === null) {
			return false;
		}
		$sql1 = 'UPDATE `' . _DB_PREFIX_ . 'option`
		SET `position` = `position` ' . ($direction ? '- 1' : '+ 1') . '
		WHERE `position` ' . ($direction ? '> ' : '< ') . (int) $movedOption['position'] . '
		AND `position` ' . ($direction ? '<= ' : '>= ') . (int) $position . '
		AND `id_variable` = ' . (int) $movedOption['id_variable'];

		$sql2 = 'UPDATE `' . _DB_PREFIX_ . 'option`
		SET `position` = ' . (int) $position . '
		WHERE `id_option` = ' . (int) $movedOption['id_option'] . '
		AND `id_variable` = ' . (int) $movedOption['id_variable'];
		
		return
			Db::getInstance()->execute($sql1) &&
			Db::getInstance()->execute($sql2);
	}


	public function cleanPositions($idVariable, $useLastOption = true)
	{
		Db::getInstance()->execute('SET @i = -1', false);
		$sql = 'UPDATE `' . _DB_PREFIX_ . 'option` SET `position` = @i:=@i+1 WHERE';

		if ($useLastAttribute) {
			$sql .= ' `id_option` != ' . (int) $this->id . ' AND';
		}

		$sql .= ' `id_variable` = ' . (int) $idVariable . ' ORDER BY `position` ASC';

		return Db::getInstance()->execute($sql);
	}

	/**
	 * get highest position.
	 *
	 * Get the highest attribute position from a group attribute
	 *
	 * @param int $idAttributeGroup AttributeGroup ID
	 *
	 * @return int $position Position
	 * @todo: Shouldn't this be called getHighestPosition instead?
	 */
	public static function getHigherPosition($idVariable)
	{
		$sql = 'SELECT MAX(`position`)
				FROM `' . _DB_PREFIX_ . 'option`
				WHERE id_variable = ' . (int) $idVariable;

		$position = Db::getInstance()->getValue($sql);

		return (is_numeric($position)) ? $position : -1;
	}
}
