<?php
// $key = "ABQIAAAAg3TBRy-RVpoUD4YTi1E57BT582rhQ-jT4ejVXzqs9fVYq9lGGhQOUoXw71_yVYCMqJMUkb9tcTK4vg";

define("KEY", "ABQIAAAAg3TBRy-RVpoUD4YTi1E57BT582rhQ-jT4ejVXzqs9fVYq9lGGhQOUoXw71_yVYCMqJMUkb9tcTK4vg");
define("MAPS_HOST", "maps.google.com");
define("HOST", "localhost");
define("USER", "store_finder");
define("PASS", "william");
define("DB_NAME", "store_finder");

define("RADIUS", 10);
define("UNIT", "K");
define("RECORDS", 10);
define("DB_FILE", "sf_data.csv");

function get_longlat($addr)
{
	$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml&key=" . KEY;
	//$address = $base_url."http://maps.google.com/maps/geo?q=$addr&output=xml&key=$key";
	$address = $base_url . "&q=" . urlencode($addr);

	// Use cURL to get the RSS feed into a PHP string variable.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $address);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$coord = curl_exec($ch);
	curl_close($ch);
	
	$xml = new SimpleXMLElement($coord);
	
	list($longitude, $latitude, $altitude) = explode(",", $xml->Response->Placemark->Point->coordinates);

	return array($longitude, $latitude);
}


?>
