<?php
/**
 * OfferEmailNotifier - Sends email notifications for offer events.
 *
 * Uses PrestaShop's native Mail::Send() with multilingual templates.
 * Templates are stored in the module's mails/ directory.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferEmailNotifier
{
    /**
     * Send a notification email for an offer event.
     *
     * @param string $type One of: quote_submitted, price_offered, offer_accepted,
     *                     offer_rejected, offer_cancelled, offer_expired,
     *                     offer_reopened, converted_to_order
     * @param Offer $offer
     * @return bool
     */
    public static function sendNotification($type, $offer)
    {
        $context = Context::getContext();
        $id_lang = (int) $context->language->id;

        $templateVars = self::buildTemplateVars($offer);
        $subject = self::getSubject($type, $id_lang);

        if ($offer->id_customer) {
            $customer = new Customer($offer->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                return false;
            }
            $recipientEmail = $customer->email;
            $recipientName = $customer->firstname . ' ' . $customer->lastname;
        } else {
            $recipientEmail = $offer->guest_email;
            $recipientName = $offer->guest_name ?: $offer->guest_email;
        }

        $adminEmail = Configuration::get('PS_SHOP_EMAIL');
        $adminName = Configuration::get('PS_SHOP_NAME');

        $customerNotifs = ['price_offered', 'offer_cancelled', 'offer_expired', 'offer_reopened', 'converted_to_order'];
        $adminNotifs = ['quote_submitted', 'offer_accepted', 'offer_rejected'];

        $sent = false;

        if (in_array($type, $customerNotifs) && $recipientEmail) {
            $sent = Mail::Send(
                $id_lang,
                $type,
                $subject,
                $templateVars,
                $recipientEmail,
                $recipientName,
                $adminEmail,
                $adminName,
                null,
                null,
                _PS_MODULE_DIR_ . 'productpriceconfig/mails/',
                false
            );
        }

        if (in_array($type, $adminNotifs) && $adminEmail) {
            $adminSubject = '[Admin] ' . $subject;
            $sent = Mail::Send(
                $id_lang,
                $type . '_admin',
                $adminSubject,
                $templateVars,
                $adminEmail,
                $adminName,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . 'productpriceconfig/mails/',
                false
            );
        }

        return $sent;
    }

    /**
     * Build template variables for email content.
     *
     * @param Offer $offer
     * @return array
     */
    private static function buildTemplateVars($offer)
    {
        $products = OfferProduct::getByOffer($offer->id);
        $productList = [];
        $totalPrice = 0;

        foreach ($products as $p) {
            $productList[] = '- ' . $p['product_name'] . ' (x' . $p['quantity'] . ')';
            if ($p['offered_price']) {
                $totalPrice += (float) $p['offered_price'] * (int) $p['quantity'];
            }
        }

        $offerLink = Context::getContext()->link->getPageLink('my-account', true) . '#offers';

        return [
            '{offer_reference}' => $offer->reference,
            '{offer_title}' => $offer->title ?: $offer->reference,
            '{offer_status}' => ucfirst(str_replace('_', ' ', $offer->status)),
            '{offer_expiry}' => $offer->expire_date ?: 'N/A',
            '{product_list}' => implode("\n", $productList) ?: 'No products',
            '{total_price}' => Tools::displayPrice($totalPrice),
            '{offer_link}' => $offerLink,
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
            '{shop_url}' => Context::getContext()->shop->getBaseURL(true),
        ];
    }

    /**
     * Get email subject by type.
     *
     * @param string $type
     * @param int $id_lang
     * @return string
     */
    private static function getSubject($type, $id_lang)
    {
        $subjects = [
            'quote_submitted' => 'New Quotation Request',
            'price_offered' => 'Price Offer for Your Quotation',
            'offer_accepted' => 'Quotation Accepted',
            'offer_rejected' => 'Quotation Rejected',
            'offer_cancelled' => 'Quotation Cancelled',
            'offer_expired' => 'Quotation Expired',
            'offer_reopened' => 'Quotation Reopened',
            'converted_to_order' => 'Quotation Converted to Order',
        ];

        return $subjects[$type] ?? 'Quotation Update';
    }
}
