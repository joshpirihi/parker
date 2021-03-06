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
	 * @var string 
	 */
	public $chartType;
	
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
	 *
	 * @var bool
	 */
	public $accumulative;
	
	/**
	 *
	 * @var int
	 */
	public $order;
	
	/**
	 * @return Topic[]
	 */
	public static function all() {
		
		$rows = dbh_query('SELECT * FROM `topics` ORDER BY `order` ASC;', []);
		
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
	 * @param int $id
	 * @return Topic
	 */
	public static function withID($id) {
		
		$rows = dbh_query('SELECT * FROM `topics` WHERE `id` = ?;', [$id]);
		
		if (count($rows) == 0) {
			return null;
		}
		
		$instance = new self();
		$instance->loadFromDBRow($rows[0]);
		return $instance;
	}
	
	
	
	/**
	 * Add a datapoint value for this topic into the datapoints table
	 * 
	 * @param double $value
	 */
	public function addValue($value) {
		
		dbh_query('INSERT INTO `datapoints` (`topic_id`, `time`, `value`) VALUES (?, ?, ?);', [$this->id, time(), $value]);
		
	}
	
	/**
	 * Save the provided topic.
	 * If id is non numeric or zero, a new topic will be inserted, otherwise the topic with the supplied ID will be updated
	 * 
	 * @param array $t
	 */
	public static function saveTopic($t) {
		
		if (!is_numeric($t['id']) || $t['id'] == 0) {
			//insert
			
			$result = dbh_query('INSERT INTO `topics` (`name`, `description`, `units`, `chartColour`, `chartMin`, `chartMax`, `decimalPoints`, `order`, `accumulative`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);', 
					[
						$t['name'], 
						$t['description'],
						$t['units'],
						$t['colour'],
						$t['chartMin'],
						$t['chartMax'],
						$t['decimalPoints'],
						$t['order'],
						$t['accumulative']
					]
				);
			
			return [
				'action' => 'insert',
				'result' => $result
				];
			
		} else {
			//update
			
			$result = dbh_query('UPDATE `topics` SET `name` = ?, `description` = ?, `units` = ?, `chartColour` = ?, `chartMin` = ?, `chartMax` = ?, `decimalPoints` = ?, `order` = ?, `accumulative` = ? WHERE `id` = ?;', [
				$t['name'], 
				$t['description'],
				$t['units'],
				$t['colour'],
				$t['chartMin'],
				$t['chartMax'],
				$t['decimalPoints'],
				$t['order'],
				$t['accumulative'],
				$t['id']
			]);
			
			return [
				'action' => 'update',
				'result' => $result
				];
			
		}
		
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
		$this->chartType = $row['chartType'];
		$this->decimalPoints = $row['decimalPoints'];
		$this->accumulative = $row['accumulative'];
		$this->order = $row['order'];
		
	}
}
