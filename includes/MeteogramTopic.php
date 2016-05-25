<?php

/**
 * Description of MeteogramTopic
 *
 * @author joshpirihi
 * @sql CREATE TABLE meteogramTopics (id INTEGER PRIMARY KEY AUTOINCREMENT, meteogramID INTEGER, seriesName TEXT, topicID INTEGER);
 */
class MeteogramTopic {
	
	/**
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 *
	 * @var int
	 */
	public $meteogramID;
	
	/**
	 *
	 * @var string
	 */
	public $seriesName;
	
	/**
	 *
	 * @var int
	 */
	public $topicID;
	
	/**
	 * 
	 * @param int $mID
	 * @return MeteogramTopic[]
	 */
	public static function allForMeteogram($mID) {
		
		$rows = dbh_query('SELECT * FROM `meteogramTopics` WHERE `meteogramID` = ?;', [$mID]);
		
		$mts = [];
		
		foreach ($rows as $row) {
			$instance = new self();
			$instance->loadFromDBRow($row);
			$mts[] = $instance;
		}
		
		return $mts;
	}
	
	public function loadFromDBRow($row) {
		
		$this->id = $row['id'];
		$this->meteogramID = $row['meteogramID'];
		$this->seriesName = $row['seriesName'];
		$this->topicID = $row['topicID'];
		
	}
	
}
