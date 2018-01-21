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
	 * @var bool
	 */
	public $gauge;
	
	/**
	 *
	 * @var bool
	 */
	public $big;
	
	/**
	 *
	 * @var int
	 */
	public $order;
	
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
		$this->gauge = $row['gauge'];
		$this->id = $row['id'];
		$this->topicID = $row['topicID'];
		$this->viewID = $row['viewID'];
		$this->big = $row['big'];
		$this->order = $row['order'];
		
	}
	
	/**
	 * Inserts a new ViewTopic
	 * 
	 * @param array $vt
	 * @param int $viewID
	 */
	public static function saveViewTopic($vt, $viewID) {
		
		
		if (is_numeric($vt['id']) && $vt['id'] > 0) {
			
			if ($vt['delete'] == 'true') {
				
				//echo 'Deleting '.$vt['id'].PHP_EOL;
				
				dbh_query('DELETE FROM `viewTopics` WHERE `id` = ?;', [$vt['id']]);
				
			} else {
				//update
				dbh_query('UPDATE `viewTopics` SET `viewID` = ?, `topicID` = ?, `chart` = ?, `big` = ?, `gauge` = ?, `order` = ? WHERE `id` = ?;', [$viewID, $vt['topicID'], $vt['chart']=='true', $vt['big']=='true', $vt['gauge']=='true', $vt['order'], $vt['id']]);
			}
		} else {
			
			if ($vt['delete'] != 'true') {
				//insert
				dbh_query('INSERT INTO `viewTopics` (`viewID`, `topicID`, `chart`, `big`, `gauge`, `order`) VALUES (?, ?, ?, ?, ?, ?);', [$viewID, $vt['topicID'], $vt['chart']=='true', $vt['big']=='true', $vt['gauge']=='true', $vt['order']]);
			}
		}
		
		
		
	}
	
}
