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
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class KDQuote extends ObjectModel
{
    public $id;
    /** @var integer*/
    public $id_customer;
    /** @var integer*/
    public $id_order;

    public $id_lang;

    public $id_cart;

    public $id_currency;

    public $title;

    public $email;

    public $allow_partial;

    public $current_status;

    public $note;

    public $date_add;

    public $date_upd;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'     => 'quote_request',
        'primary'   => 'id_quote',
        'multilang' => false,
        'fields'    => array(
            'id_customer'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_order'           => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_lang'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_cart'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_currency'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'allow_partial' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'current_status'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'title' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 1000),
            'email' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 1000),
            'note' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 10000),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

        ),
    );

    public static function deleteByCustomerID($id_customer)
    {
        $id_customer = (int)$id_customer;
        if (!$id_customer)
            return false;
        $id_quote = self::getQuoteIdByEmail($id_customer);
        if (!$id_quote)
            return false;
        return self::deleteById($id_quote);
    }
    public function checkExists($id_quote)
    {
        $sql = 'SELECT COUNT(0)
				FROM `' . _DB_PREFIX_ . 'quote_request`
				WHERE `id_quote` = ' . (int)$id_quote . '
				' . ($this->id ? 'AND id_quote !=' . $this->id : '');

        return Db::getInstance()->getValue($sql);
    }
    public static function getByCustomerId($id_customer = 0)
    {
        return Db::getInstance()->getRow(
            '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `id_customer`=' . (int)$id_customer
        );
    }
    public static function getQuotesDetailsByCustomerEmail($email = null)
    {
        $id_lang = Context::getContext()->language->id;  // Get the current language ID

        $quoteData = Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `email` = "' . pSQL($email) . '" ORDER BY id_quote DESC
        ');

        $statusMapping = array(
            0 => 'Waiting for price',
            1 => 'Quotation',
            2 => 'Approved',
            3 => 'Rejected',
            4 => 'Ordered'
        );

        foreach ($quoteData as &$quote) {
            $quote['status_text'] = isset($statusMapping[$quote['current_status']]) ? $statusMapping[$quote['current_status']] : 'Unknown Status';
            $quote['products'] = Db::getInstance()->executeS('
                SELECT qp.*, p.id_product, pl.name as product_name, i.id_image
                FROM `' . _DB_PREFIX_ . 'quote_product` qp
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = qp.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product AND pl.id_lang = ' . (int)$id_lang . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.id_product = p.id_product AND i.cover = 1
                WHERE qp.`id_quote` = ' . (int)$quote['id_quote'] . '
                ORDER BY qp.`id_quote_product` ASC
            ');

            // Add image link and product name for each product
            foreach ($quote['products'] as &$product) {
                // Construct image link
                if (!empty($product['id_image'])) {
                    $product['image_link'] = Context::getContext()->link->getImageLink(
                        'product',
                        $product['id_product'] . '-' . $product['id_image'],
                        'home_default' // You can replace this with the image size you need (e.g., 'large_default')
                    );
                } else {
                    $product['image_link'] = null; // No image found, set to null
                }

                // Include the product name
                if (!empty($product['product_name'])) {
                    $product['product_title'] = $product['product_name'];
                } else {
                    $product['product_title'] = 'Unnamed Product'; // Default if no name is found
                }
            }
        }

        return $quoteData;
    }
    public static function getQuotesDetailsByCustomerId($id = 0)
    {
        $priceFormatter = new PriceFormatter();
        $id_lang = Context::getContext()->language->id;  // Get the current language ID

        $quoteData = Db::getInstance()->executeS('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `id_customer` = "' . pSQL($id) . '" ORDER BY id_quote DESC
        ');

        $statusMapping = array(
            0 => 'Waiting for price',
            1 => 'Quotation',
            2 => 'Approved',
            3 => 'Rejected',
            4 => 'Ordered'
        );

        foreach ($quoteData as &$quote) {
            $quote['status_text'] = isset($statusMapping[$quote['current_status']]) ? $statusMapping[$quote['current_status']] : 'Unknown Status';
            $quote['products'] = Db::getInstance()->executeS('
                SELECT qp.*, p.id_product, pl.name as product_name, i.id_image
                FROM `' . _DB_PREFIX_ . 'quote_product` qp
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = qp.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product AND pl.id_lang = ' . (int)$id_lang . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.id_product = p.id_product AND i.cover = 1
                WHERE qp.`id_quote` = ' . (int)$quote['id_quote'] . '
                ORDER BY qp.`id_quote_product` ASC
            ');

            // Add image link and product name for each product
            foreach ($quote['products'] as &$product) {
                // Construct image link
                if (!empty($product['id_image'])) {
                    $product['image_link'] = Context::getContext()->link->getImageLink(
                        'product',
                        $product['id_product'] . '-' . $product['id_image'],
                        'home_default' // You can replace this with the image size you need (e.g., 'large_default')
                    );
                } else {
                    $product['image_link'] = null; // No image found, set to null
                }

                // Include the product name
                if (!empty($product['product_name'])) {
                    $product['product_title'] = $product['product_name'];
                } else {
                    $product['product_title'] = 'Unnamed Product'; // Default if no name is found
                }
                if($product['price'] != 0.0000){
                    $product['price'] = $priceFormatter->format($product['price']);
                }
                
                $files = explode(',', $product['file']);
                
                $file = isset($files[0]) && $files[0] != "" ? Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/uploads/' . $files[0] : "https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png";
                $product['file'] = $file;
            }
        }

        return $quoteData;
    }

    public static function getQuoteIdByEmail($email = '')
    {
        return Db::getInstance()->getValue(
            '
            SELECT id_quote
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `email`=' . $email
        );
    }
    public static function getEmailByQuoteId($id_quote = 0)
    {
        return Db::getInstance()->getValue(
            '
            SELECT email
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `id_quote`=' . (int)$id_quote
        );
    }
    public static function getProSetting($id_product = 0, $id_lang = 0)
    {
        return Db::getInstance()->executeS('
            SELECT vs.*
            FROM `' . _DB_PREFIX_ . 'quote_request` vs
            WHERE vs.id_product=' . (int)$id_product);
    }
    public static function getAll($latest = false)
    {
        $id_lang = (int)Context::getContext()->language->id;
        $id_shop = (int)Context::getContext()->shop->id;
        $latest = $latest ? ' ORDER BY pv.id_quote DESC' : '';
        return Db::getInstance()->executeS('
			SELECT pv.* FROM `' . _DB_PREFIX_ . 'quote_request` pv ' . $latest . '
        ');
    }
    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST as $key => $value)
            if (key_exists($key, $this) && $key != 'id_' . $this->table && !isset($_FILES[$key]))
                $this->{$key} = $value;

        /* Multilingual fields */
        if (sizeof($this->fieldsValidateLang)) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                foreach ($this->fieldsValidateLang as $field => $validation)
                    if (isset($_POST[$field . '_' . (int)($language['id_lang'])]) && !isset($_FILES[$field . '_' . (int)($language['id_lang'])]))
                        $this->{$field}[(int)($language['id_lang'])] = $_POST[$field . '_' . (int)($language['id_lang'])];
        }
    }

    public static function getQuoteDetailsByQuoteId($id = 0)
    {
        $priceFormatter = new PriceFormatter();
        $id_lang = Context::getContext()->language->id;  // Get the current language ID

        $quoteData = Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'quote_request`
            WHERE `id_quote` = "' . pSQL($id) . '"
        ');

        $statusMapping = array(
            0 => 'Waiting for price',
            1 => 'Quotation',
            2 => 'Approved',
            3 => 'Rejected',
            4 => 'Ordered'
        );

        $quoteData['status_text'] = isset($statusMapping[$quoteData['current_status']]) ? $statusMapping[$quoteData['current_status']] : 'Unknown Status';
        $quoteData['products'] = Db::getInstance()->executeS('
            SELECT qp.*, p.id_product, pl.name as product_name, i.id_image
            FROM `' . _DB_PREFIX_ . 'quote_product` qp
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = qp.id_product
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product AND pl.id_lang = ' . (int)$id_lang . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.id_product = p.id_product AND i.cover = 1
            WHERE qp.`id_quote` = ' . (int)$id . '
            ORDER BY qp.`id_quote_product` ASC
        ');

        // Add image link and product name for each product
        foreach ($quoteData['products'] as &$product) {
            // Construct image link
            if (!empty($product['id_image'])) {
                $product['image_link'] = Context::getContext()->link->getImageLink(
                    'product',
                    $product['id_product'] . '-' . $product['id_image'],
                    'home_default' // You can replace this with the image size you need (e.g., 'large_default')
                );
            } else {
                $product['image_link'] = null; // No image found, set to null
            }

            // Include the product name
            if (!empty($product['product_name'])) {
                $product['product_title'] = $product['product_name'];
            } else {
                $product['product_title'] = 'Unnamed Product'; // Default if no name is found
            }

            if($product['price'] != 0.0000){
                $product['price'] = $priceFormatter->format($product['price']);
            }
        }

        return $quoteData;
    }

    public static function getProductSettingByProductId($id_product = 0)
    {
        return Db::getInstance()->getRow(
            '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'product_setting`
            WHERE `id_product`=' . (int)$id_product
        );
    }

    public static function get_product_variable_id_from_variable_id($id_variable = 0, $id_product)
    {
        return Db::getInstance()->getValue(
            '
            SELECT id_product_variable
            FROM `' . _DB_PREFIX_ . 'product_variable`
            WHERE `id_variable`=' . (int)$id_variable .' AND `id_product`=' . (int)$id_product
        );
    }
}
