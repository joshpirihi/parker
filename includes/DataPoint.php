<?php
/**
 * Description of DataPoint
 *
 * @author joshpirihi
 */
class DataPoint implements JsonSerializable {
	
	/**
	 *
	 * @var int
	 */
	public $id;

	/**
	 *
	 * @var int
	 */
	public $topicID;
	
	/**
	 *
	 * @var DateTime
	 */
	public $time;
	
	/**
	 *
	 * @var double
	 */
	public $value;
	
	/**
	 * 
	 * @param int $tID
	 * @param int $since
	 * @return DataPoint[]
	 */
	public static function allForTopicSince($tID, $since) {
		
		$rows = dbh_query('SELECT * FROM `datapoints` WHERE `topic_id` = ? AND `time` > ? ORDER BY `time` ASC;', [$tID, $since]);
		
		if (count($rows) == 0) {
			return 0;
		}
		
		$dps = [];
		
		foreach ($rows as $row) {
			$instance = new self();
			$instance->loadFromDBRow($row);
			$dps[] = $instance;
		}
		
		return $dps;
	}
	
	private function loadFromDBRow($row) {
		
		$this->id = $row['id'];
		$this->time = new DateTime();
		$this->time->setTimestamp($row['time']);
		$this->topicID = $row['topic_id'];
		$this->value = $row['value'];
	}
	
	public function jsonSerialize() {
		$return = array();
		foreach ($this as $key => $value) {
			if ($value instanceof DateTime) {
				$return[$key] = $value->format('c');
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}
	
}
