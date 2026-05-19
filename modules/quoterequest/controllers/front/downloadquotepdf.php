<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class QuoteRequestDownloadQuotePdfModuleFrontController extends ModuleFrontController
{
    public $display_header = false;
    public $display_footer = false;

    public function init()
    {
        parent::init();
        $this->display_column_left = false;
        $this->display_column_right = false;
    }

    public function postProcess()
    {
        //parent::postProcess();

        $id_quote = (int)Tools::getValue('id_quote');

        if (!$id_quote) {
            Tools::redirect('index.php?controller=pagenotfound');
        }

        $context = Context::getContext();
        $customer = $context->customer;

        $quote = null;
        if ($customer->isLogged()) {
            $all_quotes = KDQuote::getQuotesDetailsByCustomerId($customer->id);
            foreach ($all_quotes as $q) {
                if ($q['id_quote'] == $id_quote) {
                    $quote = $q;
                    break;
                }
            }
        } elseif (Tools::getValue('email')) {
            $all_quotes = KDQuote::getQuotesDetailsByCustomerEmail(Tools::getValue('email'));
            foreach ($all_quotes as $q) {
                if ($q['id_quote'] == $id_quote) {
                    $quote = $q;
                    break;
                }
            }
        }

        if (!$quote) {
            Tools::redirect('index.php?controller=pagenotfound');
        }

        $this->generatePDF($quote);
    }

    protected function generatePDF($quote)
    {
        ob_clean();

        $tcpdf_path = $this->findTCPDFPath();

        if (!$tcpdf_path) {
            die('TCPDF library not found');
        }

        require_once($tcpdf_path);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', '', 9);

        $pdf->AddPage();
        $html = $this->generateHTML($quote);

        // echo '<pre>';
        // print_r($html);
        // echo '</pre>';
        // exit;
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'quote_' . $quote['id_quote'] . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $quote['title']) . '.pdf';

        $pdf->Output($filename, 'D');
        exit;
    }

    protected function findTCPDFPath()
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

    protected function generateHTML($quote)
    {
        // NOTE: TCPDF likes inline styles — keep most styling inline for reliable output.
        $html = '
    <div style="width:100%; background:#f3f3f3; padding:18px 22px; font-family: helvetica, Arial, sans-serif; font-size:10pt; color:#222;">
      <!-- Card -->
      <div style="background:#fff; padding:18px; border-radius:0; box-shadow:none;">

        <!-- Header: left title + right date -->
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:14px;">
          <tr>
            <td style="vertical-align:middle; padding:0;">
              <div style="font-size:13pt; font-weight:bold; letter-spacing:0.4px;">
                <span style="color:#222;">QUOTE :</span>
                <span style="color:#222; font-weight:700;"> #' . (int)$quote['id_quote'] . ' ' . $this->escapeHtml(strtoupper($quote['title'])) . ' </span>
                <span style="color:#afcb31; font-weight:800;">[ ' . $this->escapeHtml(strtoupper($quote['status_text'])) . ' ]</span>
              </div>
            </td>
            <td style="vertical-align:middle; text-align:right; padding:0;">
              <div style="font-size:9pt; color:#666; font-weight:bold;">
                POSTED ON : ' . $this->escapeHtml($quote['date_add']) . '
              </div>
            </td>
          </tr>
        </table>

        <!-- Table container -->
        <div style="background:#f6f6f6; padding:14px;">
          <table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse;">
            <!-- header row -->
            <thead>
              <tr>
                <th style="background:#333; color:#fff; text-align:left; padding:10px 12px; font-weight:700; font-size:9pt; width:15%;">Item</th>
                <th style="background:#333; color:#fff; text-align:left; padding:10px 12px; font-weight:700; font-size:9pt; width:25%;">Product details</th>
                <th style="background:#333; color:#fff; text-align:left; padding:10px 12px; font-weight:700; font-size:9pt; width:40%;">Product configure</th>
                <th style="background:#333; color:#fff; text-align:left; padding:10px 12px; font-weight:700; font-size:9pt; width:20%;">Files</th>
              </tr>
            </thead>

            <tbody style="background:#fff;">
    ';

        if (!empty($quote['products']) && is_array($quote['products'])) {
            foreach ($quote['products'] as $product) {
                // item (image)
                $itemImgHtml = '';
                if (!empty($product['image_link'])) {
                    // make sure image urls are absolute and accessible to TCPDF
                    $itemImgHtml = '<img src="' . $this->escapeHtml($product['image_link']) . '" style="max-width:100px; max-height:100px; display:block; margin:6px auto;" alt="Product" />';
                }

                // product details block
                $pd = '<div style="font-size:9pt; color:#666; margin-bottom:6px;">
                    <div><strong style="color:#999; font-weight:700;">Product :</strong> <span style="color:#111;">' . $this->escapeHtml($product['product_title']) . '</span></div>';
                if (isset($product['price']) && $product['price'] !== '0.0000' && $product['price'] !== '') {
                    $pd .= '<div style="margin-top:6px;"><strong style="color:#999; font-weight:700;">Price :</strong> <span style="color:#28a745; font-weight:700;">' . $this->escapeHtml($product['price']) . '</span> <span style="color:#28a745;">&#10003;</span></div>';
                }
                if (isset($product['price2']) && $product['price2'] !== '0.0000' && $product['price2'] !== '') {
                    $pd .= '<div style="margin-top:4px;"><strong style="color:#999; font-weight:700;">Price 2 :</strong> <span style="color:#28a745; font-weight:700;">' . $this->escapeHtml($product['price2']) . '</span></div>';
                }
                if (isset($product['price3']) && $product['price3'] !== '0.0000' && $product['price3'] !== '') {
                    $pd .= '<div style="margin-top:4px;"><strong style="color:#999; font-weight:700;">Price 3 :</strong> <span style="color:#28a745; font-weight:700;">' . $this->escapeHtml($product['price3']) . '</span></div>';
                }
                $pd .= '</div>';

                // config column: parse JSON details if available
                $configHtml = '';
                $configurations = [];
                if (!empty($product['details'])) {
                    $tmp = json_decode($product['details'], true);
                    if (is_array($tmp)) {
                        $configurations = $tmp;
                    }
                }

                if (!empty($configurations)) {
                    $configHtml .= '<div style="font-size:9pt; color:#333;">';
                    foreach ($configurations as $config) {
                        if (isset($config['additional_information']) && $config['additional_information'] !== '') {
                            $configHtml .= '<div style="margin-bottom:6px;"><strong style="color:#999; font-weight:700;">Additional Information :</strong> <span style="color:#111;">' . $this->escapeHtml($config['additional_information']) . '</span></div>';
                        } elseif (isset($config['variable_name'])) {
                            $value = isset($config['valueText']) ? $config['valueText'] : (isset($config['value']) ? $config['value'] : '-');
                            $configHtml .= '<div style="margin-bottom:4px;"><strong style="color:#999; font-weight:700;">' . $this->escapeHtml($config['variable_name']) . ' :</strong> <span style="color:#111;">' . $this->escapeHtml($value) . '</span></div>';
                        }
                    }
                    $configHtml .= '</div>';
                } else {
                    // fallback: show any direct keys from product for common labels
                    $fallbacks = [
                        'quantity' => 'Quantity',
                        'size' => 'Size',
                        'orientation' => 'Orientation',
                        'colour' => 'Colour',
                        'printside' => 'Printside',
                        'papertype' => 'PaperType',
                        'laminating' => 'Laminating',
                        'upload_method' => 'Upload method'
                    ];
                    $configHtml .= '<div style="font-size:9pt; color:#333;">';
                    foreach ($fallbacks as $k => $label) {
                        if (!empty($product[$k])) {
                            $configHtml .= '<div style="margin-bottom:4px;"><strong style="color:#999; font-weight:700;">' . $label . ' :</strong> <span style="color:#111;">' . $this->escapeHtml($product[$k]) . '</span></div>';
                        }
                    }
                    $configHtml .= '</div>';
                }

                // files column: handle array or single
                $filesHtml = '<div style="text-align:center;">';
                if (!empty($product['file'])) {
                    $files = is_array($product['file']) ? $product['file'] : [$product['file']];
                    foreach ($files as $file) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if ($ext === 'pdf') {
                            // show a small pdf icon + filename (icon path should be correct for TCPDF to fetch)
                            $pdfIcon = _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/quoterequest/views/img/pdf.png';
                            $filesHtml .= '<div style="margin:6px 0; font-size:8pt;">';
                            $filesHtml .= '<img src="' . $this->escapeHtml($pdfIcon) . '" alt="pdf" style="width:50px; height:50px; display:block; margin:0 auto 4px;" />';
                            $filesHtml .= '<div style="word-break:break-word; font-size:7.5pt; color:#444;">' . $this->escapeHtml(basename($file)) . '</div>';
                            $filesHtml .= '</div>';
                        } else {
                            $filesHtml .= '<div style="margin:6px 0;"><img src="' . $this->escapeHtml($file) . '" alt="file" style="max-width:100px; max-height:110px; display:block; margin:0 auto;" /></div>';
                        }
                    }
                }
                $filesHtml .= '</div>';

                $html .= '
              <tr>
                <td style="padding:12px; vertical-align:top; width:15%; text-align:center;">' . $itemImgHtml . '</td>
                <td style="padding:12px; vertical-align:top; width:25%;">' . $pd . '</td>
                <td style="padding:12px; vertical-align:top; width:40%;">' . $configHtml . '</td>
                <td style="padding:12px; vertical-align:top; width:20%;">' . $filesHtml . '</td>
              </tr>
            ';
            }
        }

        // close table and wrappers
        $html .= '
            </tbody>
          </table>
        </div> <!-- /table container -->
      </div> <!-- /card -->
    </div> <!-- /page bg -->
    ';

        return $html;
    }


    protected function escapeHtml($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
