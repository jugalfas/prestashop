<?php
class Order extends OrderCore
{
    /**
     * OVERRIDE: Disable old generateReference() logic
     * Let our module handle it via add() override instead
     */
    public static function generateReference()
    {
        // Return empty - our module will set it in add()
        return '';
    }

    /**
     * OnlyPrint Sequential Reference - Override add()
     */
    public function add($autodate = true, $null_values = false)
    {
        // Let parent create the order first
        $result = parent::add($autodate, $null_values);

        if (!$result) {
            return false;
        }

        // Now we have an order ID - generate sequential reference
        $this->injectSequentialReference();

        return $result;
    }

    /**
     * Generate and inject sequential reference via module
     */
    private function injectSequentialReference()
    {
        try {
            // Check if module exists and is active
            if (!Module::isInstalled('onlyprint_seqref') || !Module::isEnabled('onlyprint_seqref')) {
                return;
            }

            // Load module
            $module = Module::getInstanceByName('onlyprint_seqref');
            if (!$module) {
                return;
            }

            // Check if we should generate reference for this order
            if (!method_exists($module, 'shouldGenerateReference') || 
                !$module->shouldGenerateReference($this->id)) {
                return;
            }

            // Get current reference (might be empty from generateReference())
            $oldReference = $this->reference;

            // Generate new reference
            if (method_exists($module, 'generateNextReference')) {
                $newReference = $module->generateNextReference($this->id, $oldReference);

                if ($newReference) {
                    // Update this object
                    $this->reference = $newReference;

                    // Update in database
                    Db::getInstance()->update(
                        'orders',
                        array('reference' => pSQL($newReference)),
                        'id_order = ' . (int)$this->id
                    );
                }
            }
        } catch (Exception $e) {
            // Silent fail - don't break order creation
        }
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
