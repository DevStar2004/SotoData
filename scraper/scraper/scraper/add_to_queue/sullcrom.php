<?php
include '../config.php';

$base_url = 'https://www.sullcrom.com';
$spider_name = 'sullcrom';
$firm_name = 'Sullivan & Cromwell LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$data = json_decode(file_get_contents('https://searchg2.crownpeak.net/sullivancromwell-sullcrom-live/select/?q=*%3A*&wt=json&fl=*%2Cscore&defType=edismax&bf=field%28custom_i_customsort%29%5E100&bf=linear%28recip%28rord%28custom_dt_postdate%29%2C1%2C1000%2C1000%29%2C11%2C0%29%5E250&bq=custom_s_type%3ALawyer%5E3000&bq=custom_s_type%3APractice%5E1000&bq=custom_s_type%3AOffices%5E750&bq=custom_s_type%3ANews%5E250&bq=custom_s_type%3AEvents%5E250&bq=custom_s_type%3AAwards%5E250&bq=custom_s_type%3APublications%5E150&fq=custom_s_type%3A%28Lawyer+Awards+Events+News+Practice+Publications+AboutUs+Offices%29&facet=true&json.facet=%7B%22custom_i_type%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_i_type%22%2C%22limit%22%3A1000%7D%2C%22custom_is_office%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_office%22%2C%22limit%22%3A1000%7D%2C%22custom_is_admission%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_admission%22%2C%22limit%22%3A1000%7D%2C%22custom_is_school%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_school%22%2C%22limit%22%3A1000%7D%2C%22custom_is_clerkships%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_clerkships%22%2C%22limit%22%3A1000%7D%2C%22custom_is_lang%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_lang%22%2C%22limit%22%3A1000%7D%2C%22custom_is_practices%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_practices%22%2C%22limit%22%3A1000%7D%2C%22custom_is_industry%22%3A%7B%22type%22%3A%22terms%22%2C%22field%22%3A%22custom_is_industry%22%2C%22limit%22%3A1000%7D%7D&start=10&rows=840&sort=score%20desc&sort=custom_i_customsort%20asc&sort=title%20asc&fq=custom_s_type%3A%22Lawyer%22'), 1);

foreach($data['response']['docs'] as $row)
{
    $values[] = $row;
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['custom_s_url'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>