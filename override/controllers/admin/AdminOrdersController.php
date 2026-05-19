<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

        // Deine zusätzliche Spalte für Rechnungsnummer
        $this->_select .= ', gwaicn.current_counter_nbr AS invoice_number';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'gwaicustomnumber` gwaicn ON (gwaicn.id_gwaicustomnumber = a.id_order)';

        $this->fields_list['invoice_number'] = [
            'title' => $this->l('Rechnungsnr.'),
            'align' => 'text-center',
            'class' => 'fixed-width-xs',
        ];
        

        $this->fields_list['newsletter'] = array(
            'title' => $this->l('Newsletter'),
            'filter_key' => 'c!newsletter',
            'callback' => 'displayNewsletter'
        );
    }
    
    public function displayNewsletter($value) {
        return $value ? $this->l('Yes') : $this->l('No');   
    }
}