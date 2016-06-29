<?php

require_once('mysqldb.php');
require_once('shp_rst.php');
require_once('Shop.php');

class Order
{

    //private $id;
    //private $date;
    public $productId;
    public $numberOfSales;
    public $amount;
    private $storeId;

    function __construct(){
        $this->storeId = Shop::$id;
    }

    public function retrieveAll()
    {

        $page_size = 250;
        $order_status = "any";

        $db = new dbconn();
        $db->connect();

        $db->select("orders","max(id*1) as id","storeid='".$this->storeId."'","storeid");
        $mon = $db->getResult();

        if ($db->numResults == 1) {
            $maxordnumber = $mon['id'];
        } elseif ($db->numResults == 0) {
            $maxordnumber = 0;
        } else {
            echo "unexpected results";
        }

        //echo $maxordnumber;

        //print "<pre>";
        //print_r($maxordnumber);
        //print "</pre>";

        //retrieve count of orders
        $jsn = new shp_rst();
        $data = $jsn->getcontent("/admin/orders/count.json?status=" . $order_status . "&since_id=".$maxordnumber);
        //echo "/admin/orders/count.json?status=" . $order_status . "since_id=".$maxordnumber;

        //calculate number of pages
        $pg_qty = ceil($data['count'] / $page_size);

        //go through pages and insert orders into db
        for ($pg_nm = 1; $pg_nm <= $pg_qty; $pg_nm++) {
            $data = $jsn->getcontent("/admin/orders.json?status=" . $order_status . "&limit=" . $page_size . "&page=" . $pg_nm . "&since_id=" . $maxordnumber . "&fields=id,created_at,line_items");

            foreach ($data['orders'] as $order) {
                $ordid = $order['id'];
                $orddate = date("Y-m-d",strtotime($order['created_at']));
                foreach ($order['line_items'] as $line_item) {
                    //print $line_item['id']." - ".$line_item['title']." - ".$line_item['quantity']." - ".$line_item['price']."<br>";
                    $db->insert("orders", array((string)$ordid, (string)$this->storeId, $orddate, (string)$line_item['product_id'], $line_item['quantity'], $line_item['price']),"id,storeid,date,productid,qty,price");
                }
            }

        }

        $db->disconnect();

    }

    public function calculateSales($productID){
        $db = new dbconn();
        $db->connect();

        $this->productId = $productID;

        $db->select("orders","sum(price) as amount, sum(qty) as numberofsales","storeid='".$this->storeId."' and productid='".$this->productId."'","productid");
        $cs = $db->getResult();
        //echo $db->numResults."<br>";

        if ($db->numResults == 1) {
            $this->amount = $cs['amount'];
            $this->numberOfSales = $cs['numberofsales'];
        } elseif ($db->numResults == 0) {
            $this->amount = 0;
            $this->numberOfSales = 0;
        } else {
            echo "unexpected results";
        }
    }

}