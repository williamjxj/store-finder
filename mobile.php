<?php
// Mobile version for store_finder.

include_once('config.php');

$conn = mysql_pconnect(HOST, USER, PASS) or die("Can't Connect MySQL Server:".mysql_error());
mysql_select_db(DB_NAME);

$myaddr = '';
if (isset($_POST['q'])) {
	$myaddr = $_POST["q"]; //v7c3l9, or 7420 No 2 Road, Richmond, BC
	list($long, $lat) = get_longlat($myaddr);
	display($lat, $long);
}
else {
	$ip = $_SERVER['REMOTE_ADDR'];
	$myaddr = get_location_by_ip($ip);
	initial();
}
exit;

/////////////////////////////

function get_form() 
{
?>
<br />
<form style="display:inline;" action="<?php echo $_SERVER['PHP_SELF'];?>" id="search_form">
  &nbsp;
  <input type="text" name="q" size="20" value="Please Enter Your Postal Or Zip Code" onblur="if(this.value==''){value='Please Enter Your Postal Or Zip Code';}" onfocus="if(this.value=='Please Enter Your Postal Or Zip Code'){this.value='';}" class="text1" />
  <input type="image" src="store_images/search-button.gif" width="22" height="21" style="vertical-align:middle;">
</form>
<?php
}

function initial()
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<TITLE>Store Finder - Map</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<link href="css/sf1.css" rel="stylesheet" />
<script src="js/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo KEY;?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
document.observe('dom:loaded', function() {
	$('search_form').observe('submit', function(event) {
		event.stop();
		var form = event.element();
		var addr = form.q.value;
		if (addr.match(/^\s+$/) || addr == "" || /^Please Enter/.test(addr)) {
			alert('Please input a valid address or postal code.');
			form.q.focus();
			return false;
		}
		// alert(Object.inspect(Form.serialize(form))); 
		new Ajax.Updater('div_content', form.action, {
			parameters: Form.serialize(form)
		});
	});
});
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" bgcolor="#161616">
<?php  get_form();?>
<br />
<div id="div_content"></div>
</body>
</html>
<?php
}

function display($lat2, $lng2)
{
	global $myaddr;
	$distance_ary = array();
	$lines = file(DB_FILE);
	$unit = 'KM';
	$records = 10;
	
	foreach ($lines as $ary) {
		list ($sid, $lat, $long) = explode(",", $ary);
		$a = get_distance($lat, $long, $lat2, $lng2, $unit);
		array_push($distance_ary, array($a, $sid));
	}
	sort($distance_ary);
// echo "<pre>";print_r($distance_ary);echo "</pre>";	
	if ($records) {
		$dary = array_slice($distance_ary, 0, $records);
	}
	else {
		$dary = $distance_ary;
	}

	echo "<div>";

	foreach ($dary as $ary) {
		$distance = $ary[0];
		$sid =  $ary[1];
	
		$sql="SELECT name, street, city, prov, country, zip, lat, lng, image, phone, fax, web, email FROM stores where sid = " . $sid;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result); 

		$query_str1 = 'lat='.$row['lat']
			.'&long='.$row['lng']
			.'&name='.urlencode($row['name'])
			.'&street='.urlencode($row['street'])
			.'&city='.urlencode($row['city'])
			.'&prov='.urlencode($row['prov'])
			.'&country='.urlencode($row['country'])
			.'&zip='.$row['zip']
			.'&distance='.(string)round($distance, 2).$unit
			.'&phone='.$row['phone']
			.'&fax='.$row['fax']
			.'&email='.urlencode($row['email'])
			.'&web='.urlencode($row['web'])
			.'&image='.($row['image']);
			
		$query_str2 = 'lat='.$row['lat']
			.'&long='.$row['lng']
			.'&name='.urlencode($row['name'])
			.'&street='.urlencode($row['street'])
			.'&city='.urlencode($row['city'])
			.'&prov='.urlencode($row['prov'])
			.'&country='.urlencode($row['country'])
			.'&zip='.$row['zip']
			.'&myaddr='.urlencode($myaddr);
		
		// echo "<br>$query_str<br>\n";
?>
<ul  class="sf_txt">
  <li class="sf_ttl"><span class="mainbdytxtttl2plus">+</span>&nbsp;<?php echo $row['name']; ?></li>
  <li>
    <label><?php echo $row['street'];?></label>
  </li>
  <li>
    <label><?php echo $row['city'].', '.$row['prov'].', '.$row['zip']; ?></label>
  </li>
  <li>
    <label><?php echo $row['country'].', '.(string)round($distance, 2).' '.$unit; ?></label>
  </li>
  <?php   if ($row['phone']) { ?>
  <li>
    <label>Phone:&nbsp;<?php echo $row['phone']; ?></label>
  </li>
  <?php }   if ($row['fax']) { ?>
  <li>
    <label>Fax:&nbsp;<?php echo $row['fax']; ?></label>
  </li>
  <?php }   if ($row['email']) { ?>
  <li>
    <label>Email:&nbsp;<?php echo $row['email']; ?></label>
  </li>
  <?php }   if ($row['web']) { ?>
  <li>
    <label>Web:&nbsp;<?php echo $row['web']; ?></label>
  </li>
  <?php } ?>
  <li>
    <label><a href="show_map.php?<?php echo $query_str1;?>">Map</a></label>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <label><a href="show_direction.php?<?php echo $query_str2;?>">Direction</a></label>
  </li>
  <li>
    <div style="height:0;font:0/0 serif;border-bottom:1px dashed #ccc; padding:4px 0"></div>
  </li>
</ul>
<?php 
	}
	echo "</div>";
}	

function get_location_by_ip($ip) 
{
	if (! $ip) {
		die("No IP address identified at " . __FILE__ . "<br/>\n");
	}

	$ipArray = explode('.', $ip);
	$ipNumber = $ipArray[0]*(256*256*256) +	$ipArray[1]*(256*256) +	$ipArray[2]*(256) +	$ipArray[3];
	
	$sql = "SELECT country_code, country_name, region, city FROM ip_country_region_city WHERE IP_FROM < $ipNumber AND IP_TO > $ipNumber";
	
	$res = mysql_query($sql);
	
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		$addr=ucfirst(strtolower($row['city'])).','.ucfirst(strtolower($row['region'])).','.ucfirst(strtolower($row['country_code']));
	} else {
		$addr = '';
	}
	
	return  urlencode($addr);
}

function get_distance($lat1, $lon1, $lat2, $lon2,  $units = "KM") 
{
	$a = pow( (sin(0.0174 * ($lat2 -  $lat1) / 2)) ,2) +
			cos(0.0174  * $lat1) *
			cos(0.0174  * $lat2) *
			pow(
				(sin(0.0174 * ($lon2 - $lon1)/2))
		 ,2);
	$c = 2 * atan2(sqrt($a), sqrt(1-($a)));
	switch($units)  {
		case 'MI': // STATUTE MILES
		$R = 3956.0;
		break;
	case  'KM': // KILOMETERS
		$R  = 6367.0;
		break;
	}
	return ($R * $c);
}
?>
