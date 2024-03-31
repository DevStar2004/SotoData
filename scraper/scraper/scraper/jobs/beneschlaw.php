<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.beneschlaw.com';
$spider_name = 'beneschlaw';
$firm_name = 'Benesch, Friedlander, Coplan &amp; Aronoff LLP';

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
    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));
    $values['email'] = $pData['email'];
    $values['phone_numbers'] = json_encode(array($pData['offices_info'][0]['office_information']['phone_number']));

    $education = array();
    foreach($html->find('.sidebar__sidebarAccordionContainer--206347f8') as $item)
    {
        if(strpos($item->innertext, 'Education') !== false)
        {
            foreach($item->find('li span') as $item)
            {
                $education[] = $item->plaintext;
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    $practice_areas = array();
    foreach($html->find('.sidebar__sidebarAccordionContainer--206347f8') as $item)
    {
        if(strpos($item->innertext, 'Practices') !== false)
        {
            foreach($item->find('a') as $value)
            {
                $practice_areas[] = $value->plaintext;
            }
        }
    }

    $positions = json_encode(array($pData['content_data']['position']['name']));

    if($html->find('.rte.rte--bold p', 0))
    {
        $values['description'] = trim($html->find('.rte.rte--bold p', 0)->plaintext);
    }
    else
    {
        $values['description'] = '';
    }

    $photo = $base_url.$pData['asset_url'];
    $thumb = $base_url.$pData['attorney_square_320_url'];

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

    $primaryAddress = $html->find('.u-orange-on-hover', 0)->plaintext;

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
        $values['description'],
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