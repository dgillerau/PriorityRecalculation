<?php

require_once('mysqldb.php');
require_once('shp_rst.php');
require_once('Shop.php');
require_once ('Order.php');

class Product
{

    public $id;
    private $storeId;
    private $title;
    private $createdAt;
    private $priority;

    function __construct()
    {
        $this->storeId = Shop::$id;
    }

    public function retrieveProducts()
    {
        $page_size = 250;
        $newDays = Shop::$newDays;

        $db = new dbconn();
        $db->connect();
        $td = New DateTime(date("Y-m-d"));

        //retrieve count of products
        $jsn = new shp_rst();
        $data = $jsn->getcontent("/admin/products/count.json");

        //calculate number of pages
        $pg_qty = ceil($data['count'] / $page_size);

        //go through pages and insert products into db
        for ($pg_nm = 1; $pg_nm <= $pg_qty; $pg_nm++) {
            $data = $jsn->getcontent("/admin/products.json?limit=" . $page_size . "&page=" . $pg_nm . "&fields=id,title,published_at,variants");

            foreach ($data['products'] as $product) {
                $productId = $product['id'];
                $productTitle = $product['title'];
                //calculate how long ago in days the product was published and assign New status
                $productPublishedAt = New DateTime(date("Y-m-d", strtotime($product['published_at'])));
                $dDif = $productPublishedAt->diff($td);

                if ($dDif->days > $newDays) {
                    $productNew = 0;
                } else {
                    $productNew = 1;
                }

                //zero counters
                $productInStock = 0;
                $qty = 0;
                $inventoryManagement = 'shopify';

                foreach ($product['variants'] as $variant) {
                    //print $line_item['id']." - ".$line_item['title']." - ".$line_item['quantity']." - ".$line_item['price']."<br>";
                    $qty = $qty + $variant['inventory_quantity'];
                    if ($variant['inventory_management'] == "blank") {
                        $inventoryManagement = "blank";
                    }
                }
                if ($inventoryManagement == "blank" or $qty > 0) {
                    $productInStock = 1;
                }

                //check if products exists, if so, update it, otherwise, insert it
                $db->select("products", "id", "storeid='" . $this->storeId . "' and id='" . (string)$productId . "'");
                $db->getResult();

                if ($db->numResults == 1) {
                    $db->update("products", array("new" => $productNew, "title" => $productTitle, "instock" => $productInStock), array("id", (string)$productId, "storeid", (string)$this->storeId));
                } elseif ($db->numResults == 0) {
                    $db->insert("products", array((string)$productId, (string)$this->storeId, $productNew, $productTitle, $productInStock), "id,storeid,new,title,instock");
                } else {
                    echo "unexpected results";
                }
            }
        }
        $db->disconnect();
    }

    public function updatePriority()
    {
        $db = new dbconn();
        $db->connect();

        //call mysql stored procedure "update_sales" to update sales amounts and numbers in products table
        $db->execute("update_sales","('".$this->storeId."')");

        

        $db->disconnect();
    }

}