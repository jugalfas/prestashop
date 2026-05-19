<?php
class Carrier extends CarrierCore
{
    /*
    * module: ws_taxcarrier
    * date: 2025-10-07 09:55:21
    * version: 1.0.1
    */
    public static function getAvailableCarrierList(Product $product, $id_warehouse, $id_address_delivery = null, $id_shop = null, $cart = null, &$error = [])
    {
        $carrier_list = parent::getAvailableCarrierList($product, $id_warehouse, $id_address_delivery, $id_shop, $cart, $error);
        Hook::exec('actionCheckCarrierValidity', ['carrier_list' => &$carrier_list, 'product' => $product]);
        
        return $carrier_list;
    }
	/*
    * module: vatchecker
    * date: 2025-11-21 13:56:28
    * version: 3.1.5
    */
    public function getTaxesRate(Address $address = null)
	{
		if (!$address || !$address->id_country) {
			$address = Address::initialize();
		}
		$vatchecker = Module::getInstanceByName( 'vatchecker' );
		if ( $vatchecker && $vatchecker->canOrderWithoutVat( $address ) && Configuration::get( 'VATCHECKER_CARRIER_NOTAX' ) ) {
			if(empty(Configuration::get( 'VATCHECKER_TAXRATE_RULE' )))
			{
				return 0;
			}
			$tax_manager = TaxManagerFactory::getManager($address, (int)Configuration::get( 'VATCHECKER_TAXRATE_RULE' ));
			return $tax_manager->getTaxCalculator($address)->getTotalRate();
		}
		$tax_calculator = $this->getTaxCalculator($address);
		return $tax_calculator->getTotalRate();
	}
}