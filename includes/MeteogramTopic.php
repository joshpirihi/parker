<?php

/**
 * Description of MeteogramTopic
 *
 * @author joshpirihi
 * @sql CREATE TABLE meteogramTopics (id INTEGER PRIMARY KEY AUTOINCREMENT, meteogramID INTEGER, seriesName TEXT, topicID INTEGER);
 */
class MeteogramTopic {
	
	public $id;
	
	public $meteogramID;
	
	public $seriesName;
	
	public $topicID;
	
}
