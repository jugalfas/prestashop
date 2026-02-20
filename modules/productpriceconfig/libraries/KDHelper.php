<?php
/**
 * Bulk Orders Imorting and uploading
 *
 * @author    Krupaludev <krupaludev@icloud.com>
 * @version   1.0.0
 */

/**
 * Class KDHelper
 */
class KDHelper
{
    public static function checkZipCode($zip_code, $zip_code_format, $iso_code)
    {
        $iso_code_wildcard = preg_replace('/(.)/i', '([$1_%*]|)', $iso_code);

        $zip_regexp = '/^' . $zip_code_format . '$/ui';
        $zip_regexp = str_replace(' ', '( |)', $zip_regexp);
        $zip_regexp = str_replace('-', '(-|)', $zip_regexp);
        $zip_regexp = str_replace('N', '([0-9_%*]|)', $zip_regexp);
        $zip_regexp = str_replace('L', '([a-zA-Z_%*]|)', $zip_regexp);
        $zip_regexp = str_replace('C', $iso_code_wildcard, $zip_regexp);

        return (bool)preg_match($zip_regexp, $zip_code);
    }
}
