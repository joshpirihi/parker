<?php


/**
 * Description of Meteogram
 *
 * @author joshpirihi
 * @sql CREATE TABLE meteograms (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT);
 */
class Meteogram {
	
	/**
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 *
	 * @var MeteogramTopic[]
	 */
	public $series;
	
	/**
	 * 
	 * @param int $mID
	 * @return Meteogram|null
	 */
	public static function withID($mID) {
		
		$rows = dbh_query('SELECT * FROM `meteograms` WHERE `id` = ?;', [$mID]);
		
		if (count($rows) == 0) {
			return null;
		}
		
		$instance = new self();
		$instance->loadFromDBRow($rows[0]);
		$instance->series = MeteogramTopic::allForMeteogram($instance->id);
		
		return $instance;
	}
	
	public function loadFromDBRow($row) {
		
		$this->id = $row['id'];
		$this->name = $row['name'];
		
	}
	
}
