<?php
//定时15分钟执行一次
date_default_timezone_set("PRC");
$myfile = fopen("log.txt", "w");
$txt = date('Y-m-d H:i:s',time())."\n";
echo file_put_contents("log.txt", $txt, FILE_APPEND | LOCK_EX);

$log=file_get_contents("http://wchuanghua.ecloudm.com/api/into/index");

