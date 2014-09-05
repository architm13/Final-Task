<?php
   /**
   This is the code which is in the interacting with the user for any request
   */
   include "DBFetcher.php";
   if(isset($_POST['filter'])) {
        $list = $_POST['checkedList'];
        $category = $_POST['category'];
        $pageno = $_POST['pageno'];
        $fetch = new DBFetcher();
        $res = $fetch->getFilterList($category, $list, $pageno);
        echo json_encode($res);
    }
    if(isset($_POST['display'])) {
        $list = $_POST['checkedList'];
        $category = $_POST['category'];
        $pageno = $_POST['pageno'];
        $fetch = new DBFetcher();
        $res = $fetch->getCouponData($list,$category, $pageno);
        echo $res;
    }
?>