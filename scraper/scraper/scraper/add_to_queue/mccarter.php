<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mccarter.com';
$spider_name = 'mccarter';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {

    $payload = '{"requests":[{"indexName":"mccarter_production_searchable_posts_post_order_asc","params":"filters=post_type%3Apoa_person&hitsPerPage=1000&distinct=true&facetingAfterDistinct=true&highlightPreTag=__ais-highlight__&highlightPostTag=__%2Fais-highlight__&query=&page=0&maxValuesPerFacet=1000&facets=%5B%22taxonomies.poa_alpha_taxonomy%22%2C%22virtual%22%2C%22relationships.poa_practice%22%2C%22relationships.poa_office%22%2C%22taxonomies.me_specialty_taxonomy%22%2C%22taxonomies.me_search_position_taxonomy%22%2C%22taxonomies.poa_schools_taxonomy%22%2C%22taxonomies.poa_admission_state_taxonomy%22%5D&tagFilters=&facetFilters=%5B%5B%22taxonomies.poa_alpha_taxonomy%3A'.$char.'%22%5D%5D"},{"indexName":"mccarter_production_searchable_posts_post_order_asc","params":"filters=post_type%3Apoa_person&hitsPerPage=1&distinct=true&facetingAfterDistinct=true&highlightPreTag=__ais-highlight__&highlightPostTag=__%2Fais-highlight__&query=&page=0&maxValuesPerFacet=10000&attributesToRetrieve=%5B%5D&attributesToHighlight=%5B%5D&attributesToSnippet=%5B%5D&tagFilters=&analytics=false&clickAnalytics=false&facets=%5B%22taxonomies.poa_alpha_taxonomy%22%5D"}]}';

    $data = file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode($payload).'&url='.urlencode('https://f991lmql7a-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=Algolia for vanilla JavaScript 3.24.9;instantsearch.js (4.7.1);JS Helper (3.2.2)&x-algolia-application-id=F991LMQL7A&x-algolia-api-key=04f4e7d0707c65acf03c407b43d02b99'));

    $rows = json_decode($data, 1)['results'][0]['hits'];

    foreach($rows as $row)
    {

        $values[] = $row;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $row['permalink'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));
        
    }

}

echo count($values);

?><br/>