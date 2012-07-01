<?php
// Mobile version for store_finder.

include_once('config.php');

if (isset($_GET['name']) && isset($_GET['street']) && isset($_GET['zip']) ) {
	$lat = $_GET['lat'];
	$long = $_GET['long'];
	$name = urldecode($_GET['name']);
	$street = urldecode($_GET['street']);
	$city = urldecode($_GET['city']);
	$prov = urldecode($_GET['prov']);
	$country = urldecode($_GET['country']);
	$zip = $_GET['zip'];
	$myaddr = urldecode($_GET['myaddr']);
}
else {
	die("Invalid Input.");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Store Finder - Map</title>
<link href="css/sf1.css" rel="stylesheet" />
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo KEY;?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
var map;
var directionsPanel;
var directions;

function initialize(lat, long, name,street,city,prov,country,zip,myaddr) {
  var src = myaddr;
  var desc = street+','+city+','+prov+','+zip;
  map = new GMap2(document.getElementById("map"));
  directionsPanel = document.getElementById("map_text_div");
  map.setCenter(new GLatLng(parseFloat(lat), parseFloat(long)), 3);
  directions = new GDirections(map, directionsPanel);
  directions.load("from: "+ src + " to: " + desc);
}

function showMap(address) {
  geocoder.getLatLng(
	address,
	function(point) {
	  if (!point) {
		alert(address + " not found");
	  } else {
		map.setCenter(point, 13);
		var marker = new GMarker(point);
		map.addOverlay(marker);
		var html = '<b>My Location:</b><br/>' + address;
		marker.openInfoWindowHtml(html);
	  }
	}
  );
}

</script>
</head>
<body onload="initialize('<?php echo $lat;?>','<?php echo $long;?>', '<?php echo $name;?>','<?php echo $street;?>', '<?php echo $city;?>', '<?php echo $prov;?>','<?php echo $country;?>','<?php echo $zip;?>','<?php echo $myaddr;?>')" onunload="GUnload()">
<table width="480px" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td><div id="map" style="width: 480px; height: 480px"></div></td>
  </tr>
  <tr>
    <td><div id="map_text_div"></div></td>
  </tr>
</table>
</body>
</html>
