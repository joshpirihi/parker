<?php

/**
 *
 * @author joshpirihi
 */
class Topic {
	
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
	 * @var string
	 */
	public $description;
	
	/**
	 *
	 * @var string
	 */
	public $units;
	
	/**
	 *
	 * @var string
	 */
	public $chartColour;
	
	/**
	 *
	 * @var int
	 */
	public $defaultPeriod;
	
	
	
	/**
	 *
	 * @var DataPoint[]
	 */
	public $points;
	
	/**
	 * @return Topic[]
	 */
	public static function all() {
		
		$rows = dbh_query('SELECT * FROM `topics`;', []);
		
		if (count($rows) == 0) {
			return [];
		}
		
		$topics = [];
		
		foreach ($rows as $row) {
			$instance = new self();
			$instance->loadFromDBRow($row);
			$topics[$instance->id] = $instance;
		}
		
		return $topics;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Topic
	 */
	public static function withName($name) {
		
		$rows = dbh_query('SELECT * FROM `topics` WHERE `name` = ?;', [$name]);
		
		if (count($rows) == 0) {
			return null;
		}
		
		$instance = new self();
		$instance->loadFromDBRow($rows[0]);
		return $instance;
	}
	
	/**
	 * 
	 * @param double $value
	 */
	public function addValue($value) {
		
		dbh_query('INSERT INTO `datapoints` (`topic_id`, `time`, `value`) VALUES (?, ?, ?);', [$this->id, time(), $value]);
		
	}
	
	private function loadFromDBRow($row) {
		
		$this->description = $row['description'];
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->points = [];
		$this->units = $row['units'];
		$this->chartColour = $row['chartColour'];
		$this->defaultPeriod = $row['defaultPeriod'];
		
		
	}
}
