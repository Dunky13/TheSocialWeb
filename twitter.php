<?php

$last_item = "";
function array_unique_diff($array1, $array2)
{
	if(!empty($array1) && empty($array2))
		return $array1;
	else if(empty($array1) && !empty($array2))
		return $array2;
	else
		return array_replace(array_diff_key($array1, $array2), array_diff_key($array2, $array1));
}
function array_merge_intersect($array1, $array2){
	$a1a2 = array_intersect_key($array1, $array2);
	$a2a1 = array_intersect_key($array2, $array1);
	$sum = [];
	foreach($a1a2 as $key => $val){
		$sum[$key] = $val + $a2a1[$key];
	}
	return $sum;
}

function getDatesFromHTML($html){
	preg_match_all('/data-time="([0-9]+)"/i', $html, $matches);
	$dates = $matches[1];
	$days = [];
	foreach($dates as $key => $val){
		$day = floor(intval($val)/86400)*86400;
		if(isset($days[$day]) || array_key_exists($day, $days)){
			$days[$day] = $days[$day] + 1;
		}
		else{
			$days[$day] = 1;
		}
	}
	return $days;
}

function println($data, $title = ""){
	if(!empty($title))
		echo $title.": ";
	print_r($data);
	echo "<br>\n";
}

function getResults($refr_c, $count){
	$q			= rawurlencode('"sinterklaas" lang:nl since:2014-11-30 until:2014-12-16');
	$src		= "typd";
	$comp_c		= 0;
	$inc_a_f	= 1;
	$inc_ent	= 1;
	$inc_n_i_b	= "true";
	$last_n_t	= 775;
	$latent_c 	= 0;

	$url = "https://twitter.com/i/search/timeline?q=$q&src=$src&include_available_features=$inc_a_f&include_entities=$inc_ent&latent_count=$latent_c&scroll_cursor=$refr_c";
	$fgc = file_get_contents($url);
	$result = json_decode($fgc, true);
	$html = $result['items_html'];
	if(microtime(true)-$GLOBALS['time_pre'] >= 15 || $html === "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n"){
		$GLOBALS['last_item'] = $result['scroll_cursor'];
		return [];
	}
	$dates = getDatesFromHTML($html);
	$resDates = getResults($result['scroll_cursor'], $count+1);

	$diff = array_unique_diff($dates, $resDates);
	$intersect = array_merge_intersect($dates, $resDates);
	/*println($dates, "dates");
	println($resDates, "Recurse Dates");
	println($diff, "Diff");
	println($intersect, "Intersect");
	println($dates + $resDates, "Union1");
	println($resDates + $dates, "Union2");
	println(array_replace($dates, $resDates), "Union3");
	println("---------------------------");*/
	$dates = array_replace($diff, $intersect);
	//$dates = array_merge($dates, getResults($results['scroll_cursor'], $count+1));
	//return $all_dates;
	return $dates;
}
$refr_c 	= "TWEET-544075432275234816-544608310276542464-BD1UO2FFu9QAAAAAAAAETAAAAAcAAAASAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";

$time_pre = microtime(true);
$dates = getResults($refr_c, 0);
/*println($dates, "Result");
$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
echo "took $exec_time s to execute";
var_dump($result);*/
echo json_encode(['dates' => $dates, 'last_item' => $last_item]);
?>
