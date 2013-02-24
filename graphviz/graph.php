<?php

$n = 4;
$last = pow(2,$n) - 2;
$root = pow(2,$n-1) - 1;
echo "graph grp{\n";
traverse($root,$n);
function traverse($root,$n){
	if($n > 1){
		$left = $root - pow(2,$n-2);
		$right = $root + pow(2,$n-2);

		echo "\t".$root."--".$left;
		echo ";\n";

		echo "\t".$root."--".$right;
		echo ";\n";

		traverse($left,$n-1);
		traverse($right,$n-1);
	}
}
echo "}\n";