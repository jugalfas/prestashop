<?php
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
class CheckoutDeliveryStep extends CheckoutDeliveryStepCore
{
    /*
    * module: wkwebp
    * date: 2023-01-12 11:40:10
    * version: 4.1.0
    */
    public function render(array $extraParams = [])
    {
        if (Module::isEnabled('wkwebp')
            && Configuration::get('WK_WEBP_ENABLE_MODULE')
            && !Context::getContext()->cookie->wk_webp_safari
            ) {
            $wkDeliveryOptions = $this->getCheckoutSession()->getDeliveryOptions();
            if ($wkDeliveryOptions) {
                foreach ($wkDeliveryOptions as &$option) {
                    if (file_exists(_PS_MODULE_DIR_ . 'wkwebp/views/img/carrier/' . $option['id'] . '.webp')) {
                        
                        $option['logo'] = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.
                        '/modules/wkwebp/views/img/carrier/' . $option['id'] . '.webp';
                    }
                }
            }
            return $this->renderTemplate(
                $this->getTemplate(),
                $extraParams,
                [
                    'hookDisplayBeforeCarrier' => Hook::exec('displayBeforeCarrier', ['cart' => $this->getCheckoutSession()->getCart()]),
                    'hookDisplayAfterCarrier' => Hook::exec('displayAfterCarrier', ['cart' => $this->getCheckoutSession()->getCart()]),
                    'id_address' => $this->getCheckoutSession()->getIdAddressDelivery(),
                    'delivery_options' => $wkDeliveryOptions,
                    'delivery_option' => $this->getCheckoutSession()->getSelectedDeliveryOption(),
                    'recyclable' => $this->getCheckoutSession()->isRecyclable(),
                    'recyclablePackAllowed' => $this->isRecyclablePackAllowed(),
                    'delivery_message' => $this->getCheckoutSession()->getMessage(),
                    'gift' => [
                        'allowed' => $this->isGiftAllowed(),
                        'isGift' => $this->getCheckoutSession()->getGift()['isGift'],
                        'label' => $this->getTranslator()->trans(
                            'I would like my order to be gift wrapped %cost%',
                            ['%cost%' => $this->getGiftCostForLabel()],
                            'Shop.Theme.Checkout'
                        ),
                        'message' => $this->getCheckoutSession()->getGift()['message'],
                    ],
                ]
            );
            
        } else {
            return $this->renderTemplate(
                $this->getTemplate(),
                $extraParams,
                [
                    'hookDisplayBeforeCarrier' => Hook::exec('displayBeforeCarrier', ['cart' => $this->getCheckoutSession()->getCart()]),
                    'hookDisplayAfterCarrier' => Hook::exec('displayAfterCarrier', ['cart' => $this->getCheckoutSession()->getCart()]),
                    'id_address' => $this->getCheckoutSession()->getIdAddressDelivery(),
                    'delivery_options' => $this->getCheckoutSession()->getDeliveryOptions(),
                    'delivery_option' => $this->getCheckoutSession()->getSelectedDeliveryOption(),
                    'recyclable' => $this->getCheckoutSession()->isRecyclable(),
                    'recyclablePackAllowed' => $this->isRecyclablePackAllowed(),
                    'delivery_message' => $this->getCheckoutSession()->getMessage(),
                    'gift' => [
                        'allowed' => $this->isGiftAllowed(),
                        'isGift' => $this->getCheckoutSession()->getGift()['isGift'],
                        'label' => $this->getTranslator()->trans(
                            'I would like my order to be gift wrapped %cost%',
                            ['%cost%' => $this->getGiftCostForLabel()],
                            'Shop.Theme.Checkout'
                        ),
                        'message' => $this->getCheckoutSession()->getGift()['message'],
                    ],
                ]
            );
        }
    }
    
    
}
