<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bakerlaw.com';
$spider_name = 'bakerlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {

    $payload = '{"requests":[{"indexName":"admin_bakerlaw_production_searchable_posts_last_name_asc","params":"facetFilters=%5B%5B%22taxonomies.poa_alpha_taxonomy%3A'.$char.'%22%5D%5D&facets=%5B%22relationships.poa_office.title%22%2C%22relationships.poa_person.last_name%22%2C%22relationships.poa_practice%22%2C%22taxonomies.poa_admission_state_taxonomy%22%2C%22taxonomies.poa_alpha_taxonomy%22%2C%22taxonomies.poa_district_taxonomy%22%2C%22taxonomies.poa_position_taxonomy%22%5D&filters=post_type%3Apoa_person&highlightPostTag=__%2Fais-highlight__&highlightPreTag=__ais-highlight__&hitsPerPage=100&maxValuesPerFacet=1000&page=0&query=&tagFilters="},{"indexName":"admin_bakerlaw_production_searchable_posts_last_name_asc","params":"analytics=false&clickAnalytics=false&facets=%5B%22taxonomies.poa_alpha_taxonomy%22%5D&filters=post_type%3Apoa_person&highlightPostTag=__%2Fais-highlight__&highlightPreTag=__ais-highlight__&hitsPerPage=0&maxValuesPerFacet=1000&page=0&query="}]}';

    $data = file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode($payload).'&url='.urlencode('https://ir09eo50x5-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=Algolia%20for%20JavaScript%20(4.19.1)%3B%20Browser%20(lite)%3B%20instantsearch.js%20(4.56.8)%3B%20react%20(18.2.0)%3B%20react-instantsearch%20(6.47.3)%3B%20react-instantsearch-hooks%20(6.47.3)%3B%20next.js%20(13.4.12)%3B%20JS%20Helper%20(3.14.0)&x-algolia-api-key=05ca00afa1ed402cd7cdaadf1128b493&x-algolia-application-id=IR09EO50X5'));

    $rows = json_decode($data, 1)['results'][0]['hits'];

    foreach($rows as $row)
    {

        $values[] = $row;

        if(strpos($row['permalink'], 'http') === false)
        {
            $row['permalink'] = $base_url.$row['permalink'];
        }

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