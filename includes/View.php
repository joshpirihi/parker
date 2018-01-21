<?php

/**
 * Description of View
 *
 * @author joshpirihi
 * @sql CREATE TABLE views (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, meteogramID INTEGER);
 */
class View {
	
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
	 * @var ViewTopic[]
	 */
	public $viewTopics;
	
	/**
	 * @return View[]
	 */
	public static function all() {
		
		$rows = dbh_query('SELECT * FROM `views` ORDER BY `name` ASC;', []);
		
		$views = [];
		
		foreach ($rows as $row) {
			$instance = new self();
			$instance->loadFromDBRow($row);
			$instance->viewTopics = ViewTopic::allForView($instance->id);
			$views[] = $instance;
		}
		
		return $views;
	}
	
	function loadFromDBRow($row) {
		
		$this->id = $row['id'];
		$this->name = $row['name'];
	}
	
	/**
	 * Save the provided view (including topics).  If the id is not valid, a new view will be inserted.
	 * Regardless if the view is new or not, any existing viewtopics will be replaced with the given ones.
	 * 
	 * @param type $v
	 */
	public static function saveView($v) {
		
		//print_r($v);
		//exit();
		
		if (is_numeric($v['id']) && $v['id'] > 0) {
			
			//update
			dbh_query('UPDATE `views` SET `name` = ? WHERE `id` = ?;', [$v['name'], $v['id']]);
			
			//do the viewTopics
			foreach ($v['viewTopics'] as $vt) {
				
				ViewTopic::saveViewTopic($vt, $v['id']);
				
			}
			
		} else {
			
			//insert
			dbh_query('INSERT INTO `views` (`name`) VALUES (?) ;', [$v['name']]);
			
		}
		
		
		
	}
	
}
