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

class KDProductVariable extends ObjectModel
{
    public $id;
    /** @var integer*/
    public $id_product;
    /** @var integer*/
    public $id_variable;

    public $id_variable_tooltip;
    
    public $formula_name;
    /** @var integer */
    public $active;
    /** @var string*/
    public $options;
    /** @var string*/
    /** @var integer*/
    public $name;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'     => 'product_variable',
        'primary'   => 'id_product_variable',
        'multilang' => true,
        'fields'    => array(
            'id_product'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_variable'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_variable_tooltip' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'active'              => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'options'             => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 123255),
            'formula_name'        => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),
            'default_option' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'minimum' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'maximum' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'multiplier' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            
            

            'name'                => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 1000),
					
        ),
    );

    public static function deleteByProduct($id_product)
    {
        $variables = self::getAll($id_product);
        $res = Db::getInstance()->delete('product_variable', 'id_product = '.(int)$id_product);
        
        return $res;
    }

    public function delete()
    {             
        $res = parent::delete();
       
        return $res;
    }
    public static function hasVariables($id_product)
    {
        if(!$id_product)
            return false;

        return Db::getInstance()->getValue('
            SELECT count(0) 
            FROM `'._DB_PREFIX_.'id_product_variable`
            WHERE id_product='.$id_product
        );
    }


    public static function getAll($id_product, $id_lang=0, $active=0)
    {
        $result = Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'product_variable`
            WHERE `id_product`='.(int)$id_product.($active ? ' AND `active`=1 ' : '').'
            
            ');
            
        return $result;
    }
    
}
?>
