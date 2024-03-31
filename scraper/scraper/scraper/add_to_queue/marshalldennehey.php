<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://marshalldennehey.com';
$spider_name = 'marshalldennehey';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 47;
while ($i > 0) {

    $payload = 'view_name=attorneys_filterable_listing&view_display_id=embed_1&view_args=&view_path=%2Fnode%2F18&view_base_path=&view_dom_id=15d8b19081fffb6fe40636e865df8e4682f89675ce67ffa86130db8ce9ddd3eb&pager_element=0&viewsreference%5Bdata%5D%5Btitle%5D=0&viewsreference%5Bdata%5D%5Bargument%5D=&viewsreference%5Benabled_settings%5D%5Bargument%5D=argument&viewsreference%5Benabled_settings%5D%5Btitle%5D=title&state=All&viewsreference%5Bdata%5D%5Btitle%5D=0&viewsreference%5Benabled_settings%5D%5Bargument%5D=argument&viewsreference%5Benabled_settings%5D%5Btitle%5D=title&page='.$i.'&_drupal_ajax=1&ajax_page_state%5Btheme%5D=mdy&ajax_page_state%5Btheme_token%5D=&ajax_page_state%5Blibraries%5D=classy%2Fbase%2Cclassy%2Fmessages%2Cclassy%2Fnode%2Ccore%2Fnormalize%2Cgoogle_analytics%2Fgoogle_analytics%2Cmd_system%2Fmailto-dialog%2Cmdy%2Fglobal-scripts%2Cmdy%2Fglobal-styles%2Cparagraphs%2Fdrupal.paragraphs.unpublished%2Csocial_media_links%2Fsocial_media_links.theme%2Csuperfish%2Fsuperfish%2Csuperfish%2Fsuperfish_hoverintent%2Csuperfish%2Fsuperfish_smallscreen%2Csuperfish%2Fsuperfish_supersubs%2Csuperfish%2Fsuperfish_supposition%2Csystem%2Fbase%2Cviews%2Fviews.module%2Cviews_infinite_scroll%2Fviews-infinite-scroll';

    $f = file_get_contents('http://137.184.158.149:3000/?api=post&postData='.base64_encode($payload).'&url=https://marshalldennehey.com/views/ajax?_wrapper_format=drupal_ajax');
    $f = str_replace(array('<textarea>', '</textarea>'), '', $f);

    $data = json_decode($f, 1)[1]['data'];

    $html = str_get_html($data);

    foreach($html->find('.views-row') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

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

        if($item->find('h3 a', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('h3 a', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => @trim($item->find('.field--name-field-phone-number', 0)->plaintext),
                'email' => @trim($email),
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

    $i--;
}

echo count($values);

?><br/>