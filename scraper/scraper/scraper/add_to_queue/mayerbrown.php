<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mayerbrown.com';
$spider_name = 'mayerbrown';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

foreach (range('a', 'z') as $char) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&element=BoxCta_main__A0E6G&url='.urlencode('https://www.mayerbrown.com/en/people?sortCriteria=%40alphasort%20ascending&f-people-peoplelastnameletter='.$char));
    $html = str_get_html($data);

    foreach($html->find('.PeopleResults_results-list__CpLRg li') as $item)
    {
        if($item->find('.visually-hidden', 0))
        {
            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $item->find('.visually-hidden', 0)->plaintext,
            );

            $values[] = $row;

            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));

        }
    }

}

echo count($values);

?><br/>