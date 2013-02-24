<?php

class db{
	public $db;//redis instance
	public function __construct(){
		$this->db = new Redis;
		$this->db->connect("localhost", 6379);
	}
	public function getLevels(){
		if(defined('LEVELS')) return LEVELS;
		if($ret = $this->db->get('levels'))
			return $ret;
		else return 1;
	}
	public function minus($score){
		$pipe = $this->db->multi(Redis::PIPELINE);
		foreach($score as $s)
			$pipe->decr($s);
		$pipe->exec();
	}
	public function plus($score){
		$pipe = $this->db->multi(Redis::PIPELINE);
		foreach($score as $s)
			$pipe->incr($s);
		$pipe->exec();
	}
	public function minus_plus($minus,$plus){
		$pipe = $this->db->multi(Redis::PIPELINE);

		if(count($minus) > 0)
			foreach($minus as $s) $pipe->decr($s);
		if(count($plus) > 0)
			foreach($plus as $s) $pipe->incr($s);

		$pipe->exec();
	}
	public function getSum($elem){
		$total = $this->db->getMultiple($elem);
		if($total == false) return 0;
		return array_sum($total);
	}
	public function checkLevelsOverFlow($score){
		if(defined('LEVELS')) return ;
		$levels = $this->getLevels();
		$newLevels = ceil(log($score+2,2));
		if($levels < $newLevels)
			$this->db->set('levels',$newLevels);
	}

}