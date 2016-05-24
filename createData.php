<?php


date_default_timezone_set('Pacific/Auckland');

require_once('includes/database.inc.php');
require_once('includes/Topic.php');
require_once 'includes/DataPoint.php';

dbh_query('DELETE FROM `datapoints`;', []);

foreach (Topic::all() as $t) {
	
	
	for ($i=time()-7*86400; $i<time(); $i+=600) {
		dbh_query('INSERT INTO `datapoints` (`topic_id`, `time`, `value`) VALUES (?, ?, ?);', [$t->id, $i, rand(1, 30)]);
	}
	
}