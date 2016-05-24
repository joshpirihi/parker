<?php

/**
 * Description of View
 *
 * @author joshpirihi
 * @sql CREATE TABLE [view] (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, meteogramID INTEGER);
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
	 * @var int|null
	 */
	public $meteogramID;
	
	/**
	 *
	 * @var ViewTopic[]
	 */
	public $viewTopics;
	
	
	
}
