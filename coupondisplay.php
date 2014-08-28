<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>CouponDunia</title>
  
  <link rel="stylesheet" type="text/css" href="coupondisplay.css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
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

<table>
  <tr>
    <th>Filter By</th>
  </tr>
  <tr>
    <td>
	<a name="subcat" id="subcat" value="subcat" href="">Sub Category</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
	<a name="ctype" id="ctype" value="ctype" href="">Coupon Type</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
	<a name="store" id="store" value="store" href="">Store</a>
	</td>
  </tr>
</table>
<div id='loadingmessage' style='display:none; width: 60%;'>
  <img src='loading.gif'/>
</div>
  <div id="display-area">
  <?php
      global $couponList;
      if((sizeof($url_parts))==3) {
          $couponsList = $fetcher->getCoupons($url_parts[sizeof($url_parts)-1], null, 'all', $pageno);
    } else {
          echo "Please Choose a Category from Dropdown.";
    }
  if ($couponList != null && is_array($couponsList)) {
      echo $couponsList;
  }
  ?>
  </div>
</div>
</div>
<table id="customers" style="float:right;width: 20%">
  <tr>
    <th>Category Name</th>
  </tr>

  <tr id="feedback"><tr>
</table>
</body>

</html>