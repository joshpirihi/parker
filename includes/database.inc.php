<?php
//global $dbrw;

require_once 'config.inc.php';

$dbrw = true;

if ($dbrw) {
	$db = new SQLite3(DATABASEPATH, SQLITE3_OPEN_READWRITE);
} else {
	//$db = new SQLite3(DATABASEPATH, SQLITE3_OPEN_READONLY);
}
$db->busyTimeout(1000);

register_shutdown_function(function () {
	global $db;
	$db->close();
});

function dbh_query($query, $params) {
	
	global $db;
	
	$stmt = $db->prepare($query);
	
	$i = 1;
	if (is_array($params)) foreach ($params as $p) {
		
		if (is_string($p)) {
			$stmt->bindValue($i, $p, SQLITE3_TEXT);
		} else if (is_float($p)) {
			$stmt->bindValue($i, $p, SQLITE3_FLOAT);
		} else {
			$stmt->bindValue($i, $p, SQLITE3_INTEGER);
		}
		$i++;
	}
	
	$results = $stmt->execute();
	
	$rows = [];
	
	while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
		$rows[] = $row;
	}
	
	$stmt->close();
	
	return $rows;
}