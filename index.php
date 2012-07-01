<?php

include_once('config.php');

$conn = mysql_pconnect(HOST, USER, PASS) or die("Can't Connect MySQL Server:".mysql_error());
mysql_select_db(DB_NAME);

if (isset($_POST['sf_search_submit'])) {
	$radius = $_POST["radius"];
	$records = $_POST["records"];
	$unit = $_POST["unit"];
	$addr = $_POST["address"];
	list($long, $lat) = get_longlat($addr);
}
else {
	$ip = $_SERVER['REMOTE_ADDR'];
	// $ip = $_SERVER['SERVER_ADDR'];
	// New Westminster,British Columbia, CA
	$addr = get_location_by_ip($ip);
	// $longitude, $latitude, $altitude, $addr
	list($long, $lat) = get_longlat($addr);
	// so till here, get 3 values: $long, $lat, $addr.
	$radius = RADIUS;
	$records = RECORDS;
	$unit = UNIT;
}

$myaddr = urldecode($addr).";";
if ($ip) {
  $myaddr .= "IP Address: " . $ip; // . " Longtitude: ".$long.", Latitude: ".$lat.", ";
}
$myaddr .= "Search within ".$radius.$unit.", Records: ".$records;

display_map($long, $lat, $addr, $radius, $records, $unit, $myaddr);

exit;

////////////////////////////////////////////
// style="width:200px;height:80px;z-index:10;font-family:Arial,sans-serif;font-size:9px; border:0px solid black;float:right;" 
function get_form()
{
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" id="search_form" method="post" name="search_form"  style="display:none">
  <fieldset>
  <legend class="formLegend" align="center">Get Locations:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <label>[<a href="javascript:void(0);" onclick="$('search_form').toggle();">-Hide</a>]</label>
  </legend>
  <ul>
    <li>
      <input type="text" name="address" id="address" value="Address or postal code" onfocus="if (/^Address/.test(this.value)) this.value=''" size="30" />
    </li>
    <li>
      <label><a href="javascript:void(0);" onclick="$('ul2').toggle();">Advanced Search &hellip;</a></label>
      <ul id="ul2" style="display:none">
        <li>
          <label>Search By:</label>
          &nbsp;
          <input type="radio" name="radius_record" id="r1" value="radius" checked="checked" onchange="$('rur1').show();$('rur2').hide();" />
          <label>Radius</label>
          &nbsp;
          <input type="radio" name="radius_record" id="r2" value="records" onchange="$('rur2').show();$('rur1').hide();" />
          <label>Records</label>
        </li>
        <ul id="rur1">
          <li>
            <label for="radius">Radius:</label>
            <input type="text" name="radius" id="radius" size="5" value="10" onfocus="this.select();" />
          </li>
          <li>
            <label>Unit:</label>
            <input type="radio" name="unit" id="u1" value="K" checked="checked" />
            <label>KMs</label>
            <input type="radio" name="unit" id="u2" value="M" />
            <label>Miles</label>
          </li>
        </ul>
        <ul id="rur2" style="display:none">
          <li>
            <label for="records">Records:</label>
            <input type="text" name="records" id="records" size="5" value="10" onfocus="this.select();" />
          </li>
        </ul>
      </ul>
    </li>
    <li>
      <div align="right">
        <input name="sf_search_submit" type="submit" value="Search"  />
        &nbsp; &nbsp;
        <input name="reset" type="reset" value="Reset" />
      </div>
    </li>
  </ul>
  </fieldset>
</form>
<?php
}

function display_map ($l1, $l2, $addr, $radius, $records, $unit, $myaddr)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Store Finder - Maps</title>
<!--link href="css/style.css" rel="stylesheet" type="text/css"-->
<link href="css/sf.css" rel="stylesheet" type="text/css">
<script src="js/prototype.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo KEY;?>" type="text/javascript"></script>
<script type="text/javascript">
    //<![CDATA[
    var map;
    var geocoder;
	var directionsPanel;
	var directions;

    /*
	var iconRed = new GIcon();
    iconRed.image = 'store_images/icon.png';
    // iconRed.shadow = 'store_images/icon.png';
    iconRed.iconSize = new GSize(6, 10);
    iconRed.shadowSize = new GSize(11, 10);
    iconRed.iconAnchor = new GPoint(2, 6);
    iconRed.infoWindowAnchor = new GPoint(5, 1);
    var customIcons = iconRed;
	*/
	
    function load(l1,l2) {
      if (GBrowserIsCompatible()) {
        geocoder = new GClientGeocoder();
        map = new GMap2(document.getElementById('map'));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        map.setCenter(new GLatLng(parseFloat(l2), parseFloat(l1)), 13);
      }
    }

	//$('span_location').innerHTML = '<label>'+decodeURIComponent(myaddr)+'</label>';
   function searchLocationsNear(l1,l2,addr,radius,records,unit,myaddr)
   {
     load(l1,l2);
	 searchNearBy(l1,l2,addr,radius,records,unit,myaddr);
   }
	
   function searchNearBy(l1,l2,addr,radius,records,unit,addr1) 
   {
	if ($('span_location')) {
		var myaddr = addr;
		var location = addr;
		if (addr1) {
			myaddr = addr1;
			location = addr1.substr(0, addr1.indexOf(';'));
		}
		// alert(myaddr+', ['+location+']');
		$('span_location').innerHTML = '<label><a href="javascript:void(0);" onclick="showAddress(\''+location+'\')">'+myaddr+'<a></label>';
	}
	// alert( l1+','+ l2+','+ addr+','+ radius+','+ records+','+ unit+','+ addr1);
     var searchUrl = 'markers.php?long='+l1+'&lat='+l2+'&radius='+radius+'&records='+records+'&unit='+unit+'&addr='+encodeURIComponent(addr);
     GDownloadUrl(searchUrl, function(data) {
       var xml = GXml.parse(data);
       var markers = xml.documentElement.getElementsByTagName('marker');
       map.clearOverlays();

       var sidebar = document.getElementById('sidebar');
       sidebar.innerHTML = '';
       if (markers.length == 0) {
         sidebar.innerHTML = 'No results found.';
         map.setCenter(new GLatLng(parseFloat(l2), parseFloat(l1)), 12);
         return;
       }
	   
       var bounds = new GLatLngBounds();
       for (var i = 0; i < markers.length; i++) {

         var name = markers[i].getAttribute('name');
         var street = markers[i].getAttribute('street');
         var city = markers[i].getAttribute('city');
         var prov = markers[i].getAttribute('prov');
         var country = markers[i].getAttribute('country');
         var zip = markers[i].getAttribute('zip');
         var lat = markers[i].getAttribute('lat');
         var lng = markers[i].getAttribute('lng');
		 var distance = markers[i].getAttribute('distance');
		 var sid = markers[i].getAttribute('sid');
		 var image = markers[i].getAttribute('image');
		 var phone = markers[i].getAttribute('phone');
		 var fax = markers[i].getAttribute('fax');
		 var web = markers[i].getAttribute('web');
		 var email = markers[i].getAttribute('email');
		 
         var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')),parseFloat(markers[i].getAttribute('lng')));
         
         var marker = createMarker(point, name, street, city, zip, distance);
         map.addOverlay(marker);
         // var sidebarEntry = createSidebarEntry(marker, name, street, city, prov, country, zip, lat, lng, distance);
         var sidebarEntry = createSidebarEntry(marker, name, street, city, prov, zip, distance,image, sid, addr, phone,fax,web,email);
         sidebar.appendChild(sidebarEntry);
         bounds.extend(point);
       }
       map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));
     });
   }

    function createMarker(point, name, street, city, zip, distance) {
      var marker = new GMarker(point);
      var html = '<b>'+name+'</b><br/>' + street + '<br/>'+ city+', '+zip+', '+distance;
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
      });
      return marker;
    }

	// marker, name, image
	function createSidebarEntry(marker, name, street, city, prov, zip, distance, image, sid, from_addr,phone,fax,web,email) {
		var tt = '';
		if (phone) {
			tt += 'Phone:' + phone + '; ';
		}
		if (fax) {
			tt += 'Fax:' + fax + '; ';
		}
		if (web) {
			tt += 'Web:' + web + '; ';
		}
		if (email) {
			tt += 'Email:' + email + '; ';
		}

		// var from1 = '7420 No 2 Road, Richmond, BC';
		var from1 = decodeURIComponent(from_addr);
		var to1 = street + ',' + city + ',' + prov;
		var d_sid = 'd_'+sid;
		// alert('from: '+from1+', to: ' + to1);
		var div = document.createElement('div');
		var html = '<table style="width:100%" border="0"><tr><td>' + "\n";
		html += '<img src="store_images/'+image+'" width="110" height="80" border="0" alt="'+image+'" title="'+image+'" />' + "\n";
		html += '</td><td><span class="a1">' + name + '</span><br>';
		html += street+', '+city+', '+prov+', '+zip +', '+distance +"; \n";
		if (tt) {
			html += tt;
		}
		html += '<br /><label align="right"><a href="javascript:void(0);" onclick="if ($(\''+d_sid+'\').visible()) {$(\''+d_sid+'\').hide();return false;} showDirection(\''+sid+'\',\''+from1+'\',\''+to1+'\')">get direction</a></label>';
		html += '</td></tr>' + "\n";
		html += '<tr><td colspan="2"><b>'+name+'</b>' + "\n";
		html += '</td></tr></table>';
		html += '<div id="'+d_sid+'" style="display:none;width:90%;margin-left:6px;" class="a2"></div>';
		div.innerHTML = html;
		div.style.cursor = 'pointer';
		div.style.marginBottom = '4px'; 
      GEvent.addDomListener(div, 'click', function() {
        GEvent.trigger(marker, 'click');
      });
      GEvent.addDomListener(div, 'mouseover', function() {
        div.style.backgroundColor = '#eee';
      });
      GEvent.addDomListener(div, 'mouseout', function() {
        div.style.backgroundColor = '#fff';
      });
		return div;
	}

	function showDirection(sid, from1, to1) {
		var d_sid = 'd_'+sid;
		if (!$(d_sid)) {
			alert('no div assigned'); return false;
		}
		directionsPanel = document.getElementById(d_sid);		
		directions = new GDirections(map, directionsPanel);
		directions.clear();
		directions.load("from: " + from1 + " to: " + to1);
		$(d_sid).show();
	}
	
	function showAddress(address) {
	  geocoder.getLatLng(
		address,
		function(point) {
		  if (!point) {
			alert(address + " not found");
		  } else {
			map.setCenter(point, 13);
			// var marker = new GMarker(point, customIcons);
			var marker = new GMarker(point);
			map.addOverlay(marker);
			var html = '<b>My Location:</b><br/>' + address;
			marker.openInfoWindowHtml(html);
		  }
		}
	  );
	}

	function getRadio(rname) {
		var t;
		for (i=0;i<rname.length;i++) {
			if (rname[i].checked) {
				t = rname[i].value;
				break;
			}
		}
		return t;
	}

	document.observe('dom:loaded', function() {
		$('search_form').observe('submit', function(event) {
			event.stop();
			var form = event.element();
			var addr = form.address.value;
			if (addr.match(/^\s+$/) || addr == "") {
				alert('Please fill up the address/zip field.');
				form.address.focus();
				return false;
			}
			var f2 = getRadio(form.radius_record); //f2: radius or records.
			var v1,v2;
			var v3 = getRadio(form.unit);			//f3: Km or miles
			if (f2=='radius'){
				v1 = form.radius.value;
				v2 = 0;
			}
			else if (f2=='records'){
				v1 = 0;
				v2 = form.records.value;
			}
			// alert(Object.inspect(Form.serialize(form))); 
			// showAddress(encodeURIComponent(form.address.value)); don't work.
			// showAddress(form.address.value);
			searchNearBy(0,0,addr,v1,v2,v3);
		});
	});
	
    //]]>
    </script>
</head>
<body onLoad="searchLocationsNear('<?php echo $l1;?>', '<?php echo $l2;?>', '<?php echo $addr;?>', '<?php echo $radius;?>', '<?php echo $records;?>','<?php echo $unit;?>', '<?php echo $myaddr;?>')" onUnload="GUnload()">
<div style="margin:6px">
  <label>Your location: </label>
  <span id="span_location" class="a1"></span>&nbsp;&nbsp;
  <label>Not your location? Click <a href="javascript:void(0);" onClick="$('search_form').toggle();if($('ul2').visible()){$('ul2').hide();}">here</a>.</label>
</div>
<table width="88%" cellpadding="4" border="0" cellspacing="4" id="content">
  <tbody>
    <tr>
      <td valign="top" rowspan="2"><div id="map" style="overflow:hidden; width:650px; height:600px"></div></td>
      <td><?php get_form(); ?></td>
    </tr>
    <tr>
      <td><div id="sidebar" style="overflow-y:scroll; height:600px; font-size: 11px; color: #000"></div></td>
    </tr>
  </tbody>
</table>
</div>
</body>
</html>
<?php
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
	
	// put the address of IP in the display box. echo  $addr . "<br/>\n";
	return  urlencode($addr);
}

function get_addr_by_name_zip ($name, $zip)
{
	$sql = "SELECT street, city, prov, country, zip, lat, lng FROM stores WHERE name = '". $name . "' and zip = '".$zip."'";

	$res = mysql_query($sql);
	
	$row = mysql_fetch_array($res);

	$addr = ucfirst(strtolower($row['address'])).','.ucfirst(strtolower($row['city'])).','.ucfirst(strtolower($row['region'])).','.ucfirst(strtolower($row['zip']));

	return urlencode($addr);
}



// ORDER BY distance ASC LIMIT 0,10
function get_distance()
{
	$sql = "SELECT SQRT( POW( 69.1 * (49.1589861 - 49.2800702) , 2 ) + POW( 69.1 * ( -122.9202065 - (-123.1588658)) * COS( 49.1589861 / 57.3 ) , 2 ) ) AS distance ";
	// $sql = "SELECT SQRT( POW( 69.1 * (coord_lat - 49.2800702) , 2 ) + POW( 69.1 * ( -122.9202065 - coord_long) * COS( coord_lat / 57.3 ) , 2 ) ) ";

	$res = mysql_query($sql);
	$row = mysql_fetch_array($res);
	$distance = $row['distance'];
	echo "$distance\n";

	return $distance;
}

?>
