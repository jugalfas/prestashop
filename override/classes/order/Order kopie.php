<?php
class Order extends OrderCore
{
    public static function generateReference()
    {
        $last_id = Db::getInstance()->getValue('
        SELECT MAX(id_order)
        FROM '._DB_PREFIX_.'orders');
        $final_no = (int)$last_id + 1000;
        return str_pad((int)$final_no + 1, 9, '000000000', STR_PAD_LEFT);
    }
    /*
    * module: gwadvancedinvoice
    * date: 2025-07-10 16:04:51
    * version: 1.3.0
    */
    public static function setLastInvoiceNumber($order_invoice_id, $id_shop)
    {
        if (!$order_invoice_id) {
            return false;
        }
        if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice'))
        {
            $invoiceObj = Module::getInstanceByName('gwadvancedinvoice');
            $object = new OrderInvoice($order_invoice_id);
            $result = $invoiceObj->customizeNumber('I',$object);
            if(!$result) return parent::setLastInvoiceNumber($order_invoice_id, $id_shop);
            else return true;
        }
        else {
            return parent::setLastInvoiceNumber($order_invoice_id, $id_shop);
        }
    }
    /*
    * module: gwadvancedinvoice
    * date: 2025-07-10 16:04:51
    * version: 1.3.0
    */
    public function setDeliveryNumber($order_invoice_id, $id_shop)
    {
        if (!$order_invoice_id) {
            return false;
        }
        if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice'))
        {
            $invoiceObj = Module::getInstanceByName('gwadvancedinvoice');
            $object = new OrderInvoice($order_invoice_id);
            $result = $invoiceObj->customizeNumber('D',$object);
            if(!$result) return parent::setDeliveryNumber($order_invoice_id, $id_shop);
            else return true;
        }
        else {
            return parent::setDeliveryNumber($order_invoice_id, $id_shop);
        }
    }
}