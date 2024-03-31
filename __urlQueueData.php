<?php
include 'config.php';
include 'simple_html_dom.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

$html = str_get_html($_POST['content']);

foreach($html->find('a') as $item)
{
	if(strpos($item->href, 'miniProfile') !== false)
	{
		$linkedIn = explode('?miniProfileUrn', $item->href)[0];
		break;
	}
}

if(isset($linkedIn) && strpos($linkedIn, 'company') === false)
{

	$q = $pdo->prepare('INSERT INTO `linkedIn` VALUES (?,?,?,?,?)');
	$q->execute(array($_POST['names'], $_POST['firmName'], $linkedIn, '', NULL));

	/*
	$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `names`=? AND `firmName`=?');
	$q->execute(array($linkedIn, $_POST['names'], $_POST['firmName']));
	*/	

}
else
{
	$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `names`=? AND `firmName`=?');
	$q->execute(array('not_found', $_POST['names'], $_POST['firmName']));	
}

?>