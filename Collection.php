<?php

require_once ("Shop.php");

class Collection
{

    private $storeId;

    function __construct(){
        $this->storeId = Shop::$id;
    }

    public function retrieveAll(){

        $db = new dbconn();
        $db->connect();

        //retrieve list of connections
        $jsn = new shp_rst();
        $data = $jsn->getcontent("/admin/custom_collections.json?fields=id,title,sort_order");

        //loop through the list, insert new collections and update existing ones
        foreach ($data['custom_collections'] as $collection) {
            //echo $collection['id'] . " - " . $collection['title'] . " - " . $collection['sort_order'] . "<br>";

            $db->select("collections","id","storeid='".$this->storeId."' and id='".(string)$collection['id']."'");
            $mon = $db->getResult();

            if ($db->numResults == 1) {
                $db->update("collections",array("title"=>$collection['title'],"sortorder"=>$collection['sort_order']),array("id",(string)$collection['id'],"storeid",(string)$this->storeId));
            } elseif ($db->numResults == 0) {
                $db->insert("collections", array((string)$collection['id'], (string)$this->storeId, $collection['title'], $collection['sort_order']),"id,storeid,title,sortorder");
            } else {
                echo "unexpected results";
            }


        }

        $db->disconnect();

    }
}