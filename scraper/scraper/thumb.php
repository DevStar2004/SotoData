<?php
header("Content-type: image/png");
if(empty($_GET['url']))
{
	header('Location: https://sotodata.com/img/nophoto.png');
}
else
{
	header('Location: '.$_GET['url']);
}
echo $f;
?>