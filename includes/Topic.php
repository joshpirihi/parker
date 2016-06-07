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
	 * MQTT Topic name
	 * eg /topic/name
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * Human readable title
	 * eg Outside Temperature
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
	 * used directly as CSS value
	 * eg #ff5500 or rgba(255, 100, 0, 1)
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
	 * @var double
	 */
	public $chartMin;
	
	/**
	 *
	 * @var double
	 */
	public $chartMax;
	
	/**
	 *
	 * @var int
	 */
	public $decimalPoints;
	
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
		$this->chartMin = $row['chartMin'];
		$this->chartMax = $row['chartMax'];
		$this->decimalPoints = $row['decimalPlaces'];
		
	}
}
