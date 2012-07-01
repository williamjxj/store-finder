<?php
// markers.php?long='+l1+'&lat='+l2+'&radius='+radius+'&records='+records+'&unit='+unit+'&addr='+encodeURIComponent(addr)

include_once('config.php');

$distance_ary = array();

mysql_pconnect(HOST, USER, PASS) or die("Can't Connect MySQL Server:".mysql_error());
mysql_select_db(DB_NAME);

/*
if (! (isset($_GET['addr']) && isset($_GET['lat']) && isset($_GET['long']))) {
	die ("No address, latitude, longtitude input, can't execute.");
}
*/
if (isset($_GET['long']) && isset($_GET['lat'])) {
	if ($_GET['long']==0 || $_GET['lat']==0) {
		list($lng2, $lat2) = get_longlat($_GET['addr']);
	}
	else {
		$lng2 = $_GET['long'];
		$lat2 = $_GET['lat'];
	}
	$radius =  $_GET['radius'];
	$records =  $_GET['records'];
	$unit =  $_GET['unit'];
}

if (!$lng2) {
	$lng2 = '-122.9145880';
	$lat2 = '49.2037050'; //$lat2=0; $lng2=0; 
}
/*
if (!$radius) { $radius = RADIUS; }
if (!$unit) { $unit = UNIT; }
if (!$records) { $records = RECORDS; }
else {
	echo "<pre>";print_r($_GET);echo "</pre>";
}
*/

$lines = file(DB_FILE);

foreach ($lines as $ary) {
	list ($sid, $lat, $long) = explode(",", $ary);
	// echo "lat: [".$lat."], long: [".$long."]\n";
	$a = get_distance($lat,$long, $lat2, $lng2, $unit);
	// $a = c2($lat,$long, $lat2, $lng2, $unit);
	// array_push($distance_ary, array($sid, $a));
	array_push($distance_ary, array($a, $sid));
}
sort($distance_ary);
// array_multisort($distance_ary, SORT_NUMERIC, SORT_DESC);

if ($records) {
	// $dary = array_slice($distance_ary, 0, $records);
	$dary = array_slice($distance_ary, 0, $records);
	// echo "<pre>"; print_r($dary); echo "</pre>"; exit;
}
else {
	$dary = $distance_ary;
}

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

header("Content-type: text/xml");

foreach ($dary as $ary) {
	$distance = $ary[0];
	if ($radius && ($distance > $radius)) {
		echo $dom->saveXML();
		exit;
	}
	$sid =  $ary[1];
	// $sql = "SELECT name, street, city, prov, country, zip, lat, lng FROM stores"; // limit 0,30
	$sql = "SELECT name, street, city, prov, country, zip, lat, lng, image, phone, fax, web, email FROM stores where sid = " . $sid;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result); 
	// echo "<pre>\n"; print_r($row); echo "</pre>\n";

	$node = $dom->createElement("marker");
	$newnode = $parnode->appendChild($node);
	$newnode->setAttribute("name", $row['name']);
	$newnode->setAttribute("street", $row['street']);
	$newnode->setAttribute("city", $row['city']);
	$newnode->setAttribute("prov", $row['prov']);
	$newnode->setAttribute("country", $row['country']);
	$newnode->setAttribute("zip", $row['zip']);
	$newnode->setAttribute("lat", $row['lat']);
	$newnode->setAttribute("lng", $row['lng']);
	$newnode->setAttribute("image", $row['image']);
	$newnode->setAttribute("sid", $sid);
	$newnode->setAttribute("distance", (string)round($distance, 2).$unit);
	$newnode->setAttribute("phone", $row['phone']);
	$newnode->setAttribute("fax", $row['fax']);
	$newnode->setAttribute("web", $row['web']);
	$newnode->setAttribute("email", $row['email']);
}

echo $dom->saveXML();

exit;


///////////////////////////////////

function get_distance($lat1, $lon1, $lat2, $lon2,  $units = "K") 
{
$a = pow( (sin(0.0174 * ($lat2 -  $lat1) / 2)) ,2) +
		cos(0.0174  * $lat1) *
		cos(0.0174  * $lat2) *
		pow(
			(sin(0.0174 * ($lon2 - $lon1)/2))
	 ,2);
$c = 2 * atan2(sqrt($a), sqrt(1-($a)));
//R (Earth  Radius) = 3956.0 mi = 3437.7 nm = 6367.0 km
switch($units)  {
	case 'M': // STATUTE MILES
	$R = 3956.0;
	break;
case  'N': // NAUTICAL
	$R  = 3437.7;
	break;
case  'K': // KILOMETERS
	$R  = 6367.0;
	break;
}
return ($R * $c);
}


function c2($lat1, $lon1, $lat2, $lon2, $unit = 'K') 
{
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $dist = $dist * 60 * 1.1515;

  if ($unit == "K") {
    $dist *= 1.609344;
  } else if ($unit == "M") {
    $dist *= 0.8684;
  }

  return round($dist, 1);
}


?>
