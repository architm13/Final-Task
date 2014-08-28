<?php 
define('MAXROWS', 20, true);
class DBFetcher {

	public function getCategories() {
		$con = mysqli_connect('localhost','root','', 'coupondunia');
		$cat = mysqli_query($con, "SELECT * FROM couponcategories") or die($con->error);
		$i = 0;
		while($row = mysqli_fetch_array($cat)) {
			$res[$i][0] = $row['Name'];
			$res[$i++][1] = $row['URLKeyword'];
		}
		return $res;
	}

	public function getAllCoupons($category) {
		$con = mysqli_connect('localhost','root','', 'coupondunia');
		global $id;
		$cat = mysqli_query($con, "SELECT * FROM couponcategories") or die($con->error);
		while($row = mysqli_fetch_array($cat)) {
			if($row['URLKeyword'] == $category){
				$id = $row['CategoryID'];
			}
		}
		$res = mysqli_query($con, "SELECT * FROM Coupon AS A 
			                INNER JOIN (SELECT * FROM CouponCategoryInfo AS B WHERE CategoryID = ".$id.") AS AB 
			                ON A.CouponID = AB.CouponID GROUP BY A.CouponID") or die($con->error);
		$couponInfo;
		$i = 0;
		while($row = mysqli_fetch_array($res)) {
			$couponInfo[$i]["CouponCode"] = $row['CouponCode'];
			$couponInfo[$i]["Title"] = $row['Title'];
			$couponInfo[$i]["Description"] = $row['Description'];
			$couponInfo[$i]["Expiry"] = $row['Expiry'];
			$url = mysqli_fetch_array(mysqli_query($con, "SELECT AffilateURL FROM website WHERE websiteid = ".$row['WebsiteID']));
			$couponInfo[$i]["url"] = $url['AffilateURL'];
			$i++;
		}
		mysqli_close($con);
		return $couponInfo;
	}
    
    public function displayCoupons($couponInfo, $count, $category, $pages, $filterType) {
    	$result="<table id='customers' style='width: 60%;'><th>$category($count)</th>";
		foreach($couponInfo as $coupon) {
			$url = $coupon['url'];
			if($coupon['CouponCode'] == NULL) {
				$couponcode = "No Coupon Required";
			} else {
				$couponcode = $coupon['CouponCode'];
			}
			$result = $result."<tr> <td>".
			$coupon['Title']."<br/>Coupon Code: <b>".$couponcode."</b><br/>".$coupon['Description']."<br/>".
			$coupon['Expiry']."<br/><a href='$url' target='_blank'>Access Link</a>"."<br/>".
			"</td> </tr>";
		}
		$result = $result."<tr><td>";
		$uri_part = explode('?', $_SERVER['REQUEST_URI'], 2);
		$url = $uri_part[0];
		for($i=1;$i<=$pages;$i++) {
			$result = $result."<button name='$filterType' class='page' id='paginate".$i."' value='".$i."'>$i</a>&nbsp&nbsp&nbsp";
			if(($i%10)==0) 
				$result = $result."<br/>";
		}
		$result = $result."</td></tr></table>";
		echo $result;
    }
    public function getQuery($req, $filterType, $id) {
        switch($filterType) {
            case "subcat":
            if(isset($req)) {
            	$sub_ids = join(',',$req);
			    $query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = B.CouponID AND (B.SubCategoryID IN (".
			    	      $sub_ids.")) AND B.CategoryID = ".$id.") GROUP BY A.CouponID";
		    } else {
			    $query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = B.CouponID AND CategoryID = ".
			    	      $id.") GROUP BY A.CouponID";
		    }
		    return $query;
	        break;

	        case "coupontype":
	            if(isset($req)) {
				$sub_ids = join(',',$req);
				$query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = B.CouponID AND (A.isDeal IN (".
					      $sub_ids.")) AND B.CategoryID = ".$id.") GROUP BY A.CouponID";
		    	} else {
			    $query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = AB.CouponID AND CategoryID = ".
			    	      $id.") GROUP BY A.CouponID";
		    	}
		    return $query;
	        break; 

	 		case "store":
	   			if(isset($req)) {
			        $sub_ids = join('\',\'',$req);
			        $query = "SELECT * FROM coupon AS A INNER JOIN CouponCategoryInfo AS B ON A.CouponID = B.CouponID AND B.CategoryID = ".
			                  $id." INNER JOIN Website AS C ON (C.WebsiteName IN ('".$sub_ids."') AND C.WebsiteID = A.WebsiteID) GROUP BY A.CouponID";
		        } else {
			        $query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = AB.CouponID AND CategoryID = ".
			        	      $id.") GROUP BY A.CouponID";
		        }
		        return $query;
	   			break;
	   	    default:
	   	        $query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = B.CouponID AND CategoryID = ".
	   	        	      $id.") GROUP BY A.CouponID";
	   	        return $query;
	   	        break; 
	    }

    }
    public function getLimits($pageno) {
    $limit = " LIMIT ".MAXROWS." ";
    $off = (($pageno-1) * MAXROWS);
    $offset = "OFFSET $off";
    return $limit.$offset;
    }
	public function getCoupons($category, $req, $filterType, $pageno) {
		$con = mysqli_connect('localhost','root','', 'coupondunia');
		global $id;
		$cat = mysqli_query($con, "SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'") or die($con->error);
		$row = mysqli_fetch_array($cat);
		$id = $row['CategoryID'];
	    $query = $this->getQuery($req, $filterType, $id);
		$res = mysqli_query($con, $query) or die($con->error);
		$tot = mysqli_num_rows($res);
		$limitString = $this->getLimits($pageno);
		$query = $query.$limitString;
		$res = mysqli_query($con, $query) or die($con->error);
		$total_rows = mysqli_num_rows($res);
		//$limitString = $this->getLimits($pageno);
		//$query = $query.$limitString;
		$res = mysqli_query($con, $query) or die($con->error);
		$couponInfo;
		$i = 0;
		while($row = mysqli_fetch_array($res)) {
			$couponInfo[$i]["CouponCode"] = $row['CouponCode'];
			$couponInfo[$i]["Title"] = $row['Title'];
			$couponInfo[$i]["Description"] = $row['Description'];
			$couponInfo[$i]["Expiry"] = $row['Expiry'];
			$url = mysqli_fetch_array(mysqli_query($con, "SELECT AffilateURL FROM website WHERE websiteid = ".$row['WebsiteID']));
			$couponInfo[$i]["url"] = $url['AffilateURL'];
			$i++;
		}
		mysqli_close($con);
		$this->displayCoupons($couponInfo, $tot, $category, ceil($tot/MAXROWS), $filterType);
	}
	public function fetchFilterListData($filterType, $cID, $con) {
		switch($filterType) {
		case 'subcat':
		$subcatID = mysqli_query($con, "SELECT SubCategoryID, Name FROM couponsubcategories WHERE CategoryID = ".$cID['CategoryID']) or die($con->error);
		$i = 0;
		$result = "";
		while($row = mysqli_fetch_array($subcatID)) {
			$sid = $row['SubCategoryID'];
			$sName = $row['Name'];
			$subCoupons = mysqli_query($con, "SELECT COUNT(CouponID) FROM couponcategoryinfo WHERE SubCategoryID = ".$sid) or die($con->error);
			$cCount = mysqli_fetch_assoc($subCoupons);
			$result = $result."<tr><td><input type='checkbox' name='subcat' id='ftype' value='".
			          $sid."' checked='yes' onChange='refreshContent(this);'>".$sName."(".$cCount['COUNT(CouponID)'].")</td></tr>";
		}
		mysqli_close($con);
		return $result;
		break;
		case 'store':
		$query = "SELECT WebsiteName, COUNT(DISTINCT B.CouponID) FROM Website AS A INNER JOIN Coupon AS B ON (A.WebsiteID = B.WebsiteID) 
		          INNER JOIN CouponCategoryInfo AS C ON (C.CategoryID = ".$cID['CategoryID']." AND C.CouponID = B.CouponID) GROUP BY WebsiteName";
	    // echo $query;
		$storeCount = mysqli_query($con, $query) or die($con->error);
		$i = 0;
		$result = "";
		while($row = mysqli_fetch_array($storeCount)) {
			$name = $row['WebsiteName'];
			$siteCount = $row['COUNT(DISTINCT B.CouponID)'];
			$result = $result."<tr><td><input type='checkbox' name='store' id='ftype' value='".$name."' checked='yes' onChange='refreshContent(this);'>".
			          $name."(".$siteCount.")</td></tr>";
		}
		mysqli_close($con);
		return $result;
        break;
        case 'coupontype':
        $cc = mysqli_query($con, "SELECT COUNT(DISTINCT A.CouponID) FROM coupon AS A INNER JOIN 
        	              (SELECT couponcategoryinfo.CouponID FROM couponcategoryinfo WHERE CategoryID =". $cID['CategoryID'].")
        	               AS B ON (A.isDeal = 0 AND (B.CouponID = A.CouponID))") or die($con->error);
		$dc = mysqli_query($con, "SELECT COUNT(DISTINCT A.CouponID) FROM coupon AS A INNER JOIN 
			              (SELECT couponcategoryinfo.CouponID FROM couponcategoryinfo WHERE CategoryID =". $cID['CategoryID'].") 
			              AS B ON (A.isDeal = 1 AND (B.CouponID = A.CouponID))") or die($con->error);
		$couponCount = mysqli_fetch_assoc($cc);
		$dealCount = mysqli_fetch_assoc($dc);
		$i = 0;
		$result = "";
		$result = 
		$result."<tr><td><input type='checkbox' name='coupontype' id='ftype' value='0' checked='yes' onChange='refreshContent(this);'>Coupons(".
			        $couponCount['COUNT(DISTINCT A.CouponID)'].")</td></tr>";
		$result = 
		$result."<tr><td><input type='checkbox' name='coupontype' id='ftype' value='1' checked='yes' onChange='refreshContent(this);'>Deals(".
			        $dealCount['COUNT(DISTINCT A.CouponID)'].")</td></tr>";
        mysqli_close($con);
        return $result;
		break;
	    }
	}
	public function getFilterList($category, $filterType) {
		$con = mysqli_connect('localhost', 'root', '', 'coupondunia');
		$catID = mysqli_query($con, "SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'") 
		         or die($con->error);
		$cID = mysqli_fetch_array($catID);
		return $this->fetchFilterListData($filterType, $cID, $con);
	}
}
?>
