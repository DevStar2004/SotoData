<?php
include '../config.php';

$base_url = 'https://www.butlersnow.com';
$spider_name = 'butlersnow';
$firm_name = 'Butler Snow LLP';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData=ewogICAgInF1ZXJ5IjogIlxuICAgIHF1ZXJ5IFNlYXJjaFByb2Zlc3Npb25hbHMoXG4gICAgICAkY2l0eTogU3RyaW5nXG4gICAgICAkbGV0dGVyOiBTdHJpbmdcbiAgICAgICRpbmR1c3RyeUlkOiBTdHJpbmdcbiAgICAgICRuYW1lOiBTdHJpbmdcbiAgICAgICRjb2xsZWdlSWQ6IFN0cmluZ1xuICAgICAgJGxhd1NjaG9vbElkOiBTdHJpbmdcbiAgICAgICRzdGF0ZU9mQmFyQWRtaXNzaW9uOiBTdHJpbmdcbiAgICAgICRwcmFjdGljZUFyZWFJZDogU3RyaW5nXG4gICAgKSB7XG4gICAgICBzZWFyY2hQcm9mZXNzaW9uYWxzKFxuICAgICAgICBuYW1lOiAkbmFtZVxuICAgICAgICBsZXR0ZXI6ICRsZXR0ZXJcbiAgICAgICAgY29sbGVnZUlkOiAkY29sbGVnZUlkXG4gICAgICAgIGxhd1NjaG9vbElkOiAkbGF3U2Nob29sSWRcbiAgICAgICAgY2l0eTogJGNpdHlcbiAgICAgICAgcHJhY3RpY2VBcmVhSWQ6ICRwcmFjdGljZUFyZWFJZFxuICAgICAgICBpbmR1c3RyeUlkOiAkaW5kdXN0cnlJZFxuICAgICAgICBzdGF0ZU9mQmFyQWRtaXNzaW9uOiAkc3RhdGVPZkJhckFkbWlzc2lvblxuICAgICAgKSB7XG4gICAgICAgIGNpdHlcbiAgICAgICAgZW1haWxcbiAgICAgICAgZnVsbE5hbWVcbiAgICAgICAgaWRcbiAgICAgICAgaW1hZ2VcbiAgICAgICAgbGFzdFxuICAgICAgICBzbHVnXG4gICAgICAgIHZjYXJkVXJsXG4gICAgICAgIHdvcmtQaG9uZVxuICAgICAgfVxuICAgIH1cbiAgIiwKICAgICJ2YXJpYWJsZXMiOiB7CiAgICAgICAgImNpdHkiOiAiIiwKICAgICAgICAiY29sbGVnZUlkIjogIiIsCiAgICAgICAgImluZHVzdHJ5SWQiOiAiIiwKICAgICAgICAibGF3U2Nob29sSWQiOiAiIiwKICAgICAgICAibGV0dGVyIjogIiIsCiAgICAgICAgIm5hbWUiOiAiIiwKICAgICAgICAicHJhY3RpY2VBcmVhSWQiOiAiIiwKICAgICAgICAic3RhdGVPZkJhckFkbWlzc2lvbiI6ICIiCiAgICB9Cn0=&url=https://butlersnow-api.gsandfdev.com/graphql'), 1);

$values = array();

foreach($data['data']['searchProfessionals'] as $row)
{
    $values[] = $row;
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.'/professionals/'.$row['slug'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>