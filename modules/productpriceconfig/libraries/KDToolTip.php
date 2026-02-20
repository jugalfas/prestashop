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

class KDToolTip extends ObjectModel
{
	public $id;
	public $label;
	public $text;
	

	public static $definition = array(
		'table' => 'variable_tooltip',
		'primary' => 'id_variable_tooltip',
		'multilang' => true,
		'fields' => array(
			'text' =>      array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => false),
			'label' =>	   array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 255, 'required' => true),
		)
	);

	public function getAllToolTips($id_lang)
    {
        
        $result = Db::getInstance()->executeS(
        'SELECT o.*, ol.* FROM '._DB_PREFIX_.'variable_tooltip o
        LEFT JOIN `'._DB_PREFIX_.'variable_tooltip_lang` ol ON (ol.`id_variable_tooltip`= o.`id_variable_tooltip`AND ol.`id_lang` = '.(int)$id_lang.' ) 
        ');
        return $result;
    }

	
}
