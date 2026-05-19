<?php
/**
* History:
*
* 1.0 - First version
*
*  @author    Vincent MASSON <contact@coeos.pro>
*  @copyright Vincent MASSON <www.coeos.pro>
*  @license   http://www.coeos.pro/fr/content/3-conditions-generales-de-ventes
*/
class Address extends AddressCore
{
    /*
    * module: vatnumbercleaner
    * date: 2023-06-20 14:24:52
    * version: 1.5.12
    */
    public function validateController($htmlentities = true)
    {
        include_once(_PS_MODULE_DIR_ . 'vatnumbercleaner/vatnumbercleaner.php');
        $errors = parent::validateController($htmlentities);
        if (Tools::substr(_PS_VERSION_, 0, 3) == '1.6') {
            $verif = VNC::verificationVATNumber($this->vat_number, $this->id_country);
            $ok = VNC::verificationVATNumberLikeOkForANewAddress();
            if ($verif > 0 && !in_array($verif, $ok)) {
                $msg = VNC::msgVerification($verif);
                return array_merge($errors, array($msg));
            }
        }
        return $errors;
    }
}
