<?php

$dbrw = true;

date_default_timezone_set('Pacific/Auckland');

require_once('includes/config.inc.php');
require_once('includes/database.inc.php');
require_once('includes/Topic.php');
require_once 'includes/DataPoint.php';
require_once('includes/User.php');
require_once('includes/Summary.php');

$summaries = Summary::allByID();

foreach ($summaries as $s) {
	
	echo 'Working on summary '.$s->id.'. From '.$s->fromTopic.' to '.$s->toTopic.' with factor '.$s->multiplier.'.'.PHP_EOL;
	
	//get the last time we made an entry for the toTopic, then get all the
	//fromTopic's datapoints since then
	
	//find the time at the bottom of the hour of that first point,
	//then go through and sum the points in each hour,
	//inserting each hourly sum into as a datapoint of toTopic
	
	$fromTopic = Topic::withID($s->fromTopic);
	$toTopic = Topic::withID($s->toTopic);
	
	$l = dbh_query('SELECT MAX(`time`) AS `time` FROM `datapoints` WHERE `topic_id` = ?;', [$toTopic->id]);
	$lastToTopicDataPointTime = max($l[0]['time'], time()-86400);
	
	echo 'Retrieving datapoints since '.strftime('%c', $lastToTopicDataPointTime).PHP_EOL;
	
	$newDataPoints = dbh_query('SELECT * FROM `datapoints` WHERE `topic_id` = ? AND `time` > ? ORDER BY `time` ASC;', [$fromTopic->id, $lastToTopicDataPointTime]);
	
	echo 'Got '.count($newDataPoints).' of them.'.PHP_EOL;
	
	//echo $lastToTopicDataPointTime;
	//print_r($l);
	//print_r($s);
	//print_r($newDataPoints);
	
	if (count($newDataPoints) == 0) continue;
	
	$startTime = $newDataPoints[0]['time'] - $newDataPoints[0]['time']%3600;
	$sumToTime = $startTime + 3600;
	
	echo 'Starting summarizer.  Initial period is '.strftime('%c', $startTime).' to '.strftime('%c', $sumToTime).'.'.PHP_EOL;
	
	$sum = 0;
	
	foreach ($newDataPoints as $dp) {
		
		if ($dp['time'] > $sumToTime) {
			
			echo 'Insert '.$sum.'. Next end time is '.strftime('%c', $sumToTime).'.'.PHP_EOL;
			
			//then insert
			dbh_query('INSERT INTO `datapoints` (`topic_id`, `time`, `value`) VALUES (?, ?, ?);', [$s->toTopic, $sumToTime, $sum]);
			
			$sumToTime += 3600;
			$sum = 0;
			
			
		} else {
			
			echo $dp['value'].PHP_EOL;
			
			$sum += $dp['value'] * $s->multiplier;
			
		}
		
	}
	
}