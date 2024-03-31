<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

$_GET['url'] = htmlspecialchars_decode(urldecode($_GET['url']));

if(@strlen(parse_url($_GET['url'])['path'])<5)
{
	header('Location: https://sotodata.io/img/nophoto.png');
	exit();
}

if(strpos($_GET['url'], 'dentons.com') !== false || strpos($_GET['url'], 'bakerbotts.com') !== false)
{
	header('Location: '.$_GET['url']);
	exit;
}

if(strpos($_GET['url'], '.jpg') === false && strpos($_GET['url'], '.png') === false && strpos($_GET['url'], '.jpeg') === false && strpos($_GET['url'], '.avif') === false && strpos($_GET['url'], '.gif') === false && strpos($_GET['url'], '.webp') === false)
{
	header('Location: https://sotodata.io/img/nophoto.png');
	exit();
}

if(strpos($_GET['url'], 'mofo.com') !== false)
{
	$_GET['url'] = str_replace('https://www.mofo.com/_next/image?url=', '', $_GET['url']);
	header('Location: '.htmlspecialchars_decode($_GET['url']));
	exit();
}

$url = $_GET['url'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
$res = curl_exec($ch);
$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
curl_close($ch);
//echo $res;

$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if(!$res)
{
	header('Location: '.$_GET['url']);
	exit();
}

header("Content-Type: ".$contentType);

echo $res;

?>