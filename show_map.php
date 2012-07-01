<?php
// Mobile version for store_finder.

include_once('config.php');

if (isset($_GET['long']) && isset($_GET['lat']) && isset($_GET['name']) ) {
	$lat = $_GET['lat'];
	$long = $_GET['long'];
	$name = urldecode($_GET['name']);
	$street = urldecode($_GET['street']);
	$city = urldecode($_GET['city']);
	$prov = urldecode($_GET['prov']);
	$country = urldecode($_GET['country']);
	$zip = $_GET['zip'];
	$distance = $_GET['distance'];
	$phone = $_GET['phone'];
	$fax = $_GET['fax'];
	$email = urldecode($_GET['email']);
	$web = urldecode($_GET['web']);
	$image = $_GET['image'];
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
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo KEY;?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
var map;

function initialize(lat, long, name,street,city,prov,country,zip,distance,phone,fax,email,web,image) {
  if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("map"));
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(parseFloat(lat), parseFloat(long)), 13);
	map.addOverlay(createMarker(lat, long, name,street,city,prov,country,zip,distance,phone,fax,email,web,image));
  }
}

function createMarker(lat, long, name,street,city,prov,country,zip,distance,phone,fax,email,web,image) {
  var point = new GLatLng(parseFloat(lat),parseFloat(long));
  var marker = new GMarker(point);
  
  var html = '<b>Name:</b> ' + name + '<br/>';
	html += '<b>Street:</b> ' + street + '<br/>';
	html += '<b>City:</b> ' + city + ', '+ prov + ', ' + zip + '<br/>';
	html += '<b>Country:</b> ' + country + '<br/>';
	html += '<b>Distance:</b> ' + distance + '<br/>';
	if (phone) {
		html += '<b>Phone:</b> ' + phone + '<br/>';
	}
	if (fax) {
		html += '<b>Fax:</b> ' + fax + '<br/>';
	}
	if (email) {
		html += '<b>Email:</b> ' + email + '<br/>';
	}
	if (web) {
		html += '<b>Web:</b> ' + web + '<br/>';
	}
	if (image) {
		html += '<img src="store_images/'+image+'" width="110" height="80" border="0" alt="'+image+'" title="'+image+'" />';
	}

  GEvent.addListener(marker, 'click', function() {
	marker.openInfoWindowHtml(html);
  });
  return marker;
}

</script>
</head>

<body onload="initialize('<?php echo $lat;?>', '<?php echo $long;?>', '<?php echo $name;?>',
'<?php echo $street;?>', '<?php echo $city;?>', '<?php echo $prov;?>','<?php echo $country;?>','<?php echo $zip;?>',
'<?php echo $distance;?>', '<?php echo $phone;?>', '<?php echo $fax;?>',
'<?php echo $email;?>', '<?php echo $web;?>', '<?php echo $image;?>')" onunload="GUnload()">
<div id="map" style="width: 480px; height: 480px"></div>
</body>
</html>
