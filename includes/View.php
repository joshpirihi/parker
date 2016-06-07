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
	
}
