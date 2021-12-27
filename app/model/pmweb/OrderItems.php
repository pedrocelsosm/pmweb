<?php

use Adianti\Database\TRecord;

class OrderItems extends TRecord
{
    const TABLENAME = 'order_items';
    const PRIMARYKEY= 'order_id';
    const IDPOLICY  = 'max';

    //Metodo Construct
    public function __construct($order_id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($order_id, $callObjectLoad);
        parent::addAttribute('order_date');
        parent::addAttribute('product_sku');
        parent::addAttribute('size');
        parent::addAttribute('color');
        parent::addAttribute('quantity');
        parent::addAttribute('price');
    }
}