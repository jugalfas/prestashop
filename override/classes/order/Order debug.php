<?php
class Order extends OrderCore
{
    /**
     * OVERRIDE: Disable old generateReference() logic
     * DEBUG VERSION
     */
    public static function generateReference()
    {
        // LOG IT!
        file_put_contents(
            _PS_ROOT_DIR_ . '/debug_order_ref.log',
            date('Y-m-d H:i:s') . " - generateReference() called - returning empty\n",
            FILE_APPEND
        );

        // Return empty - our module will set it in add()
        return '';
    }

    /**
     * OnlyPrint Sequential Reference - Override add()
     * DEBUG VERSION
     */
    public function add($autodate = true, $null_values = false)
    {
        // LOG START
        file_put_contents(
            _PS_ROOT_DIR_ . '/debug_order_ref.log',
            date('Y-m-d H:i:s') . " - add() called - BEFORE parent\n",
            FILE_APPEND
        );

        // Let parent create the order first
        $result = parent::add($autodate, $null_values);

        if (!$result) {
            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - add() FAILED - parent returned false\n",
                FILE_APPEND
            );
            return false;
        }

        // LOG AFTER PARENT
        file_put_contents(
            _PS_ROOT_DIR_ . '/debug_order_ref.log',
            date('Y-m-d H:i:s') . " - add() SUCCESS - Order ID: " . $this->id . " - Reference: " . $this->reference . "\n",
            FILE_APPEND
        );

        // Now we have an order ID - generate sequential reference
        $this->injectSequentialReference();

        // LOG AFTER INJECT
        file_put_contents(
            _PS_ROOT_DIR_ . '/debug_order_ref.log',
            date('Y-m-d H:i:s') . " - add() AFTER inject - Reference: " . $this->reference . "\n",
            FILE_APPEND
        );

        return $result;
    }

    /**
     * Generate and inject sequential reference via module
     * DEBUG VERSION
     */
    private function injectSequentialReference()
    {
        file_put_contents(
            _PS_ROOT_DIR_ . '/debug_order_ref.log',
            date('Y-m-d H:i:s') . " - injectSequentialReference() START\n",
            FILE_APPEND
        );

        try {
            // Check if module exists and is active
            if (!Module::isInstalled('onlyprint_seqref')) {
                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - Module NOT installed\n",
                    FILE_APPEND
                );
                return;
            }

            if (!Module::isEnabled('onlyprint_seqref')) {
                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - Module NOT enabled\n",
                    FILE_APPEND
                );
                return;
            }

            // Load module
            $module = Module::getInstanceByName('onlyprint_seqref');
            if (!$module) {
                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - Module getInstance FAILED\n",
                    FILE_APPEND
                );
                return;
            }

            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - Module loaded OK\n",
                FILE_APPEND
            );

            // Check if we should generate reference for this order
            if (!method_exists($module, 'shouldGenerateReference')) {
                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - Method shouldGenerateReference NOT exists\n",
                    FILE_APPEND
                );
                return;
            }

            $should = $module->shouldGenerateReference($this->id);
            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - shouldGenerateReference(" . $this->id . ") = " . ($should ? 'YES' : 'NO') . "\n",
                FILE_APPEND
            );

            if (!$should) {
                return;
            }

            // Get current reference (might be empty from generateReference())
            $oldReference = $this->reference;

            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - Old reference: [" . $oldReference . "]\n",
                FILE_APPEND
            );

            // Generate new reference
            if (!method_exists($module, 'generateNextReference')) {
                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - Method generateNextReference NOT exists\n",
                    FILE_APPEND
                );
                return;
            }

            $newReference = $module->generateNextReference($this->id, $oldReference);

            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - New reference: [" . $newReference . "]\n",
                FILE_APPEND
            );

            if ($newReference) {
                // Update this object
                $this->reference = $newReference;

                // Update in database
                $dbResult = Db::getInstance()->update(
                    'orders',
                    array('reference' => pSQL($newReference)),
                    'id_order = ' . (int)$this->id
                );

                file_put_contents(
                    _PS_ROOT_DIR_ . '/debug_order_ref.log',
                    date('Y-m-d H:i:s') . " - DB UPDATE result: " . ($dbResult ? 'SUCCESS' : 'FAILED') . "\n",
                    FILE_APPEND
                );
            }
        } catch (Exception $e) {
            file_put_contents(
                _PS_ROOT_DIR_ . '/debug_order_ref.log',
                date('Y-m-d H:i:s') . " - EXCEPTION: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
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
