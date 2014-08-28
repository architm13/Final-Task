<?php
   include "DBFetcher.php";
   if(isset($_POST['filterBy'])) {
   $filterType = $_POST['filterBy'];
   $fType = $_POST['fType'];
   $fetch = new DBFetcher();
       echo $fetch->getFilterList($filterType, $fType);
	}
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