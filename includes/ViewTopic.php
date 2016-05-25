<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ViewTopic
 *
 * @author joshpirihi
 * @sql CREATE TABLE viewTopics (id INTEGER PRIMARY KEY AUTOINCREMENT, viewID INTEGER, topicID INTEGER, chart BOOLEAN);
 */
class ViewTopic {
	
	/**
	 *
	 * @var ing
	 */
	public $id;
	
	/**
	 *
	 * @var int
	 */
	public $viewID;
	
	/**
	 *
	 * @var int
	 */
	public $topicID;
	
	/**
	 *
	 * @var bool
	 */
	public $chart;
	
	/**
	 * 
	 * @param int $vID
	 * @return ViewTopic[]
	 */
	public static function allForView($vID) {
		
		$rows = dbh_query('SELECT * FROM `viewTopics` WHERE `viewID` = ? ORDER BY `topicID` ASC;', [$vID]);
		
		$vts = [];
		foreach ($rows as $row) {
			$instance = new self();
			$instance->loadFromDBRow($row);
			$vts[] = $instance;
		}
		
		return $vts;
	}
	
	public function loadFromDBRow($row) {
		
		$this->chart = $row['chart'];
		$this->id = $row['id'];
		$this->topicID = $row['topicID'];
		$this->viewID = $row['viewID'];
		
	}
}
