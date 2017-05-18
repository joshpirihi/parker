<?php

/**
 * Description of Summary
 *
 * @author joshpirihi
 */
class Summary {
	
	/**
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 *
	 * @var int
	 */
	public $fromTopic;
	
	/**
	 *
	 * @var int 
	 */
	public $toTopic;
	
	/**
	 *
	 * @var double
	 */
	public $multiplier;
	
	/**
	 * 
	 * @return [Summary]
	 */
	public static function allByID() {
		
		$rows = dbh_query('SELECT * FROM `summaries`;', []);
		
		$ss = [];
		
		foreach ($rows as $row) {
			
			$instance = new self();
			$instance->loadFromDBRow($row);
			$ss[$instance->id] = $instance;
		}
		
		return $ss;
	}
	
	/**
	 * 
	 * @param array $row
	 */
	private function loadFromDBRow($row) {
		
		$this->id = $row['id'];
		$this->fromTopic = $row['fromTopic'];
		$this->toTopic = $row['toTopic'];
		$this->multiplier = $row['muliplier'];
		
	}
	
}
