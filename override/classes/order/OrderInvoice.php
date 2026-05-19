<?php
/**
 * Clean override for OrderInvoice
 * Removes gwadvancedinvoice dependencies
 * Compatible with PrestaShop 1.7 / 8.x
 */

class OrderInvoice extends OrderInvoiceCore
{
    public function getInvoiceNumberFormatted($id_lang, $id_shop = null)
    {
        return parent::getInvoiceNumberFormatted($id_lang, $id_shop);
    }
}
