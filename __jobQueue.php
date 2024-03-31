<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
$data = file_get_contents('__jobTemp.txt');
if(strlen($data)>0)
{
	echo $data;
	file_put_contents('__jobTemp.txt', '');
}
?>