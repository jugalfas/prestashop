<?php
/**
 * OfferPdfGenerator - Generates PDF documents for offers.
 *
 * Uses PrestaShop's native PDF library (TCPDF under the hood).
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferPdfGenerator
{
    /**
     * Generate and output the PDF for an offer.
     *
     * @param int $id_offer
     * @param string $mode 'D' (download), 'I' (inline), 'S' (string)
     * @return string|void
     */
    public static function generate($id_offer, $mode = 'D')
    {
        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            return false;
        }

        $products = OfferProduct::getByOffer($id_offer);
        $history = OfferHistory::getByOffer($id_offer, 50);

        $context = Context::getContext();
        $id_lang = (int) $context->language->id;

        $smarty = $context->smarty;

        $formattedProducts = [];
        $totalPrice = 0;

        foreach ($products as $p) {
            $attachments = OfferAttachment::getByOfferProduct($p['id_offer_product']);
            $configSummary = self::buildConfigSummary($p['configuration_json']);

            $lineTotal = 0;
            if ($p['offered_price']) {
                $lineTotal = (float) $p['offered_price'] * (int) $p['quantity'];
                $totalPrice += $lineTotal;
            }

            $imagePath = '';
            if (!empty($p['image_id'])) {
                $imagePath = _PS_IMG_DIR_ . 'p/' . implode('/', str_split((string) $p['id_product'])) . '/' . $p['image_id'] . '-small_default.jpg';
                if (!file_exists($imagePath)) {
                    $imagePath = '';
                }
            }

            $formattedProducts[] = [
                'name' => $p['product_name'],
                'reference' => $p['product_reference'],
                'quantity' => (int) $p['quantity'],
                'configuration' => $configSummary,
                'offered_price' => $p['offered_price'] ? Tools::displayPrice($p['offered_price']) : '-',
                'line_total' => Tools::displayPrice($lineTotal),
                'weight' => $p['weight'] ? $p['weight'] . ' kg' : '-',
                'production_time' => $p['production_time'] ?: '-',
                'status' => ucfirst(str_replace('_', ' ', $p['product_status'])),
                'customer_note' => $p['customer_note'],
                'admin_note' => $p['admin_note'],
                'attachments' => $attachments,
                'image_path' => $imagePath,
            ];
        }

        $customerName = $offer->guest_name ?: '';
        $customerEmail = $offer->guest_email ?: '';
        if ($offer->id_customer) {
            $customer = new Customer($offer->id_customer);
            if (Validate::isLoadedObject($customer)) {
                $customerName = $customer->firstname . ' ' . $customer->lastname;
                $customerEmail = $customer->email;
            }
        }

        $smarty->assign([
            'offer' => $offer,
            'products' => $formattedProducts,
            'history' => $history,
            'total_price' => Tools::displayPrice($totalPrice),
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'shop_logo' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO'),
            'date_today' => date('Y-m-d'),
            'id_lang' => $id_lang,
        ]);

        $html = $smarty->fetch(_PS_MODULE_DIR_ . 'productpriceconfig/views/templates/admin/offers/pdf_template.tpl');

        require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('ProductPriceConfig');
        $pdf->SetAuthor(Configuration::get('PS_SHOP_NAME'));
        $pdf->SetTitle('Offer ' . $offer->reference);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true);
        $pdf->SetFooterMargin(15);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 25);
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'offer_' . $offer->reference . '.pdf';

        return $pdf->Output($filename, $mode);
    }

    /**
     * Build a human-readable configuration summary.
     *
     * @param string $json
     * @return string
     */
    private static function buildConfigSummary($json)
    {
        $config = json_decode($json, true);
        if (!$config || !is_array($config)) {
            return '';
        }

        $parts = [];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $parts[] = $key . ': ' . $value;
        }

        return implode(' | ', $parts);
    }
}
