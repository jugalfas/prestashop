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
    public static function generate($id_offer, $mode = 'S')
    {
        try {
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
                $configSummary = ConfigurationSummaryRenderer::renderText($p['configuration_json']);
                $configRows = ConfigurationSummaryRenderer::renderArray($p['configuration_json']);

                $lineTotal = 0;
                if ($p['offered_price']) {
                    $lineTotal = (float) $p['offered_price'] * (int) $p['quantity'];
                    $totalPrice += $lineTotal;
                }

                $imagePath = '';
                if (!empty($p['image_id'])) {
                    $candidate = _PS_IMG_DIR_ . 'p/' . implode('/', str_split((string) $p['id_product'])) . '/' . $p['image_id'] . '-small_default.jpg';
                    if (file_exists($candidate)) {
                        $imagePath = $candidate;
                    }
                }

                $pdfAttachments = [];
                foreach ($attachments as $att) {
                    $pdfAttachments[] = [
                        'filename' => $att['original_name'] ?: $att['saved_name'],
                        'size' => !empty($att['size']) ? self::formatFileSize($att['size']) : '-',
                    ];
                }

                $formattedProducts[] = [
                    'name' => $p['product_name'],
                    'product_type' => isset($p['product_type']) ? $p['product_type'] : 'normal',
                    'reference' => $p['product_reference'],
                    'quantity' => (int) $p['quantity'],
                    'configuration' => $configSummary,
                    'configuration_rows' => $configRows,
                    'offered_price' => OfferService::formatPrice($p['offered_price']),
                    'estimated_price' => OfferService::formatPrice($p['estimated_price']),
                    'line_total' => OfferService::formatPrice($lineTotal),
                    'weight' => $p['weight'] ? $p['weight'] . ' kg' : '-',
                    'production_time' => $p['production_time'] ?: '-',
                    'production_time_days' => OfferService::parseProductionTimeDays($p['production_time']),
                    'status' => ucfirst(str_replace('_', ' ', $p['product_status'])),
                    'customer_note' => $p['customer_note'],
                    'admin_note' => $p['admin_note'],
                    'attachments' => $pdfAttachments,
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
                'total_price' => OfferService::formatPrice($totalPrice),
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'shop_name' => Configuration::get('PS_SHOP_NAME'),
                'shop_logo' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO'),
                'date_today' => date('Y-m-d'),
                'id_lang' => $id_lang,
            ]);

            $html = $smarty->fetch(_PS_MODULE_DIR_ . 'productpriceconfig/views/templates/admin/offers/pdf_template.tpl');

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $tcpdf_path = self::findTCPDFPath();
        
            if (!$tcpdf_path) {
                die('TCPDF library not found');
            }

            require_once($tcpdf_path);
            
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

            return $pdf->Output($filename, 'S');
        } catch (Exception $e) {
            PrestaShopLogger::addLog('OfferPdfGenerator error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 3);
            return false;
        }
    }

    protected static function findTCPDFPath()
    {
        $possible_paths = array(
            _PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php',
            _PS_ROOT_DIR_ . '/tools/tcpdf/tcpdf.php',
            _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php',
        );

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Build a human-readable configuration summary.
     *
     * @param string $json
     * @return string
     */
    private static function buildConfigSummary($json)
    {
        return ConfigurationSummaryRenderer::renderText($json);
    }

    /**
     * Format file size in human-readable form.
     *
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize($bytes)
    {
        $bytes = (int) $bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return number_format($bytes / 1048576, 1) . ' MB';
    }
}
