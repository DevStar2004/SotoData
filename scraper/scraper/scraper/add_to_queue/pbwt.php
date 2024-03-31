<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.pbwt.com';
$spider_name = 'pbwt';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {
	
	$data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/people?search[post_type]=person&search[letter]='.$char));
	$html = str_get_html($data);https://www.pbwt.com/people?search[post_type]=person&search[letter]=A

	foreach($html->find('.people-search-result') as $item)
	{
		if($item->find('a', 0))
		{

			$email = '';
			foreach($item->find('a') as $link)
			{
			    if(strpos($link->href, 'mailto:') !== false || strpos($link->href, '&#109;&#97;&#105;') !== false)
			    {
			        $email = str_replace('mailto:', '', html_entity_decode($link->href));
			    }
			}

			$phone = '';
			foreach($item->find('a') as $link)
			{
			    if(strpos($link->href, 'tel:') !== false)
			    {
			        $phone = str_replace('tel:', '', $link->href);
			    }
			}

			$row = array(
				'image' => $item->find('img', 0)->src,
				'url' => $base_url.$item->find('a', 0)->href,
				'name' => trim($item->find('.contact-details p', 0)->plaintext),
				'position' => @trim($item->find('.contact-title', 0)->plaintext),
				'phone' => @trim($phone),
				'email' => trim($email),
			);

			$values[] = $row;

			$q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
			$q->execute(array(
			    $spider_name,
			    $row['url'],
			    json_encode($row),
			    'pending',
			    time(),
			    NULL
			));

		}
	}

}

echo count($values);

?><br/>