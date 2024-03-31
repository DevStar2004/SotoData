<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.grsm.com';
$spider_name = 'grsm';
$firm_name = 'Gullett Sanford Robinson & Martin PLLC';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$data = file_get_contents('http://137.184.158.149:3000/?api=getClick&element=frm_submit&url=https://www.grsm.com/lawyers');
$html = str_get_html($data);

foreach($html->find('#tbl_attyResults tr') as $item)
{

    $row = array(

        'name' => trim(preg_replace('/[\t\n\r\s]+/', ' ', $item->find('.attyInfo a', 0)->plaintext)),
        'email' => get_string_between($item->innertext, 'href="javascript:mailerConfirm(\'mailto:', '\')'),
        'url' => $base_url.$item->find('.attyInfo a', 0)->href,
        'image' => @$base_url.$item->find('img', 0)->src,

    );

    $values[] = $row;

}

foreach($values as $row)
{
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>