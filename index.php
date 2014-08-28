<?php
    $parts = explode("?", $_SERVER['REQUEST_URI'], 2);
    $url_parts = explode("/", $parts[0]);
    if(!isset($_POST['pageno'])) {
    if(isset($parts[1])) {
        $arg = explode("=", $parts[1]);
        $pageno = $arg[sizeof($arg)-1];
    } else {
    	$pageno = 1;
    }
	} else {
		$pageno = $_POST['pageno']; 
	}
    include "coupondisplay.php";
?>