<?php

class db{
	public $db;//memcached instance
	private $levels;

	public function __construct(){
		$this->db = new Memcached;
		$this->db->addServer("localhost", 11211);
	}
	public function getLevels(){
		if(defined('LEVELS')) return LEVELS;
		if($ret = $this->db->get('levels'))
			return $ret;
		else return 1;
	}
	public function minus($score){
		foreach($score as $s)
			if($ret = $this->db->get($s))
				$this->db->set($s,$ret-1);
	}
	public function plus($score){
		foreach($score as $s)
			if($ret = $this->db->get($s))
				$this->db->set($s,++$ret);
			else $this->db->set($s,1);
	}
	public function minus_plus($minus,$plus){
		$this->minus($minus);
		$this->plus($plus);
	}
	public function getSum($elem){
		$total = 0;
		if(count($elem) > 0 )
			foreach ($elem as $key)
				if($ret = $this->db->get($key)) $total += $ret;
		return $total;
	}
	public function checkLevelsOverFlow($score){
		if(defined('LEVELS')) return ;
		$levels = $this->getLevels();
		$newLevels = ceil(log($score+2,2));
		if($levels < $newLevels)
			$this->db->set('levels',$newLevels);
	}


}