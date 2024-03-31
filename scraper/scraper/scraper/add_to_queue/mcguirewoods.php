<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mcguirewoods.com';
$spider_name = 'mcguirewoods';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$values = array();

$i = 12;
while ($i > 0) {

    $json = '{"requests":[{"indexName":"wp_posts_people","params":"facetingAfterDistinct=true&facets=%5B%22taxonomies.role%22%2C%22linked_services%22%2C%22taxonomies.location_tax%22%2C%22taxonomies.jurisdiction%22%2C%22taxonomies.school%22%2C%22taxonomies.admission%22%2C%22people_last_name_letter%22%5D&highlightPostTag=__%2Fais-highlight__&highlightPreTag=__ais-highlight__&hitsPerPage=96&maxValuesPerFacet=1000&page='.$i.'&query=&tagFilters="}]}';

    $data = file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode($json).'&url='.urlencode('https://30gjxcd6ge-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=Algolia for vanilla JavaScript 3.24.9;instantsearch.js (4.7.1);JS Helper (3.2.2)&x-algolia-application-id=30GJXCD6GE&x-algolia-api-key=561b44c6d69b0c2b7020fcd5ec64e945'));

    $people = json_decode($data, 1);

    foreach ($people['results'][0]['hits'] as $item) {

        $row = array(
            'url' => $item['permalink'],
            'name' => $item['post_title'],
            'image' => $item['profile_image_url'],
            'title' => $item['people_job_title'],
            'email' => $item['email_address'],
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

    $i--;

}

echo count($values);

?><br/>