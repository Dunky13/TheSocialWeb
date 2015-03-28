<?php

switch($_SERVER['REQUEST_METHOD'])
{
case 'GET': $the_request = &$_GET; break;
case 'POST': $the_request = &$_POST; break;
default: 
}

$search = $the_request["search"];

/*
#!/bin/bash
grep -hiEr "[[:digit:]]{9}.*"$1 /ddsData/www/plein/* | cut -c 1-50
*/
$output = shell_exec("sudo -u root /root/searchPlein.sh $search"); //due to an NDA I cannot grant you access to the DDS data, you'll have to trust me that it accesses the proper data here.

$dates = [];

$output = explode(PHP_EOL, $output);

foreach($output as $out){
	preg_match("/([0-9]{9})/", $out, $date);
	$dt = strtotime(date("Y-m-d", $date[1]));
	if(array_key_exists($dt, $dates))
		$dates[$dt] = $dates[$dt]+1;
	else
		$dates[$dt] = 0;
}
ksort($dates);
$no_dates = count($dates);
$average = array_sum($dates)/$no_dates*1.0;
$biggest_start = PHP_INT_MAX;
$biggest_end = PHP_INT_MAX;

$start = PHP_INT_MAX;
$end = PHP_INT_MIN;
$count_fails = 0;

$response = [];

foreach($dates as $date => $count){
	if($count > $average){
		$count_fails = 0;
		if($start == PHP_INT_MAX){
			$start = $date;
		}
		else if($date > $start){
			$end = $date;
		}
		else{
			$response['error'] = "WTF, dates should be sorted :/";
			break;
		}
	}
	else{
		if($count_fails == floor($no_dates*0.1)-1){
			if($end-$start > $biggest_end-$biggest_start){
				$biggest_start = $start;
				$biggest_end = $end;
			}
			$start = PHP_INT_MAX;
			$end = PHP_INT_MIN;
			$count_fails = 0;
		}
		else{
			$count_fails++;
		}
	}
}
if($biggest_start == PHP_INT_MAX || $biggest_end == PHP_INT_MAX){
	$response['error'] = "No dates found";
}
else{
	$response['start'] = $biggest_start;
	$response['end'] = $biggest_end;
	foreach($dates as $date => $count){
		if($date >= $biggest_start && $date <= $biggest_end)
			$response['dates'][$date] = $count;
	}
}
echo json_encode($response);
?>
