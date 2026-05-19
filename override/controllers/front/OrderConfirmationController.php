<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;

class OrderConfirmationController extends OrderConfirmationControllerCore
{
    public function initContent()
    {
        if (Configuration::isCatalogMode()) {
            Tools::redirect('index.php');
        }

        $order = new Order(Order::getIdByCartId((int) ($this->id_cart)));
        $customer_nb_orders = Order::getCustomerNbOrders($order->id_customer);
        $customer_nb_old_orders = $customer_nb_orders - 1;
        $presentedOrder = $this->order_presenter->present($order);
        $register_form = $this
            ->makeCustomerForm()
            ->setGuestAllowed(false)
            ->fillWith(Tools::getAllValues());

        parent::initContent();

        $this->context->smarty->assign([
            'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation($order),
            'HOOK_PAYMENT_RETURN' => $this->displayPaymentReturn($order),
            'order' => $presentedOrder,
            'register_form' => $register_form,
            'customer_nb_old_orders' => $customer_nb_old_orders,
        ]);

        if ($this->context->customer->is_guest) {
            /* If guest we clear the cookie for security reason */
            $this->context->customer->mylogout();
        }
        $this->setTemplate('checkout/order-confirmation');
    }
}
