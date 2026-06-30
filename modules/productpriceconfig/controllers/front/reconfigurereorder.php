<?php
/**
 * ReconfigureReorder - Front controller for the Reconfigure & Reorder page.
 *
 * URL: /module/productpriceconfig/reconfigurereorder?id_order=123
 *
 * Loads a single order (by id_order), validates all its PPC products,
 * and renders the reconfigure page with independent product blocks.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


require_once(dirname(__FILE__) . '/../../libraries/KDReconfigureReorder.php');

class ProductPriceConfigReconfigureReorderModuleFrontController extends ModuleFrontController
{
    /** @var ProductPriceConfig */
    public $module;

    public $auth = true;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        if (!$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication');
        }

        $id_order = (int) Tools::getValue('id_order');
        if (!$id_order) {
            Tools::redirect('index.php?controller=history');
        }

        // Verify order belongs to this customer
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order) || (int) $order->id_customer !== (int) $this->context->customer->id) {
            Tools::redirect('index.php?controller=history');
        }

        $service = new KDReconfigureReorder($this->module);
        $data = $service->loadOrder($id_order);

        if (!$data['success']) {
            $this->context->smarty->assign([
                'error' => $data['error'],
                'products' => [],
                'id_order' => $id_order,
                'order_reference' => '',
            ]);
        } else {
            $this->context->smarty->assign([
                'error' => null,
                'products' => $data['products'],
                'id_order' => $data['id_order'],
                'order_reference' => $data['order_reference'],
                'order_date' => $data['order_date'],
            ]);
        }

        $ajax_url = $this->context->link->getModuleLink(
            $this->module->name,
            'reconfigureajax',
            ['ajax' => 1],
            true
        );

        $cart_url = $this->getCartSummaryURL();
        $history_url = $this->context->link->getPageLink('history', true);

        $this->context->smarty->assign([
            'ajax_url' => $ajax_url,
            'cart_url' => $cart_url,
            'history_url' => $history_url,
        ]);

        $this->setTemplate('module:productpriceconfig/views/templates/front/reconfigure/page.tpl');
    }

    private function getCartSummaryURL()
    {
        return $this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            array(
                'action' => 'show'
            ),
            false,
            null,
            true
        );
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerJavascript(
            'module-productpriceconfig-reconfigure',
            'modules/' . $this->module->name . '/views/js/reconfigure.js',
            ['priority' => 200, 'server' => 'local']
        );

        $this->registerStylesheet(
            'module-productpriceconfig-reconfigure',
            'modules/' . $this->module->name . '/views/css/reconfigure.css',
            ['media' => 'all', 'server' => 'local']
        );
    }
}
