<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>CouponDunia</title>
  
  <link rel="stylesheet" type="text/css" href="coupondisplay.css">
  <script src="jquery.js"></script>
  <script src="coupon.js" ></script>
</head>

<?php
		include('DBFetcher.php');
?>

<body>
  <h2>CouponDunia</h2>
<br/><br/>
<span>
    <select class="category" id="category" onchange="getCategory();" style="width: 160px;">
	    <option value="" style="display:none">Choose the Category</option>
    <?php
		$fetcher = new DBFetcher();
		$categories = $fetcher->getCategories();
		$len = count($categories);
		foreach($categories as $x) {
    if($x[1] == $url_parts[sizeof($url_parts)-1]) {
        $selected = "selected = true";
    } else {
        $selected = "";
    }
		echo "<option value=".$x[1]." id='$x[1]' $selected>".$x[0]."</option>";
		$i = $i + 2;
		}
		?>
    </select>

<div id='loadingmessage' style='display:none; width: 60%;'>
  <img src='loading.gif'/>
</div>
  <div id="display-area">
  <?php
      global $couponList;
      $last = $url_parts[sizeof($url_parts)-1];
      if((sizeof($url_parts))==3 AND ($last != null AND $last != 'coupondisplay')) {
          $couponsList = $fetcher->getCouponData('all', $url_parts[sizeof($url_parts)-1], $pageno);
    } else {
          echo "Please Choose a Category from Dropdown.";
    }
  if ($couponList != null && is_array($couponsList)) {
      echo $couponsList;
  }
  ?>
  </div>
  <?php
  if((sizeof($url_parts))==3 AND ($last != null AND $last != 'coupondisplay')) {
      echo $fetcher->getFilters($url_parts[sizeof($url_parts)-1]);
  }
  ?>
</body>

</html>