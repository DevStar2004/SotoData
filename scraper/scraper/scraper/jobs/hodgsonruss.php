<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.hodgsonruss.com';
$spider_name = 'hodgsonruss';
$firm_name = 'Hodgson Russ LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    if(strpos($html, 'The link you followed or URL you entered does not correspond to a valid address on this web site.') !== false)
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }

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

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    $values['phone_numbers'] = json_encode(array($pData['phone']));

    $values['email'] = $pData['email'];

    $fullAddress = '';

    $education = array();
    foreach($html->find('#bio_education') as $item)
    {
        foreach($item->find('p') as $item)
        {
            $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('#bio_admissions') as $item)
    {
        foreach($item->find('li') as $item)
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

    $practice_areas = array();
    foreach($html->find('#bio_areas') as $item)
    {
        foreach($item->find('a') as $item)
        {
            $practice_areas[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
        }
    }

    $languages = array();
    foreach($html->find('.category-list__category') as $item)
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

    $positions = json_encode(array($pData['position']));

    if($html->find('#bio_content', 0))
    {
        $values['description'] = $html->find('#bio_content', 0)->plaintext;
    }
    else
    {
        $values['description'] = '';
    }

    $photo = $base_url.'/'.$html->find('#bioPhoto', 0)->src;
    $thumb = $base_url.'/'.$html->find('#bioPhoto', 0)->src;

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

    $primaryAddress = $html->find('#bioOfficesWrapper a', 0)->plaintext;

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