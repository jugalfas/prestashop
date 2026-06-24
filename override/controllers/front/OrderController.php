<?php
/**
 * Custom reorder: rebuild productpriceconfig customizations before adding to cart.
 */

class OrderController extends OrderControllerCore
{
    public function postProcess()
    {
        if (Tools::isSubmit('submitReorder')
            && $this->context->customer->isLogged()
            && ($idOrder = (int) Tools::getValue('id_order'))
        ) {
            /** @var ProductPriceConfig|false $module */
            $module = Module::getInstanceByName('productpriceconfig');
            if ($module instanceof ProductPriceConfig && $module->active) {
                $module->processCustomReorder($idOrder, $this);

                return;
            }
        }

        parent::postProcess();
    }
}
