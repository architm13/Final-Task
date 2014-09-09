<?php 
define('MAXROWS', 20, true);
class DatabaseSingleton {
  // [Singleton]
  private static $instance = null;
  public static function getInstance() {
    if (!self::$instance)
    {
      self::$instance = new self();
    }
    return self::$instance;
  }
  private function __clone(){}

  private $connection = null;

  private function __construct() {
    $this->connection = mysqli_connect('localhost','root','', 'coupondunia');
  }
  
  public function executeQuery($query) {
  	$result = mysqli_query($this->connection, $query);
  	return $result;
  }
}
class DBFetcher {
    /**
    This function is responsible forgetting all the categories in which coupons are avaialable.
    It is populating it to the Dropdown box.
    */
	public function getCategories() {
		$instance = DatabaseSingleton::getInstance();
        $query1 = "SELECT * FROM couponcategories";
        $cat = $instance->executeQuery($query1);
		$i = 0;
		while($row = mysqli_fetch_array($cat)) {
			$res[$i][0] = $row['Name'];
			$res[$i++][1] = $row['URLKeyword'];
		}
		return $res;
	}
    /**
    This Method is Handling the display of Coupons on the page after fetching the relevant information from the database.
    */
    public function displayCoupons($couponInfo, $count, $category, $pages, $pageno) {
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
		$result = $result."<tr><td>Page ".$pageno." of ".$pages." Pages";
		$uri_part = explode('?', $_SERVER['REQUEST_URI'], 2);
		$url = $uri_part[0];
		for($i=1;$i<=$pages;$i++) {
			$result = $result."<button name='abcd' class='page' id='paginate".$i."' value='".$i."'>$i</a>&nbsp&nbsp&nbsp";
			if(($i%10)==0) 
				$result = $result."<br/>";
		}
		$result = $result."</td></tr></table>";
		echo $result;
    }

    /**
    For getting the Pagination limits which will be appended to the query
    */
    public function getLimits($pageno) {
    $limit = " LIMIT ".MAXROWS." ";
    $off = (($pageno-1) * MAXROWS);
    $offset = "OFFSET $off";
    return $limit.$offset;
    }
    public function queryCreator($list, $id, $limits) {
    	if(is_array($list)) {
    	$cl = array();
        if(in_array('coupon', $list[1])) {
        	$cl[0] = 0;
        }
        if(in_array('deal', $list[1])) {
        	$cl[1] = 1;
        }
        $len = sizeof($list[0]);
        for($i=0;$i<$len;$i++) {
        	$ex = explode("_", $list[0][$i]);
        	$sl[$i] = $ex[sizeof($ex)-1];
        }
        $subList = join(',',$sl);
        $coupList = join(',',$cl);
        $storeList = join('\',\'',$list[2]);

    $query = "SELECT B.SubCategoryID, A.Name,C.CouponCode, C.Title, C.Description, C.Expiry, C.WebsiteID, D.AffilateURL FROM couponsubcategories AS A INNER JOIN couponcategoryinfo AS B 
		            INNER JOIN Coupon AS C INNER JOIN Website AS D ON (B.SubcategoryID IN (".$subList.") 
		            AND D.WebsiteID = C.WebsiteID AND D.WebsiteName IN ('".$storeList."') AND C.CouponID = B.couponID AND 
		            B.CategoryID = ".$id." AND A.SubCategoryID = B.SubCategoryID AND C.isDeal IN 
			        (".$coupList.")) GROUP BY C.CouponID";
	} else {
		$query = "SELECT * FROM Coupon AS A INNER JOIN CouponCategoryInfo AS B ON (A.CouponID = B.CouponID AND CategoryID = ".$id.") GROUP BY A.CouponID";
	}
    return $query;
    }
    /**
    Main Function to get the coupon information and return to the display page
    */
    public function getCouponData($list, $category, $pageno) {
    	$instance = DatabaseSingleton::getInstance();
        $cat = $instance->executeQuery("SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'");
		$row = mysqli_fetch_array($cat);
		$id = $row['CategoryID'];
        $limits = $this->getLimits($pageno);
        if(!isset($list[0]) || !isset($list[1]) || !isset($list[2])) {
        	$query = $this->queryCreator('all', $id, $limits);
        } else {
        $query = $this->queryCreator($list, $id, $limits);
        }
        $res1 = $instance->executeQuery($query);
        $tot = mysqli_num_rows($res1);
        $query = $query.$limits;
        $res = $instance->executeQuery($query);
        $couponInfo;
		$i = 0;
		while($row = mysqli_fetch_array($res)) {
			$couponInfo[$i]["CouponCode"] = $row['CouponCode'];
			$couponInfo[$i]["Title"] = $row['Title'];
			$couponInfo[$i]["Description"] = $row['Description'];
			$couponInfo[$i]["Expiry"] = $row['Expiry'];
			$url = mysqli_fetch_array($instance->executeQuery("SELECT AffilateURL FROM website WHERE websiteid = ".$row['WebsiteID']));
			$couponInfo[$i]["url"] = $url['AffilateURL'];
			$i++;
		}
		return $this->displayCoupons($couponInfo, $tot, $category, ceil($tot/MAXROWS), $pageno);

    }

	/**
	Responsible for getting the type of subcategories,stores,coupontype on the right sidebar of the page
	*/
	public function fetchFilterListData($filterType, $cID) {
		$instance = DatabaseSingleton::getInstance();

		switch($filterType) {
		case 'subcat':
		$subcatID = $instance->executeQuery("SELECT SubCategoryID, Name FROM couponsubcategories WHERE CategoryID = ".$cID['CategoryID']);
		$i = 0;
		$result = "";
		while($row = mysqli_fetch_array($subcatID)) {
			$sid = $row['SubCategoryID'];
			$sName = $row['Name'];
			$query = "SELECT COUNT(CouponID) FROM couponcategoryinfo WHERE SubCategoryID = ".$sid;
			$subCoupons = $instance->executeQuery($query);
			$cCount = mysqli_fetch_assoc($subCoupons);
			$result = $result."<tr><td><input type='checkbox' name='subcat' value='sub_".$sid."' checked='yes' onChange='refreshContent(this);'>".
			          $sName."(<span id = 'sub_".$sid."'>".$cCount['COUNT(CouponID)']."</span>)</td></tr>";
		}
		return $result;
		break;

		case 'store':
		$query = "SELECT WebsiteName, COUNT(DISTINCT B.CouponID) FROM Website AS A INNER JOIN Coupon AS B ON (A.WebsiteID = B.WebsiteID) 
		          INNER JOIN CouponCategoryInfo AS C ON (C.CategoryID = ".$cID['CategoryID']." AND C.CouponID = B.CouponID) GROUP BY WebsiteName";
		          $storeCount = $instance->executeQuery($query);
		$i = 0;
		$result = "";
		while($row = mysqli_fetch_array($storeCount)) {
			$name = $row['WebsiteName'];
			$siteCount = $row['COUNT(DISTINCT B.CouponID)'];
			$result = $result."<tr><td><input type='checkbox' name='store' value='".$name."' checked='yes' onChange='refreshContent(this);'>".
			          $name."(<span id = '".$name."'>".$siteCount."</span>)</td></tr>";
		}
		return $result;
        break;

        case 'coupontype':
        $cc = $instance->executeQuery("SELECT COUNT(DISTINCT A.CouponID) FROM coupon 
        	               AS A INNER JOIN (SELECT couponcategoryinfo.CouponID FROM couponcategoryinfo WHERE CategoryID =". $cID['CategoryID'].")
        	               AS B ON (A.isDeal = 0 AND (B.CouponID = A.CouponID))");
		$dc = $instance->executeQuery("SELECT COUNT(DISTINCT A.CouponID) FROM coupon AS A INNER JOIN (SELECT couponcategoryinfo.CouponID FROM couponcategoryinfo WHERE CategoryID =". $cID['CategoryID'].") 
			              AS B ON (A.isDeal = 1 AND (B.CouponID = A.CouponID))");
		$couponCount = mysqli_fetch_assoc($cc);
		$dealCount = mysqli_fetch_assoc($dc);
		$i = 0;
		$result = "";
		$result = 
		$result."<tr><td><input type='checkbox' name='coupontype' id='ftype' value='coupon' checked='yes' onChange='refreshContent(this);'>Coupons(<span id = 'coupon'>".
			        $couponCount['COUNT(DISTINCT A.CouponID)']."</span>)</td></tr>";
		$result = 
		$result."<tr><td><input type='checkbox' name='coupontype' id='ftype' value='deal' checked='yes' onChange='refreshContent(this);'>Deals(<span id = 'deal'>".
			        $dealCount['COUNT(DISTINCT A.CouponID)']."</span>)</td></tr>";
        return $result;
		break;
	    }
	}
    /**
    All Filters loaded in one time
    */
    function getFilters($category) {
    	$instance = DatabaseSingleton::getInstance();
        $query1 = "SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'";
        $catID = $instance->executeQuery($query1);
		$cID = mysqli_fetch_array($catID);

		$f1 = $this->fetchFilterListData('subcat', $cID);
		$f2 = $this->fetchFilterListData('coupontype', $cID);
		$f3 = $this->fetchFilterListData('store', $cID);

  return "<div id='filters' style='float: right;vertical-align: top;'><div id='sub-categories'><table id='customers' style='float:right;'>
          <tr><th>By Sub Categories</th></tr>".$f1."</table></div><div id='coupon-type'><table id='customers' style='float:right;'>
          <tr><th>By Coupon Type</th></tr>".$f2."</table></div><div id='store-type'><table id='customers' style='float:right;'>
          <tr><th>By Store</th></tr>".$f3."</table></div></div>";
    }


    /**
	Responsible for getting the type of subcategories,stores,coupontype on the right sidebar of the page
	*/
	public function filterData($filterType, $cID, $list) {
		if(($filterType == 'subcat' && !empty($list[0])) || ($filterType == 'coupontype' && !empty($list[1])) || ($filterType == 'store' && !empty($list[2]))) {
		$instance = DatabaseSingleton::getInstance();
		$cl = array();
		$sl = array();
        if(!empty($list[1])) {
        if(in_array('coupon', $list[1])) {
        	$cl[0] = 0;
        }
        if(in_array('deal', $list[1])) {
        	$cl[1] = 1;
        }
        }
        if(!empty($list[0])) {
        $len = sizeof($list[0]);
        for($i=0;$i<$len;$i++) {
        	$ex = explode("_", $list[0][$i]);
        	$sl[$i] = $ex[sizeof($ex)-1];
        }
        }
        $subList = join(',',$sl);
        $coupList = join(',',$cl);
        $storeList = join('\',\'',$list[2]);
		switch($filterType) {
		case 'subcat':
		$query34 = "SELECT B.SubCategoryID, A.Name,COUNT(B.CouponID) FROM couponsubcategories AS A INNER JOIN couponcategoryinfo AS B 
		            INNER JOIN Coupon AS C INNER JOIN Website AS D ON (B.SubcategoryID IN (".$subList.") AND D.WebsiteID = C.WebsiteID AND D.WebsiteName IN ('".$storeList."') 
			        AND C.CouponID = B.couponID AND B.CategoryID = ".$cID['CategoryID']." AND A.SubCategoryID = B.SubCategoryID AND C.isDeal IN 
			        (".$coupList.")) GROUP BY B.SubCategoryID";
        $subcatID = $instance->executeQuery($query34);
		$i = 0;
		$result = array();
		while($row = mysqli_fetch_array($subcatID)) {
			$result["sub_".$row['SubCategoryID']] = $row['COUNT(B.CouponID)'];
			$i++;
		}
		if($i < sizeof($list[2])) {
			foreach($list[0] as $value) {
				if(!isset($result[$value]))
				   $result["sub_".$value] = 0;
			}
		}
		return $result;
		break;

		case 'store':
		$query = "SELECT WebsiteName, COUNT(DISTINCT B.CouponID) FROM Website AS A INNER JOIN Coupon AS B ON (A.WebsiteID = B.WebsiteID) 
		INNER JOIN CouponCategoryInfo AS C ON (A.WebsiteName IN ('".$storeList."') AND C.CategoryID = ".$cID['CategoryID']." AND C.CouponID = B.CouponID 
			AND C.SubCategoryID IN (".$subList.") AND B.isDeal IN (".$coupList.")) GROUP BY WebsiteName";
        $storeCount = $instance->executeQuery($query);
		$i = 0;
		$result = array();
		while($row = mysqli_fetch_array($storeCount)) {
			$result[$row['WebsiteName']] = $row['COUNT(DISTINCT B.CouponID)'];
			$i++;
		}
		if($i < sizeof($list[2])) {
			foreach($list[2] as $value) {
				if(!isset($result[$value]))
				    $result[$value] = 0;
			}
		}
		return $result;
        break;

        case 'coupontype':
        $result = array();
        if(in_array('coupon', $list[1])) {
        $query_cc = "SELECT COUNT(DISTINCT A.CouponID) FROM coupon AS A INNER JOIN 
                    (SELECT couponcategoryinfo.CouponID, couponcategoryinfo.SubCategoryID FROM couponcategoryinfo WHERE CategoryID = ".$cID['CategoryID'].") AS B 
                    INNER JOIN Website AS C ON (A.isDeal IN (".$coupList.") AND C.WebsiteID = A.WebsiteID AND C.WebsiteName IN ('".$storeList."') AND A.isDeal = 0 AND 
                    	(B.CouponID = A.CouponID) AND B.SubCategoryID IN (".$subList."))";
        $cc = $instance->executeQuery($query_cc);
       	$couponCount = mysqli_fetch_assoc($cc);
       	$result['coupon'] = $couponCount['COUNT(DISTINCT A.CouponID)'];
        }
        if(in_array('deal', $list[1])) {
        $query_dc = "SELECT COUNT(DISTINCT A.CouponID) FROM coupon AS A INNER JOIN 
                    (SELECT couponcategoryinfo.CouponID, couponcategoryinfo.SubCategoryID FROM couponcategoryinfo WHERE CategoryID = ".$cID['CategoryID'].") AS B 
                    INNER JOIN Website AS C ON (A.isDeal IN (".$coupList.") AND C.WebsiteID = A.WebsiteID AND C.WebsiteName IN ('".$storeList."') AND A.isDeal = 1 AND 
                    (B.CouponID = A.CouponID) AND B.SubCategoryID IN (".$subList."))";
		$dc = $instance->executeQuery($query_dc);
		$dealCount = mysqli_fetch_assoc($dc);
		$result['deal'] = $dealCount['COUNT(DISTINCT A.CouponID)'];
        }
        return $result;
		break;
	 }
	}
	 else {
		$result =array();
		return $result;
    }
}
/**
    Retrieve the count of values to be displayed on the unchecked boxes.
    */
     public function getUnFilterList($category, $list, $unList) {
		$instance = DatabaseSingleton::getInstance();
		$catID = $instance->executeQuery("SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'");
		$cID = mysqli_fetch_array($catID);
        $id = $cID;
        
        for($i=0;$i<3;$i++) {
        	if(!isset($unList[$i]))
        		$unList[$i] = null;
        	if(!isset($list[$i]))
        		$list[$i] = null;
        }
        $newList = array();
        $newList[0] = $unList[0];
        $newList[1] = $list[1];
        $newList[2] = $list[2];
        //print_r($newList);
		$f1 = $this->filterData('subcat', $id, $newList);
		$newList1 = array();
		$newList1[0] = $list[0];
        $newList1[1] = $unList[1];
        $newList1[2] = $list[2];
		$f2 = $this->filterData('coupontype', $id, $newList1);
		$newList2 = array();
		$newList2[0] = $list[0];
        $newList2[1] = $list[1];
        $newList2[2] = $unList[2];
		$f3 = $this->filterData('store', $id, $newList2);        
		$result = array_merge($f1, array_merge($f2, $f3));
		$res = array();
		$x = 0;
		foreach($result as $key => $value) {
				$res[$key] = $value;    
        }
        return $res;
}

	/**
	main function for displaying the filterList to the user
	*/
	public function getFilterList($category, $list) {
		$instance = DatabaseSingleton::getInstance();
		$catID = $instance->executeQuery("SELECT CategoryID FROM couponcategories WHERE URLKeyword = '".$category."'");
		$cID = mysqli_fetch_array($catID);
		$f1 = $this->filterData('subcat', $cID, $list);
		$f2 = $this->filterData('coupontype', $cID, $list);
		$f3 = $this->filterData('store', $cID, $list);
		$result = array_merge($f1, array_merge($f2, $f3));
        return $result;
    }
}
?>
