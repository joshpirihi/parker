<?php


/**
 * Description of Meteogram
 *
 * @author joshpirihi
 * @sql CREATE TABLE meteograms (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT);
 */
class Meteogram {
	
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
	 * @var MeteogramTopic[]
	 */
	public $series;
	
}
