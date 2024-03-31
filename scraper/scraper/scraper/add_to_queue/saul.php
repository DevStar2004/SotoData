<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.saul.com';
$spider_name = 'saul';
$firm_name = 'Saul Ewing LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


//characters A-Z
foreach (range('A', 'Z') as $char) {

    $data = fetch_quick($base_url.'/professionals?f[0]=last_name:'.$char.'&page=0');
    $html = str_get_html($data);

    if(!$html->find('a[title="Go to last page"]', 0))
    {
        $page = 0;
    }
    else
    {
        $link = $html->find('a[title="Go to last page"]', 0)->href;
        $page = explode('page=', $link)[1];
    }

    while ($page >= 0) {
        
        $data = @fetch_quick($base_url.'/professionals?f[0]=last_name:'.$char.'&page='.$page);

        if(empty($data)) { continue; }

        $html = str_get_html($data);

        foreach($html->find('.views-row') as $item)
        {

            $row = array(
                'name' => $item->find('profile-teaser', 0)->{'full-name'},
                'image' => $base_url.$item->find('img', 0)->src,
                'position' => $item->find('profile-teaser', 0)->{'main-title'},
                'email' => $item->find('profile-teaser', 0)->email,
                'phone' => $item->find('profile-teaser', 0)->phone,
                'url' => $base_url.$item->find('profile-teaser', 0)->{'profile-url'},
                'location' => $item->find('profile-teaser', 0)->{'primary-office-location'},
            );

            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

            $values[] = $row;

        }

        if($page == 0)
        {
            break;
        }
        else
        {
            $page--;
        }

    }

}

echo count($values);

?><br/>