<?php
/**
* This file will override class PDFCore. Do not modify this file if you want to upgrade the module in future
* 
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2017 Globo ., Jsc
* @license   please read license in file license.txt
* @link      http://www.globosoftware.net
*/

class PDF extends PDFCore
{
    public function render($display = true)
	{
            $this->pdf_renderer->SetHeaderMargin(0);
    $this->pdf_renderer->SetMargins(10, 10, 10);
    $this->pdf_renderer->SetAutoPageBreak(true, 30);
    $this->pdf_renderer->SetFooterMargin(30);
	    $_controller = Tools::getValue('controller');
        $id_shop_group = Shop::getContextShopGroupID();
	    $id_shop = Shop::getContextShopID(); 
        $id_lang = (int)Context::getContext()->language->id;
        $old_lang = $id_lang;
        $iso_code_lang = Context::getContext()->language->iso_code;
		$render = false;
		$this->pdf_renderer->setFontForLang($iso_code_lang);
		foreach ($this->objects as $object)
		{
			$template = $this->getTemplateObject($object);
			if (!$template)
				continue;
            $templatetype =  get_class($template);
			if (empty($this->filename))
			{
			    if (count($this->objects) > 1){
					$this->filename = $template->getBulkFilename();
                }else{ 
    			    if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice') && Configuration::get('GWADVANCEDINVOICE_ACTIVE', null, $id_shop_group, $id_shop) && get_class($object) == 'OrderInvoice'){
    			         $invoiceObj = Module::getInstanceByName('gwadvancedinvoice');
                         if($templatetype == 'HTMLTemplateInvoice'){
                            $title = $invoiceObj->formatNumber('I',$template->order_invoice->number,$template->order_invoice);
                            $template->title = $title;
                            $this->filename = preg_replace('/[^A-Za-z0-9\-\.]/', '-', $title. '.pdf');
                         }elseif($templatetype == 'HTMLTemplateDeliverySlip'){
                            $title = $invoiceObj->formatNumber('D',$template->order_invoice->delivery_number,$template->order_invoice);
                            $template->title = $title;
                            $this->filename = preg_replace('/[^A-Za-z0-9\-\.]/', '-', $title. '.pdf');
                         }
    			    }else
    				    $this->filename = $template->getFilename();
				}
			}
			$template->assignHookData($object);
            if($id_shop <= 0) $id_shop = (int)Context::getContext()->shop->id;
            if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice') && Configuration::get('GWADVANCEDINVOICE_ACTIVE', null, $id_shop_group, $id_shop) && get_class($object) == 'OrderInvoice'){
                $id_template = 0;
                $pdftype = '';
                if($templatetype == 'HTMLTemplateInvoice'){$pdftype = 'INVOICE';}elseif($templatetype == 'HTMLTemplateDeliverySlip'){$pdftype = 'DELIVERY';}
                if($templatetype == 'HTMLTemplateInvoice' || $templatetype == 'HTMLTemplateDeliverySlip'){
                    $id_template = (int)Configuration::get('GWADVANCED'.$pdftype.'_TEMPLATE', null, $id_shop_group, $id_shop);
                    $order = new Order((int)$object->id_order);
                    if(in_array($_controller,array('AdminPdf'))){
                        $pdf_lang = (int)Configuration::get('GWADVANCEDINVOICE_BACKOFFICE_LANG', null, (int)$id_shop_group, (int)$id_shop);
                        if($pdf_lang == -1){
                            $id_lang = (int)Context::getContext()->language->id;
                        }elseif($pdf_lang == 0){
                            $id_lang = (int)$order->id_lang;
                        }else $id_lang = (int)$pdf_lang;
                        Context::getContext()->language = new Language((int)$id_lang);
                    }else $id_lang = (int)$order->id_lang; 
                    
                    $template_by = Configuration::get('GWADVANCED'.$pdftype.'_CUSTOMER_TEMPLATE_TYPE', null, $id_shop_group, $id_shop);
                    if($template_by == 'payment_method'){
                        $id_payment = Module::getModuleIdByName($order->module);
                        $_id_template = (int)Configuration::get('GWADVANCED'.$pdftype.'_PAYMENT_TEMPLATE_'.(int)$id_payment, null, $id_shop_group, $id_shop);
                        if($_id_template > 0){$id_template = (int)$_id_template;} 
                    }elseif($template_by == 'order_status'){
                        $current_state = $order->current_state;
                        $_id_template = (int)Configuration::get('GWADVANCED'.$pdftype.'_STATUS_TEMPLATE_'.(int)$current_state, null, $id_shop_group, $id_shop);
                        if($_id_template > 0){$id_template = (int)$_id_template;}
                    }else{
                        $customer_group = Customer::getDefaultGroupId((int)$order->id_customer);
                        if($customer_group){
                            $_id_template = (int)Configuration::get('GWADVANCED'.$pdftype.'_GROUP_'.$customer_group, null, $id_shop_group, $id_shop);
                            if($_id_template > 0) $id_template = (int)$_id_template;
                        }
                    }
                }
                include_once(_PS_MODULE_DIR_.'/gwadvancedinvoice/model/gwadvancedinvoicetemplateModel.php');
                $templateinvoice = new gwadvancedinvoicetemplateModel((int)$id_template);
                if(Validate::isLoadedObject($templateinvoice)){
                    
                                        
                    $this->pdf_renderer->setCurOrientation($templateinvoice->pagesize,$templateinvoice->pageorientation);
                    $data = $template->assignData($id_template);

// --- apply header/content/footer margins from template settings ---

// Header-Abstand (zwischen Seitenrand und dem Header-HTML)
if (isset($templateinvoice->header_margin)) {
    $this->pdf_renderer->SetHeaderMargin((float)$templateinvoice->header_margin);
}

// Content-Abstände: Left-Top-Right-Bottom, z.B. "10-60-10-80"
if (!empty($templateinvoice->content_margin) && strpos($templateinvoice->content_margin, '-') !== false) {
    list($mL, $mT, $mR, $mB) = explode('-', $templateinvoice->content_margin);
    // linke, obere, rechte Ränder
    $this->pdf_renderer->SetMargins((float)$mL, (float)$mT, (float)$mR);
    // automatischer Seitenumbruch mit dem unteren Rand
    $this->pdf_renderer->SetAutoPageBreak(true, (float)$mB);
}

// Footer-Abstand (zwischen Footer-HTML und Seitenunterkante)
if (isset($templateinvoice->footer_margin)) {
    $this->pdf_renderer->SetFooterMargin((float)$templateinvoice->footer_margin);
}


                    
                    if($templateinvoice->rtl){
                        $this->pdf_renderer->setRTL((bool)$templateinvoice->rtl);
                    }
                    
                    if($templateinvoice->activeheader){
                        $this->pdf_renderer->SetPrintHeader(true);
                        $this->pdf_renderer->createHeader($template->getHeaderGw($data));
                    }else{
                        $this->pdf_renderer->createHeader('');
                        $this->pdf_renderer->SetPrintHeader(false);
                        
                    }
                    $this->pdf_renderer->createContent($template->getContentGw($data));
                    
                    if($templateinvoice->activefooter){
                        $this->pdf_renderer->SetPrintFooter(true);
        			    $this->pdf_renderer->createFooter($template->getFooterGw($data));
                    }else{
                        $this->pdf_renderer->createFooter('');
                        $this->pdf_renderer->SetPrintFooter(false);
                        
                    }
                    $this->pdf_renderer->AddPage();  
                    $this->pdf_renderer->writePageGw($templateinvoice->mgheader,$templateinvoice->mgfooter,$templateinvoice->mgcontent);
                    $watermank_img = '';
                    $watermank_text = '';
                    $watermank_font = '';
                    $watermank_size = '10';
                    if(isset($templateinvoice->watermark[(int)$id_lang]) && $templateinvoice->watermark[(int)$id_lang]!=''){
                        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
                		$protocol_content = ($useSSL) ? 'https:/'.'/' : 'http:/'.'/';
                        $base_url = $protocol_content.Tools::getHttpHost().__PS_BASE_URI__;
                        if(!is_dir(_PS_MODULE_DIR_.'/gwadvancedinvoice/views/img/watermark/'.$templateinvoice->watermark[(int)$id_lang]))
                                if(file_exists(_PS_MODULE_DIR_.'/gwadvancedinvoice/views/img/watermark/'.$templateinvoice->watermark[(int)$id_lang]))
                                    $watermank_img = $base_url.'modules/gwadvancedinvoice/views/img/watermark/'.$templateinvoice->watermark[(int)$id_lang];
                    }
                    if(isset($templateinvoice->watermarktext[(int)$id_lang]) && $templateinvoice->watermarktext[(int)$id_lang]!=''){
                        $watermank_text = $templateinvoice->watermarktext[(int)$id_lang];
                        $watermank_font = $templateinvoice->watermarkfont[(int)$id_lang];
                        $watermank_size = $templateinvoice->watermarksize[(int)$id_lang];
                    }
                    if($watermank_img !='' || $watermank_text !=''){
                        $this->pdf_renderer->addWaterMark($watermank_text,$watermank_img,45,0,'0.1',$watermank_font,$watermank_size);
                    }
                }else{
                    $this->pdf_renderer->createHeader($template->getHeader());
        			$this->pdf_renderer->createFooter($template->getFooter());
        			$this->pdf_renderer->createContent($template->getContent());
                    $this->pdf_renderer->writePage();
                }
            }else{
                $this->pdf_renderer->createHeader($template->getHeader());
    			$this->pdf_renderer->createFooter($template->getFooter());
    			$this->pdf_renderer->createContent($template->getContent());
                $this->pdf_renderer->writePage();
            }
			$render = true;
			unset($template);
		}
        if(in_array($_controller,array('AdminPdf'))){
            if($old_lang != Context::getContext()->language->id)
                Context::getContext()->language = new Language((int)$old_lang);
        }
		if ($render)
		{
			if (ob_get_level() && ob_get_length() > 0)
				ob_clean();
            if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice') && Configuration::get('GWADVANCEDINVOICE_ACTIVE', null, $id_shop_group, $id_shop) && get_class($object) == 'OrderInvoice'){
                return $this->pdf_renderer->renderInvoice($this->filename, $display);
            }
            else
			     return $this->pdf_renderer->render($this->filename, $display);
		}
	}
}
?>