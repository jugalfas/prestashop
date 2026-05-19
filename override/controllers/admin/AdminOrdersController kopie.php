<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

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