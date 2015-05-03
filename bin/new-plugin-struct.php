<?php

if(empty($argv[1])){
	echo "Please give the name of the plugin";
	exit;
}

$dir= __DIR__ . '/../plugins/' . $argv[1];

`mkdir $dir`;
`mkdir $dir/controllers`;
`mkdir $dir/models`;
`mkdir $dir/views`;
`mkdir $dir/static`;
`mkdir $dir/widgets`;
`touch $dir/start.php`;