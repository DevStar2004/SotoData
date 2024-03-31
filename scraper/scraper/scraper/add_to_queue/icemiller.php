<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.icemiller.com';
$spider_name = 'icemiller';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 420;
while ($i > 0) {

	$data = fetch('https://www.icemiller.com/people?search%5Bpost_type%5D=person&from='.$i);
	$html = @str_get_html($data);

	if(empty($html))
	{
		continue;
	}

	foreach($html->find('.results-container li') as $item)
	{

		if($item->find('a', 0))
		{

			foreach($item->find('a') as $link)
			{
				if(strpos($link->href, 'mailto:') !== false)
				{
					$email = $link->href;
				}
			}

			if(empty($email))
			{
				$email = '';
			}

			$values[] = array(
				'name' => $item->find('div p', 0)->plaintext,
				'url' => $base_url.$item->find('a', 0)->href,
				'position' => trim($item->find('div p', 1)->plaintext),
				'phone' => trim($item->find('div a', 1)->plaintext),
				'email' => $email,
			);

		}

	}

	$i = $i-30;

}

foreach ($values as $row) {

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

echo count($values);

?><br/>