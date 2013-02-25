<?php

$db = new Redis;	
$db->connect('localhost',6379);
$db->flushDB();// delete all data from the db
unset($db);
require_once('PHPUnit/Autoload.php');
require_once('../db.redis.php');
require_once('../TRank.php');

class TRankTest extends PHPUnit_Framework_TestCase{
	public function testTRank(){
		$tr = new TRank(new db());
		$testCase = 1000;
		$start_score = 500000;
		$testCase += $start_score;

		for($i=$start_score;$i<$testCase;$i++)//add
			$tr->add($i);
		for($i=$start_score;$i<$testCase;$i++)
			$this->assertEquals($testCase-$i,$tr->getRank($i));

		$more = 5;
		for($i=$start_score;$i<$testCase;$i++)//update
			$tr->update($i,$i+$more);
		for($i=$start_score;$i<$testCase;$i++)
			$this->assertEquals($testCase-$i,$tr->getRank($i+$more));

		for($i=$start_score;$i<$testCase;$i++)//delete
			$tr->delete($i+$more);
		for($i=$start_score;$i<$testCase;$i++)
			$this->assertEquals(1,$tr->getRank($i));
	}
}
