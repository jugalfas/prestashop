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

class KDQuoteProduct extends ObjectModel
{
    public $id;
    /** @var integer*/
    public $id_quote;
    /** @var integer*/
    public $id_product;

    public $id_product_attribute;

    public $file;
    /** @var integer */
    public $status;
    /** @var string*/
    public $details;
    /** @var string*/
    /** @var integer*/
    public $quantity;
    public $quantity2;
    public $quantity3;

    public $production;
    public $production2;
    public $production3;

    public $price;
    public $price2;
    public $price3;


    public $weight;
    public $approved_quantity;
    public $artwork;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'     => 'quote_product',
        'primary'   => 'id_quote_product',
        'multilang' => false,
        'fields'    => array(
            'id_quote'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_product'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'status'              => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'details'             => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 123255),
            'file'                => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 1500),
            'artwork'             => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 500),

            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'quantity2' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'quantity3' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),

            'production' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'production2' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'production3' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),

            'price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price2' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price3' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),

            'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'approved_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),

        ),
    );

    public static function deleteByQuote($id_quote)
    {
        //$variables = self::getAll($id_quote);
        $res = Db::getInstance()->delete('quote_product', 'id_quote = ' . (int)$id_quote);

        return $res;
    }

    public function delete()
    {
        $res = parent::delete();

        return $res;
    }
    public static function hasProduct($id_quote)
    {
        if (!$id_quote)
            return false;

        return Db::getInstance()->getValue(
            '
            SELECT count(0) 
            FROM `' . _DB_PREFIX_ . 'id_quote_product`
            WHERE id_quote=' . $id_quote
        );
    }


    public static function getAll($id_quote, $status = 0)
    {
        $result = Db::getInstance()->executeS('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_product`
            WHERE `id_quote`=' . (int)$id_quote . ($status ? ' AND `status`= ' . $status : '') . '
            
            ');

        return $result;
    }

    public static function getSingleQuoteProductsDetailsByQuoteId($id_quote, $id_quote_product)
    {
        $result = Db::getInstance()->getRow(
            '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_product`
            WHERE `id_quote`=' . (int)$id_quote . ' AND `id_quote_product`=' . (int)$id_quote_product
        );

        $configuration = json_decode($result['details'], true);

        $product = new Product($result['id_product'], false, Context::getContext()->language->id);

        $quote['additional_information'] = $configuration['additional_information'];
        foreach ($configuration as $key => &$config) {
            if (isset($config['additional_information'])) {
                unset($configuration[$key]);
            }
        }
        $quote['configuration'] = $configuration;
        $quote['product_id'] = $result['id_product'];
        $quote['price'] = $result['price'];
        if (Validate::isLoadedObject($product)) {
            $image_url = "";

            $cover = Image::getCover($result['id_product']);
            if ($cover) {
                $image_id = $cover['id_image'];
                $image_url = Context::getContext()->link->getImageLink($product->link_rewrite, $image_id, 'home_default');
            }

            $quote['product_image'] = $image_url;
            $quote['product_name'] = $product->name;
        }
        return $quote;
    }
    
    public static function getQuoteProductsDetailsByQuoteId($id_quote)
    {
        $result = Db::getInstance()->executeS(
            '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_product`
            WHERE `id_quote`=' . (int)$id_quote
        );

        $data = [];
        foreach ($result as $key => &$quote) {
            $configuration = json_decode($quote['details'], true);
    
            $product = new Product($quote['id_product'], false, Context::getContext()->language->id);
    
            $quote_data = [];
            $quote_data['additional_information'] = $configuration['additional_information'];
            foreach ($configuration as $key => &$config) {
                if (isset($config['additional_information'])) {
                    unset($configuration[$key]);
                }
            }
            $quote_data['configuration'] = $configuration;
            $quote_data['product_id'] = $quote['id_product'];
            if (Validate::isLoadedObject($product)) {
                $image_url = "";
    
                $cover = Image::getCover($quote['id_product']);
                if ($cover) {
                    $image_id = $cover['id_image'];
                    $image_url = Context::getContext()->link->getImageLink($product->link_rewrite, $image_id, 'home_default');
                }
    
                $quote_data['product_image'] = $image_url;
                $quote_data['product_name'] = $product->name;
            }

            $files = explode(',', $quote['file']);
            
            $file_urls = [];
            foreach ($files as $file_name) {
                if ($file_name != "") {
                    $file_urls[] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/uploads/' . $file_name;
                }
            }
            if (empty($file_urls)) {
                // Default image if no files exist
                $file_urls[] = "https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png";
            }
            $quote_data['images'] = $file_urls;

            $data[] = $quote_data;
        }

        return $data;
    }
}
