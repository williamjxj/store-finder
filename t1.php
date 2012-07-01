<?php

define("MAPS_HOST", "maps.google.com");

get_longlat('Westminster, bc, ca');

function get_longlat($addr)
{
	$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml&key=" . KEY;
	$address = $base_url . "&q=" . urlencode($addr);

	// Use cURL to get the RSS feed into a PHP string variable.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $address);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$coord = curl_exec($ch);
	curl_close($ch);

	preg_match("/<coordinates>.*?<\/coordinates>/i",$coord, $matches);

	$m = str_replace('<coordinates>', '', $matches[0]);
	$m = str_replace('</coordinates>', '', $m);

	list($longtitude, $latitude, $altitude) = explode(",", $matches[0]);
	echo "\n$longtitude\n";
	echo "$latitude\n";
	echo "$altitude\n";

	return array($longtitude, $latitude);
}
?>
