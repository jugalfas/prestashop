<?php
/**
 * OfferAttachment - ObjectModel for uploaded artwork files.
 *
 * Supports multiple files per offer product. Stores metadata only;
 * the actual file is saved on disk under the module's upload directory.
 *
 * Table: ps_offer_attachment
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferAttachment extends ObjectModel
{
    /** @var int */
    public $id_offer_attachment;

    /** @var int */
    public $id_offer_product;

    /** @var string */
    public $original_name;

    /** @var string */
    public $saved_name;

    /** @var string */
    public $extension;

    /** @var string */
    public $mime_type;

    /** @var int */
    public $size;

    /** @var int */
    public $width;

    /** @var int */
    public $height;

    /** @var int customer or employee ID */
    public $uploaded_by;

    /** @var string */
    public $date_add;

    public static $definition = array(
        'table' => 'offer_attachment',
        'primary' => 'id_offer_attachment',
        'multilang' => false,
        'fields' => array(
            'id_offer_product'  => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true),
            'original_name'     => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'saved_name'        => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'extension'         => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 20),
            'mime_type'         => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 100),
            'size'              => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'width'             => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'height'            => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => false),
            'uploaded_by'       => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => false),
            'date_add'          => array('type' => self::TYPE_DATE,   'validate' => 'isDate', 'required' => false),
        ),
    );

    /** @var array Allowed file extensions */
    public static $allowedExtensions = ['pdf', 'ai', 'eps', 'svg', 'png', 'jpg', 'jpeg'];

    /** @var array MIME types for allowed extensions */
    public static $allowedMimeTypes = [
        'pdf' => 'application/pdf',
        'ai'  => 'application/postscript',
        'eps' => 'application/postscript',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    /** @var int Max file size in bytes (default 20MB) */
    public static $maxFileSize = 20971520;

    /**
     * Get all attachments for an offer product.
     *
     * @param int $id_offer_product
     * @return array
     */
    public static function getByOfferProduct($id_offer_product)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'offer_attachment
                WHERE id_offer_product = ' . (int) $id_offer_product . '
                ORDER BY date_add ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Get a single attachment by ID.
     *
     * @param int $id_offer_attachment
     * @return array|false
     */
    public static function getById($id_offer_attachment)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'offer_attachment
             WHERE id_offer_attachment = ' . (int) $id_offer_attachment
        );
    }

    /**
     * Delete an attachment record and its file on disk.
     *
     * @param int $id_offer_attachment
     * @return bool
     */
    public static function deleteAttachment($id_offer_attachment)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'offer_attachment
             WHERE id_offer_attachment = ' . (int) $id_offer_attachment
        );

        if (!$row) {
            return false;
        }

        $uploadDir = self::getUploadDir();
        $filePath = $uploadDir . $row['saved_name'];

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'offer_attachment
             WHERE id_offer_attachment = ' . (int) $id_offer_attachment
        );
    }

    /**
     * Delete all attachments for an offer product.
     *
     * @param int $id_offer_product
     * @return bool
     */
    public static function deleteByOfferProduct($id_offer_product)
    {
        $attachments = self::getByOfferProduct($id_offer_product);
        $uploadDir = self::getUploadDir();

        foreach ($attachments as $att) {
            $filePath = $uploadDir . $att['saved_name'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'offer_attachment
             WHERE id_offer_product = ' . (int) $id_offer_product
        );
    }

    /**
     * Validate a file against allowed extensions, MIME types, and size.
     *
     * @param array $file $_FILES entry
     * @return array [valid => bool, error => string|null]
     */
    public static function validateFile($file)
    {
        if (empty($file) || !isset($file['name'])) {
            return ['valid' => false, 'error' => 'No file provided.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error code: ' . $file['error']];
        }

        if ($file['size'] > self::$maxFileSize) {
            return ['valid' => false, 'error' => 'File exceeds maximum size of 20MB.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            return ['valid' => false, 'error' => 'File type .' . $extension . ' is not allowed.'];
        }

        $expectedMime = self::$allowedMimeTypes[$extension] ?? '';
        $detectedMime = function_exists('mime_content_type')
            ? mime_content_type($file['tmp_name'])
            : $file['type'];

        if ($expectedMime && $detectedMime && $detectedMime !== $expectedMime) {
            return ['valid' => false, 'error' => 'MIME type mismatch. Expected ' . $expectedMime . ', got ' . $detectedMime . '.'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Get the upload directory path (with trailing slash).
     *
     * @return string
     */
    public static function getUploadDir()
    {
        $dir = _PS_UPLOAD_DIR_ . 'offer_attachments/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /**
     * Generate a safe saved filename.
     *
     * @param string $originalName
     * @return string
     */
    public static function generateSavedName($originalName)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return 'offer_' . date('Ymd_His') . '_' . Tools::passwdGen(8) . '.' . $extension;
    }
}
