<?php
include_once('config.php');

mysql_pconnect(HOST, USER, PASS) or die("Can't Connect MySQL Server:".mysql_error());
mysql_select_db(DB_NAME);

//$query = "select sid, CONCAT_WS(',',street,city,prov,zip,country) address from stores where (lat is null or long is null) and country in ('canada', 'united kindom')";
//$query = "select sid, CONCAT_WS(',',zip,country) address from stores where (lat is null or long is null)";
$query = "select sid, CONCAT_WS(',',street,city,prov,zip,country) address from stores";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

// Initialize delay in geocode speed
$delay = 0;
$base_url = "http://" . MAPS_HOST . "/maps/geo?output=csv&key=" . KEY;

// Iterate through the rows, geocoding each address
while ($row = @mysql_fetch_assoc($result)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $address = $row["address"];
    $id = $row["sid"];
    $request_url = $base_url . "&q=" . urlencode($address);
    $csv = file_get_contents($request_url) or die("url not loading");
	//echo "<pre>"; print_r($csv); echo "</pre>";

    $csvSplit = split(",", $csv);
    $status = $csvSplit[0];	// 200
    // $lat = $csvSplit[2]; $lng = $csvSplit[3];

    if (strcmp($status, "200") == 0) {
      // successful geocode
      $geocode_pending = false;
      $lat = $csvSplit[2];
      $lng = $csvSplit[3];

      $query = sprintf("UPDATE stores " .
             " SET lat = %s, lng = %s " .
             " WHERE sid = %s LIMIT 1;",
             mysql_real_escape_string($lat),
             mysql_real_escape_string($lng),
             mysql_real_escape_string($id));
      $update_result = mysql_query($query);
      if (!$update_result) {
        die("Invalid query: " . mysql_error());
      }

    } else if (strcmp($status, "620") == 0) {
      // sent geocodes too fast
      $delay += 100000;
    } else {
      // failure to geocode
      $geocode_pending = false;
      echo "Address: [" . $address . "] failed to geocoded. ";
      echo "Received status " . $status . "\n";
    }

    usleep($delay);
  }

}
?>
