<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.weil.com';
$spider_name = 'weil';
$firm_name = 'Weil, Gotshal & Manges LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


for ($i=1; $i <= 25; $i++) {
    $data = json_decode(fetch_quick($base_url.'/WeilPeopleListing/Execute?pagenum='.$i.'&pagesize=50'), 1);
    foreach($data['SearchGroups'] as $item)
    {
        foreach($item['SearchResultItemsAsHtml'] as $value)
        {
            $html = str_get_html($value);
            
            if(!$html->find('img', 0))
            {
                $image = '';
            }
            else
            {
                $image = $html->find('img', 0)->src;
            }

            $row = array(
                'name' => trim($html->find('.h3.h-alt a', 0)->plaintext),
                'url' => trim($base_url.$html->find('.h3.h-alt a', 0)->href),
                'image' => $base_url.$image,
                'title' => $html->find('.ppl-item-status b', 0)->plaintext,
                'location' => $html->find('.ppl-item-status a', 0)->plaintext,
            );
            $values[] = $row;

            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

        }
    }

}

echo count($values);

?><br/>