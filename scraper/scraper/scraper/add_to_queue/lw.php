<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.lw.com';
$spider_name = 'lw';
$firm_name = 'Latham & Watkins';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$i = 0;
while ($i < 3640) {
    
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.lw.com/en/people#first='.$i.'&sort=%40peoplerankbytitle%20ascending%3B%40peoplelastname%20ascending'));
    $html = str_get_html($data);

    foreach($html->find('.coveo-list-layout.CoveoResult') as $item)
    {

        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $item->find('[data-field="@peopleformattedname"] span', 0)->plaintext,
            'title' => $item->find('[data-field="@peopleemployeetitle"] span', 0)->plaintext,
            'location' => @$item->find('[data-field="@peopleofficesstring"] span', 0)->plaintext,
            'image' => $base_url.@$item->find('img', 0)->src,
            'phone' => @$item->find('[data-field="@peopledirectdialnumber"] span', 0)->plaintext,
            'email' => @$item->find('[data-field="@peopleemailaddress"] span', 0)->plaintext,
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

    $i = $i+20;

}

echo count($values);

?><br/>