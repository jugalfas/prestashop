<?php
/*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class KDProductSetting extends ObjectModel
{
	public $id;
    /** @var integer*/
    public $id_product;
    /** @var integer*/
    public $formula_price;

    public $formula_weight;

    public $formula_thickness;

    public $formula_shipping;

    public $baned_comb;

    public $tiered;

    public $variable_position;
    
    /**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table'     => 'product_setting',
		'primary'   => 'id_product_setting',
		'multilang' => false,
		'fields'    => array(
            'id_product'        =>array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'formula_price' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
		    'formula_weight' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
            'formula_thickness' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
            'formula_shipping' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
			'baned_comb' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
            'tiered' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
            'variable_position' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
					
        ),
	);
	
    public static function deleteByProductId($id_product)
    {
        $id_product = (int)$id_product;
        if(!$id_product)
            return false;
        $id_product_setting = self::getSettingIdByProductId($id_product);
        if(!$id_product_setting)
            return false;
        return self::deleteById($id_product_setting);
    }
	public function checkExists($id_product)
	{
		$sql = 'SELECT COUNT(0)
				FROM `'._DB_PREFIX_.'product_setting`
				WHERE `id_product` = '.(int)$id_product.'
				'.($this->id ? 'AND id_product_setting !='.$this->id : '');

		return Db::getInstance()->getValue($sql);
	}
    public static function getByProductId($id_product = 0)
    {
        return Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'product_setting`
            WHERE `id_product`='.(int)$id_product
            );
    }
    public static function getSettingIdByProductId($id_product = 0)
    {
        return Db::getInstance()->getValue('
            SELECT id_product_setting
            FROM `'._DB_PREFIX_.'product_setting`
            WHERE `id_product`='.(int)$id_product
            );
    }
    public static function getProductIdBySettingId($id_product_setting = 0)
    {
        return Db::getInstance()->getValue('
            SELECT id_product
            FROM `'._DB_PREFIX_.'product_setting`
            WHERE `id_product_setting`='.(int)$id_product_setting
            );
    }
    public static function getProSetting($id_product = 0, $id_lang=0)
    {
        return Db::getInstance()->executeS('
            SELECT vs.*
            FROM `'._DB_PREFIX_.'product_setting` vs
            WHERE vs.id_product='.(int)$id_product);
    }
	public static function getAll()
	{
		$id_lang = (int)Context::getContext()->language->id;
		$id_shop = (int)Context::getContext()->shop->id;
		return Db::getInstance()->executeS('
			SELECT pl.name, pv.* FROM `'._DB_PREFIX_.'product_setting` pv
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON pv.id_product = pl.id_product AND pl.id_lang='.$id_lang.' AND pl.id_shop='.$id_shop.'
			
			');
	}
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (key_exists($key, $this) && $key != 'id_'.$this->table && !isset($_FILES[$key]))
				$this->{$key} = $value;

        /* Multilingual fields */
        if (sizeof($this->fieldsValidateLang))
        {
            $languages = Language::getLanguages(false);
            foreach ($languages AS $language)
                foreach ($this->fieldsValidateLang AS $field => $validation)
					if (isset($_POST[$field.'_'.(int)($language['id_lang'])]) && !isset($_FILES[$field.'_'.(int)($language['id_lang'])]))
                        $this->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];
        }
	}
}

?>
