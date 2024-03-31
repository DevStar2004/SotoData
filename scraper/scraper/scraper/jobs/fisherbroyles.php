<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.fisherbroyles.com';
$spider_name = 'fisherbroyles';
$firm_name = 'Fisher Broyles';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    

    $pData = json_decode($row['data'], 1);

    $values = array();

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'linkedin') !== false)
        {
            $linkedIn = $link->href;
            break;
        }
    }

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'vcard') !== false)
        {
            $values['vCard'] = $link->href;
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $html->find('.people-single__name', 0)->plaintext));

    $values['email'] = cfDecodeEmail(str_replace('/cdn-cgi/l/email-protection#', '', $html->find('.people-single__mail a', 0)->href));

    $values['phone_numbers'] = json_encode(array($html->find('.people-single__phone', 0)->plaintext));

    $fullAddress = '';

    $education = array();
    foreach($html->find('.people-single__content-list') as $item)
    {
        if(strpos($item->innertext, 'Education') !== false)
        {
            foreach($item->find('.people-single__content-list__list p') as $item)
            {
                $education = explode('<br />', $item->innertext);
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('.people-single__content-list') as $item)
    {
        if(strpos($item, 'Admissions') !== false)
        {
            foreach($item->find('.people-single__content-list__list p') as $item)
            {
                $bar_admissions = explode('<br />', $item->innertext);
            }
        }
    }

    $practice_areas = array();
    foreach($html->find('.colored-list__categories li a') as $item)
    {
        $practice_areas[] = $item->plaintext;
    }

    $positions = json_encode(array(trim($html->find('h3.people-single__subtitle', 0)->plaintext)));

    $values['description'] = $html->find('.base-content', 0)->plaintext;

    $photo = $pData['acf']['image']['url'];
    $thumb = $pData['acf']['image']['url'];

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

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link), 'vcard') !== false)
        {
            $values['vCard'] = $link->href;
        }
    }

    $primaryAddress = $html->find('.people-single__firm a', 0)->plaintext;

    $values['vCard'] = '';

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));
    
    $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute(array(
        $values['names'],
        $values['email'],
        @$values['vCard'],
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
        json_encode(array('N.A.')),
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