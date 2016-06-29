<?php

require_once('mysqldb.php');
require_once('shp_rst.php');

class Shop
{
    public static $id;
    private static $title;
    public static $analyseMonths = 6;
    public static $pushUpNew = false;
    public static $pushDownOutOfStock = false;
    public static $newDays = 30;

    private static function retrieveShop(){
        $jsn = new shp_rst();
        $data = $jsn->getcontent("/admin/shop.json");
        //echo '<pre>' . var_dump($data) . '</pre>';
        Shop::$id= $data['shop']['id'];
        Shop::$title=$data['shop']['name'];
    }

    public static function updateShopDetails(){
        //update store id and name
        Shop::retrieveShop();

        //connect with db
        $db = New dbconn();
        $db->connect();

        //check if store already exists
        $db->select("shops","ifnull(id,0) as id","id='".Shop::$id."'");

        if ($db->numResults == 0){
            //echo "<br>1";
            $db->insert("shops",array((string)Shop::$id,Shop::$title,Shop::$analyseMonths,(int)Shop::$pushUpNew,(int)Shop::$pushDownOutOfStock,Shop::$newDays));
        }else{
            $db->update("shops",array("title"=>Shop::$title),array("id",(string)Shop::$id));
            $db->select("shops","analysemonths, pushupnew, pushdownoutofstock, newdays","id='".(string)Shop::$id."'");
            $sel = $db->getResult();


            if ($db->numResults == 1) {
                Shop::$analyseMonths = $sel['analysemonths'];
                Shop::$pushUpNew = $sel['pushupnew'];
                Shop::$pushDownOutOfStock = $sel['pushdownoutofstock'];
                Shop::$newDays = $sel['newdays'];
            } else {
                echo "unexpected results";
            }

        }
        $db->disconnect();

    }


}