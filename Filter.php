<?php
   /**
   This is the code which is in the interacting with the user for any request
   */
   include "DBFetcher.php";
   /**
   For getting the Filter List
   */
   if(isset($_POST['filterBy'])) {
   $filterType = $_POST['filterBy'];
   $fType = $_POST['fType'];
   $fetch = new DBFetcher();
       echo $fetch->getFilterList($filterType, $fType);
	}

    /**
    For getting the Coupon dataand displaying it on the screen
    */
	if(isset($_POST['mode'])) {
		if(isset($_POST['checkedList'])) {
		$list = $_POST['checkedList'];
		} else {
			$list = null;
		}
	$filterType = $_POST['ftype'];
	$pageno = $_POST['pageno'];
	$fetch = new DBFetcher();
	   echo $fetch->getCoupons($_POST['cid'], $list, $filterType, $pageno);
	}
?>