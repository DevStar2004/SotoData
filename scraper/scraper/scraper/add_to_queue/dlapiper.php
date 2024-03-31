<?php

echo '1';
exit();

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.dlapiper.com';
$spider_name = 'dlapiper';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 4800;

while ($i > 0) {
    
    $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.dlapiper.com/en-gb/coveosearchpages/people-index-hosted-search-page#first='.$i.'&t=All&sort=relevancy&numberOfResults=25'));
    $html = @str_get_html($data);

    if(empty($data))
    {
        $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.dlapiper.com/en-gb/coveosearchpages/people-index-hosted-search-page#first='.$i.'&t=All&sort=relevancy&numberOfResults=25'));
        $html = @str_get_html($data);

        if(empty($data))
        {
            $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.dlapiper.com/en-gb/coveosearchpages/people-index-hosted-search-page#first='.$i.'&t=All&sort=relevancy&numberOfResults=25'));
            $html = @str_get_html($data);

            if(empty($data))
            {
                continue;
            }

        }
    }

    foreach($html->find('.coveo-result-frame') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = $base_url.@$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('[data-field="@profilename"] span', 0))
        {
            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $item->find('[data-field="@profilename"] span', 0)->plaintext,
                'image' => $image,
                'title' => @$item->find('.CoveoFieldValue.professional-level span', 0)->plaintext,
                'email' => @$item->find('.CoveoFieldValue.email a', 0)->plaintext,
                'phone' => @$item->find('.CoveoFieldValue.professional-level.phone a', 0)->plaintext
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

    $i = $i-25;
}

echo count($values);

?><br/>