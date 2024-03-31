<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$spider_name = 'hoganlovells';
$firm_name = 'Hogan Lovells US LLP';
$base_url = 'https://www.hoganlovells.com';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'linkedin') !== false)
        {
            $linkedIn = $link->href;
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));
    $values['email'] = $pData['email'];
    $values['phone_numbers'] = json_encode(array($pData['phone']));
    
    $education = array();
    if($html->find('.bio_credentials ul', 0))
    {
        $list = $html->find('.bio_credentials ul', 0);
        foreach($list->find('li') as $item)
        {
            $education[] = trim(strip_tags($item->plaintext));
        }
    }

    unset($education[0]);

    $bar_admissions = array();
    if($html->find('.bio_credentials ul', 1))
    {
        $list = $html->find('.bio_credentials ul', 1);
        foreach($list->find('li') as $item)
        {
            $bar_admissions[] = trim(strip_tags($item->plaintext));
        }
    }

    unset($bar_admissions[0]);

    $court_admissions = array();

    $memberships = array();

    $languages = array();
    $languages[] = 'N.A.';

    $practice_areas = array();

    if($html->find('.relatedCapabilities .tags', 0))
    {
        $list = $html->find('.relatedCapabilities .tags', 0);
        foreach($list->find('.tag') as $item)
        {
            $practice_areas[] = trim($item->plaintext);
        }
    }

    $positions = array();
    $positions[] = $pData['title'];

    if($html->find('div.intro', 0))
    {
        $values['description'] = trim($html->find('div.intro', 0)->plaintext);
    }
    else
    {
        $values['description'] = '';
    }

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', ' ', $value));
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

    if(empty($law_school))
    {
        $law_school = '';
    }

    if($html->find('.bio-panel img', 0))
    {
        $image = $html->find('.bio-panel img', 0)->src;
    }
    else
    {
        $image = '';
    }

    $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);

    if($html->find('.location_name a', 0))
    {
        $primaryAddress = $html->find('.location_name a', 0)->plaintext;
    }
    else
    {
        $primaryAddress = '';
    }

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));

    $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute(array(
        $values['names'],
        $values['email'],
        '',
        @$fullAddress,
        $primaryAddress,
        $linkedIn,
        $values['phone_numbers'],
        '',
        json_encode(array_values($education)),
        json_encode(array_values($bar_admissions)), //bar admissions
        json_encode(array_values($court_admissions)), //court admissions
        json_encode($practice_areas),
        '[]',
        json_encode($memberships),
        json_encode($positions),
        json_encode($languages),
        $row['url'],
        $values['description'],
        time(),
        $image,
        $image,
        $spider_name,
        $firm_name,
        $law_school,
        str_replace('-', '', $jd_year),
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