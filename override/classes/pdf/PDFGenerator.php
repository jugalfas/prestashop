<?php
/**
* This file will override class PDFGeneratorCore. Do not modify this file if you want to upgrade the module in future
* 
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2017 Globo ., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/
class PDFGenerator extends PDFGeneratorCore
{
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function Header()
    {
        parent::Header();
        if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice'))
        {
            $id_shop = (int)Context::getContext()->shop->id;
            $id_shop_group = (int)Shop::getGroupFromShop($id_shop);
            if(Configuration::get('GWADVANCEDINVOICE_ACTIVE', null, $id_shop_group, $id_shop)){
                $show_pagenumber = Configuration::get('GWADVANCEDINVOICE_PAGE_NUMBER', null, $id_shop_group, $id_shop);
                if($show_pagenumber){
                    $pagenumber_pos = Configuration::get('GWADVANCEDINVOICE_PAGE_NUMBER_POS', null, $id_shop_group, $id_shop);
                    if($pagenumber_pos =='' || $pagenumber_pos == 0) $pagenumber_pos = 6;
                    if($pagenumber_pos < 4){
                        $xpos = 5;$right_shift = '';
                        if(isset($this->original_lMargin) && $this->original_lMargin >= 0)
                            $xpos = $this->original_lMargin;
                        $ypos = 0;$align = 'L';
                        if($pagenumber_pos == 2){
                            $align = 'C';
                        }elseif($pagenumber_pos == 3){
                            $align = 'R';
                            $right_shift = $this->getAliasRightShift();
                        }
                        $this->SetY($ypos);
                        $this->SetX($xpos);
                        $this->SetFontSize(10);
                        $this->Cell(0, 15, $right_shift.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, $align, 0, '', 0, false, 'T', 'C'); 
                    }
                }
            }
        }
    }
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function Footer()
	{
	   parent::Footer();
       if(Module::isInstalled('gwadvancedinvoice') && Module::isEnabled('gwadvancedinvoice'))
       {
           $id_shop = (int)Context::getContext()->shop->id;
            $id_shop_group = (int)Shop::getGroupFromShop($id_shop);
            if(Configuration::get('GWADVANCEDINVOICE_ACTIVE', null, $id_shop_group, $id_shop)){
                $show_pagenumber = Configuration::get('GWADVANCEDINVOICE_PAGE_NUMBER', null, $id_shop_group, $id_shop);
                if($show_pagenumber){
                    $pagenumber_pos = Configuration::get('GWADVANCEDINVOICE_PAGE_NUMBER_POS', null, $id_shop_group, $id_shop);
                    if($pagenumber_pos =='' || $pagenumber_pos == 0) $pagenumber_pos = 6;
                    if($pagenumber_pos > 3){
                        $xpos = 5;$right_shift = '';
                        if(isset($this->original_lMargin) && $this->original_lMargin >= 0)
                            $xpos = $this->original_lMargin;
                        $ypos = -15;$align = 'L';
                        if($pagenumber_pos == 5){
                            $align = 'C';
                        }elseif($pagenumber_pos == 6){
                            $align = 'R';
                            $right_shift = $this->getAliasRightShift();
                        }
                        $this->SetY($ypos);
                        $this->SetX($xpos);
                        $this->SetFontSize(10);
                        $this->Cell(0, 15, $right_shift.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, $align, 0, '', 0, false, 'T', 'C'); 
                    }
                }
            }
        }
	}
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function writePageGw($mgheader=0,$mgfooter=0,$mgcontent='10-10-10-10')
	{
        $this->SetHeaderMargin($mgheader);
		$this->SetFooterMargin($mgfooter);
        $mgcontents = explode('-',$mgcontent);
		$this->setMargins((isset($mgcontents[0]) ? (int)$mgcontents[0] : 0), (isset($mgcontents[1]) ? (int)$mgcontents[1] : 0), (isset($mgcontents[2]) ? (int)$mgcontents[2] : 0));
        $this->setAutoPageBreak(true,(isset($mgcontents[3]) ? (int)$mgcontents[3] : 0));
		$preview = (bool)Tools::getValue('previewTemplate');
        if($preview){
            $this->AddPage();
        } 
		$this->writeHTML($this->content, true, false, true, false, '');
     }
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function setCurOrientation($format='A4',$orientation = 'P'){
         $this->setPageFormat($format, $orientation);
  	}
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function renderInvoice($filename, $display = true)
	{
		if (empty($filename))
			throw new PrestaShopException('Missing filename.');
		$this->lastPage();
		if ($display === true)
			$output = 'D';
		elseif ($display === false)
			$output = 'S';
		elseif ($display == 'D')
			$output = 'D';
		elseif ($display == 'S')
			$output = 'S';
		elseif ($display == 'F')
			$output = 'F';
		else
			$output = 'I';
		return $this->output($filename, $output);
	}
    /*
    * module: gwadvancedinvoice
    * date: 2024-04-26 12:52:28
    * version: 1.3.0
    */
    public function addWaterMark($mark='',$img='',$rotatemark = 45,$rotateimg = 0,$alpha='0.1',$tipoLetra='Helvetica',$tamanoLetra=35,$estiloLetra='B'){
        $nbr_page = $this->getNumPages();
        $myPageWidth = $this->getPageWidth();
        $myPageHeight = $this->getPageHeight();
        $myXMark = 0;
        $myYMark = 0;
        if($mark !=''){
            $widthCadena = $this->GetStringWidth(trim($mark), $tipoLetra, $estiloLetra, $tamanoLetra, false );
            $factorCentrado = round(($widthCadena * sin(deg2rad($rotatemark))) / 2 ,0);
            $myXMark = ( $myPageWidth / 2 ) - $factorCentrado;
            $myYMark = ( $myPageHeight / 2 ) + $factorCentrado;
            
        }
        $myX = 0;
        $myY = 0;
        $ImageW = 0;
        $ImageH = 0;
        if($img !=''){
            $imgwh = getimagesize($img);
            
            if($imgwh){
                $ImageW = $imgwh[0]/2.83;
                $ImageH = $imgwh[1]/2.83;
                $myX = round($myPageWidth / 2  - $ImageW/2);
                $myY = round($myPageHeight / 2  - $ImageH/2);
            }
        }
        for($i=1;$i<=$nbr_page;$i++){
            $this->setPage($i);
            if($mark !=''){
                $this->SetAlpha($alpha);
                $this->StartTransform();
                $this->SetFont($tipoLetra, $estiloLetra, $tamanoLetra);
                $this->Rotate($rotatemark, $myXMark, $myYMark);
                $this->Text($myXMark, $myYMark ,trim($mark));
                $this->StopTransform();
                $this->SetAlpha(1);
            }
            if($img !=''){
                if($ImageW > 0){
                    $this->SetAlpha($alpha);
                    $this->StartTransform();
                    $this->Rotate($rotateimg, $myX, $myY);
                    $this->Image($img, $myX, $myY, $ImageW, $ImageH, '', '', '', false);
                    $this->StopTransform();
                    $this->SetAlpha(1);
                }
            }
            
            
        }
    }
}
?>