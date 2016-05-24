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
	
}
