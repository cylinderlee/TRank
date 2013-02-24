<?php
define('LEVELS',20);
class TRank{
	public $error = null;
	private $db;// data processing

	public function __construct($db){
		if(empty($db)) die('error: invalid $db');
		$this->db = $db;
	}
	public function add($score){
		$this->db->checkLevelsOverFlow($score);
		$levels = $this->getLevels($score);
		$elem = $this->getRelevantElem($score,$levels,'lte');
		$this->db->plus($elem);
	}
	public function delete($score){		
		$levels = $this->getLevels($score);
		$elem = $this->getRelevantElem($score,$levels,'lte');
		$this->db->minus($elem);
	}
	public function update($score,$newScore){
		if($score == $newScore) return ;
		$this->db->checkLevelsOverFlow($newScore);

		$levels = $this->getLevels(max($score,$newScore));
		$top = pow(2,  $levels -1) -1;

		while(($top > $newScore && $top > $score) || ($top < $newScore && $top < $score)){
			if($top > $newScore && $top > $score)
				$top = $this->getLeftChild($top,$levels);
			else $top = $this->getRightChild($top,$levels);
			$levels --;
		}

		$minus = $this->getRelevantElem($score,$levels,'lt',$top);
		$plus = $this->getRelevantElem($newScore,$levels,'lt',$top);

		if(isset($plus[0])  && $score == $plus[0]) unset($plus[0]);
		else $minus[] = $score;
		if(isset($minus[0]) && $newScore == $minus[0]) unset($minus[0]);
		else $plus[] = $newScore;
		
		$this->db->minus_plus($minus,$plus);
	}

	public function getRank($score){
		$levels = $this->db->getLevels();//get the real levels
		//relevent element to calculate the rank
		$elem = $this->getRelevantElem($score + 1,$levels,'gte');
		$rank = $this->db->getSum($elem);
		return $rank + 1;
	}

	private function getRelevantElem($score,$levels,$type = 'lte',$top = null){
		$elem = array();
		if($top === null) $top = pow(2,  $levels -1) -1;

		while($score != $top){
			if($score < $top){
				if($type == 'gte' || $type == 'gt') $elem[] = $top; 
				$top = $this->getLeftChild($top,$levels--);					
			}
			else{
				if($type == 'lte' || $type == 'lt') $elem[] = $top;
				$top = $this->getRightChild($top,$levels--);
			}
			if($top < 0) die('error occur'); //levels is incorrect
		}
		if($type == 'gte' || $type == 'lte') $elem[] = $score;
		return $elem;
	}
	private function getLevels($score = -1){
		if($score != -1) return ceil(log($score+2,2));
		else{
			if(defined('LEVELS')) return LEVELS;//the whole tree's levels
			return $this->db->getLevels();//the real levels
		}
	}
	public function getLeftChild($num,$levels){
		if($levels < 2) return -1;
		return $num - pow(2,$levels - 2);
	}
	public function getRightChild($num,$levels){
		if($levels < 2) return -1;
		return $num + pow(2,$levels - 2);
	}

	// test for memcached
	public function testMemcached($maxValue = 10){
		$levels = $this->getLevels();
		echo "---- levels: $levels ----\n";
		echo "  score  |  gte  |  rank\n";
		for($i=0;$i<=$maxValue;$i++)
			if(($ret = $this->db->db->get($i)) !== false && $ret > 0){
				$rank = $this->getRank($i);
				printf("% 5d    :% 5d     %5d\n",$i,$ret,$rank);
			}
		echo "----------------------\n";
	}
	// test for redis
	public function testRedis(){
		$levels = $this->getLevels();
		echo "---- levels: $levels ----\n";
		echo "  score  |  gte  |  rank\n";
		$keys = $this->db->db->keys("*");
		sort($keys);
		if($keys !== false)
		foreach($keys as $k){
			$v = $this->db->db->get($k);
			if($v !== false && $v > 0){
				$rank = $this->getRank($k);
				printf("% 5d    :% 5d     %5d\n",$k,$v,$rank);
			}
		}
		echo "----------------------\n";
	}
}
