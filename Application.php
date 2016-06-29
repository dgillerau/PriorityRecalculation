<?php

require_once('Shop.php');
require_once('Order.php');
require_once('Collection.php');
require_once ('Product.php');


class Application
{

    public function _init()
    {
        Shop::updateShopDetails();
        //$od = New Order();
        //$od->retrieveAll();
        //$od->calculateSales("363104147");
        //echo $od->productId." - ".$od->amount." - ".$od->numberOfSales;
        //$cl = New Collection();
        //$cl->retrieveAll();
        $pr = New Product();
        $pr->updatePriority();
    }


}