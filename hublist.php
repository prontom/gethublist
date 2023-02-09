<?php
/*
hublist_xml array ( 'Address' => '1-new.no-ip.org', 'Country' => 'Russian Federation', 'Description' => '<Enter hub description here>', 'Maxhubs' => '0', 'Maxusers' => '1000', 'Minshare' => '0', 'Minslots' => '0', 'Name' => '...:::: новый ::::...', 'Shared' => '64871186038784', 'Users' => '189', )array ( 'Address' => '109.174.22.4', 'Country' => 'Russian Federation', 'Description' => '<Enter hub description here>', 'Maxhubs' => '0', 'Maxusers' => '8000', 'Minshare' => '0', 'Minslots' => '0', 'Name' => 'Vipernsk Hub', 'Shared' => '78065325572096', 'Users' => '218', favorites array ( 'Name' => 'Pafas� Hub (pafas.ru)', 'Connect' => '1', 'Description' => 'www.pafas.ru', 'Nick' => '', 'Password' => '', 'Server' => 'pafas.ru', 'UserDescription' => '', 'AwayMsg' => '', 'Email' => '', 'WindowPosX' => '0', 'WindowPosY' => '0', 'WindowSizeX' => '0', 'WindowSizeY' => '0', 'WindowType' => '3', 'ChatUserSplit' => '7500', 'StealthMode' => '0', 'HideShare' => '0', 'ShowJoins' => '0', 'ExclChecks' => '0',
'ExclusiveHub' => '0', 'UserListState' => '1', 'HeaderOrder' => '0,1,2,3,4,5,6,7,8,9,10,11,12', 'HeaderWidths' => '100,75,75,75,100,50,40,40,100,30,30,50,80', 'HeaderVisible' => '1,1,1,1,1,1,1,1,1,1,1,1,1', 'RawOne' => '', 'RawTwo' => '', 'RawThree' => '', 'RawFour' => '', 'RawFive' => '', 'Mode' => '0', 'IP' => '', 'OpChat' => '', 'CliendId' => 'FakeDC V:1.0', 'OverrideId' => '0', )
*/
$Gb = pow(1024, 3); $Tb = $Gb * 1024;

$limit = 10;
$min_share = 1*$Tb;

$file = file_get_contents("http://dchublist.ru/hublist.xml.bz2");

$fh = fopen("Favorites.xml",'w');
fwrite($fh, $file);
fclose($fh);
$bz = bzopen("Favorites.xml", "r") or die("Couldn't open $file");

$decompressed_file = '';
while (!feof($bz)) 
  $decompressed_file .= bzread($bz, 4096);
bzclose($bz);

$fh = fopen("Favorites.xml","w");
fwrite($fh, $decompressed_file);
fclose($fh);

$arr = get_hublist_xml("Favorites.xml");
$xml = array();
for ($i=0; $i <= count($arr['Server']); $i++){

	if ($arr['Server'][$i]=='' || $arr['Shared'][$i] < $min_share)  continue;
	$shared = $arr['Shared'][$i];
	$name  = str_replace('"', "'", $arr['Name'][$i]);
	$descr = str_replace('"', "'", $arr['Description'][$i]);
	$xml[$shared][$i] = "		<Hub Name=\"\" Connect=\"1\" Description=\"\" Nick=\"\" Password=\"\" Server=\"{$arr['Server'][$i]}\" UserDescription=\"\" AwayMsg=\"\" Email=\"\" WindowPosX=\"0\" WindowPosY=\"0\" WindowSizeX=\"0\" WindowSizeY=\"0\" WindowType=\"0\" ChatUserSplit=\"0\" StealthMode=\"0\" HideShare=\"0\" ShowJoins=\"0\" ExclChecks=\"0\" ExclusiveHub=\"0\" UserListState=\"1\" HeaderOrder=\"\" HeaderWidths=\"\" HeaderVisible=\"\" RawOne=\"\" RawTwo=\"\" RawThree=\"\" RawFour=\"\" RawFive=\"\" Mode=\"0\" IP=\"\" OpChat=\"\" CliendId=\"FakeDC V:1.0\" OverrideId=\"0\"/>\r\n";	

}
krsort($xml); //up to down

//var_export($xml);
$i = 0;
foreach($xml as $share=>$arr)
	foreach($arr as $no=>$text){
		$i++;
		if ($i>$limit) break;
		$xml_out .= $text;
	}

$xml_out =
"<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>
<Favorites>
	<Hubs>
$xml_out
	</Hubs>
	<Users/>
	<UserCommands/>
	<FavoriteDirs/>
</Favorites>";


$fh = fopen("Favorites.xml","w");
fwrite($fh, $xml_out);	
	
print "<a href='Favorites.xml'>Favorites.xml</a>";

function get_hublist_xml($file){
	$fav_arr = array();
	$arr = xml_to_array($file);
	error_reporting(0);
	foreach($arr['children'] as $data['children'])
		foreach($data['children'] as $data2)
			foreach($data2 as $data3){
				if ($data3['attributes']['Shared'] == 0) continue;
				$fav_arr['Name'][] = $data3['attributes']['Name'];
				$fav_arr['Description'][] = $data3['attributes']['Description'];
				$fav_arr['Server'][] = $data3['attributes']['Address'];
				$fav_arr['Shared'][] = $data3['attributes']['Shared'];
				$fav_arr['Users'][] = $data3['attributes']['Users'];
			}
	error_reporting(1);

//var_export($fav_arr);
	return $fav_arr;
}


function xml_to_array( $file ){
	$parser = xml_parser_create();
	xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct( $parser, file_get_contents($file), $tags );
	xml_parser_free( $parser );
 
	$elements = array();
	$stack = array();
	foreach ( $tags as $tag )
	{
			$index = count( $elements );
			if ( $tag['type'] == "complete" || $tag['type'] == "open" )
			{
					$elements[$index] = array();
					$elements[$index]['name'] = $tag['tag'];
					$elements[$index]['attributes'] = $tag['attributes'];
					$elements[$index]['content'] = $tag['value'];
				 
					if ( $tag['type'] == "open" )
					{    # push
							$elements[$index]['children'] = array();
							$stack[count($stack)] = &$elements;
							$elements = &$elements[$index]['children'];
					}
			}
		 
			if ( $tag['type'] == "close" )
			{    # pop
					$elements = &$stack[count($stack) - 1];
					unset($stack[count($stack) - 1]);
			}
	}
	return $elements[0];
}
?>
