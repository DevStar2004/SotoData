<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.faegredrinker.com';
$spider_name = 'faegredrinker';
$firm_name = 'Faegre Drinker Biddle &amp; Reath LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch($row['url']);
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

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['Name']));

    $values['email'] = $pData['Email'];

    $values['phone_numbers'] = json_encode(array($pData['PhoneNoSpace']));

    $fullAddress = '';

    $education = array();
    foreach($html->find('.vertical-spacing-bottom-mobile div') as $item)
    {
        if(strpos($item->innertext, 'Education') !== false)
        {
            foreach($item->find('p') as $item)
            {
                $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('.vertical-spacing-bottom-mobile div') as $item)
    {
        if(strpos($item, 'Admissions') !== false)
        {
            foreach($item->find('p') as $item)
            {
                if(strpos(strtolower($item->plaintext), 'court'))
                {
                    $court_admissions[] = $item->plaintext;
                }
                else
                {
                    $bar_admissions[] = $item->plaintext;
                }
            }
        }
    }

    $practice_areas = array();
    foreach($html->find('.area-block.page-break-inside-avoid') as $item)
    {
        if(strpos(strtolower($item->innertext), 'related services') !== false)
        {
            foreach($item->find('a') as $practice)
            {
                $practice_areas[] = trim($practice->plaintext);
            }
        }
    }

    $positions = json_encode(array($pData['Role']));

    if($html->find('.readmore__content', 0))
    {
        $values['description'] = $html->find('.readmore__content', 0)->plaintext;
    }

    $photo = $base_url.$pData['ImageUrl'];
    $thumb = $base_url.$pData['ImageUrl'];

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

    if($html->find('.hero__location-name', 0))
    {
        $primaryAddress = $html->find('.hero__location-name', 0)->plaintext;
    }

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));

    $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute(array(
        $values['names'],
        $values['email'],
        '',
        $fullAddress,
        $primaryAddress,
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
        trim($values['description']),
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