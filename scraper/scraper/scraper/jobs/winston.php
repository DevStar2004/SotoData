<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.winston.com';
$spider_name = 'winston';
$firm_name = 'Winston & Strawn LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);

    //var_dump($pData);
    //var_dump($row);

    $values = array();

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'linkedin') !== false)
        {
            $linkedIn = $link->href;
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', str_replace(', ', ' ', $pData['name'])));

    $values['phone_numbers'] = json_encode(array($pData['offices_info'][0]['phone']));

    $values['email'] = $pData['email'];

    $fullAddress = '';

    $education = array();
    foreach($html->find('.bio-content-row__content') as $item)
    {
        if(strpos(strtolower($item->innertext), 'education') !== false)
        {
            foreach($item->find('.bio-badges__school-info') as $item)
            {
                $education[] = str_replace('-', '&ndash;', trim(preg_replace('/\s+/', ' ', $item->plaintext)));
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('.bio-content-row__content') as $item)
    {
        if(strpos(strtolower($item->innertext), 'admissions') !== false)
        {
            foreach($item->find('.bio-badges__grid-item .bio-badges__vertical-center-cell') as $item)
            {
                if(strpos(strtolower($item->plaintext), 'court'))
                {
                    $court_admissions[] = trim($item->plaintext);
                }
                else
                {
                    $bar_admissions[] = trim($item->plaintext);
                }
            }
        }
    }

    $practice_areas = array();
    foreach($html->find('.anchored-row__content') as $item)
    {
        if(strpos(strtolower($item->innertext), 'services') !== false)
        {
            foreach($item->find('li a') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
            }
        }
    }

    $languages = array();
    foreach($html->find('#bio_languages') as $item)
    {
        if(strpos($item->innertext, 'Languages') !== false)
        {
            foreach($item->find('li') as $item)
            {
                $languages[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }
    }

    if(count($languages)<1)
    {
        $languages[] = 'N.A.';
    }

    $positions = json_encode(array(str_replace(',  ', '', $pData['content_data']['position_fk']['name'])));

    $values['description'] = $html->find('.bio-rte__wrapper .rte', 0)->plaintext;

    if($html->find('.bio-hero__photo-container img', 0))
    {
        $pData['image'] = $html->find('.bio-hero__photo-container img', 0)->src;
    }
    else
    {
        $pData['image'] = 'https://sotodata.com/img/nophoto.png';
    }

    $photo = $pData['image'];
    $thumb = $pData['image'];

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
        if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false)
        {
            $law_school = $value;
            break;
        }
    }

    if(empty($law_school))
    {
        $law_school = '';
    }

    $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);

    foreach($html->find('.bio-top-banner__title-area a') as $item)
    {
        if(strpos($item->href, 'where-we-are'))
        {
            $primaryAddress = $item->plaintext;
            break;
        }
    }

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));

    $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute(array(
        $values['names'],
        $values['email'],
        '',
        @sEncode($fullAddress),
        @sEncode($primaryAddress),
        $linkedIn,
        $values['phone_numbers'],
        '',
        json_encode($education),
        json_encode($bar_admissions), //bar admissions
        json_encode($court_admissions), //court admissions
        json_encode($practice_areas),
        '[]',
        '[]',
        $positions,
        json_encode($languages),
        $row['url'],
        sEncode(trim(strip_tags($values['description']))),
        time(),
        $thumb,
        $photo,
        $spider_name,
        $firm_name,
        $law_school,
        $jd_year,
        0,
        NULL
    ));

    $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
    $q->execute(array($row['id']));

    unset($values);
    unset($law_school);
    unset($jd_year);
    unset($fullAddress);
    unset($primaryAddress);
    unset($linkedIn);

}

@unlink($spider_name.'_temp.vcf');

?>