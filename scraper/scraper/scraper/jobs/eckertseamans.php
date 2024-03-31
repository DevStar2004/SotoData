<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.eckertseamans.com';
$spider_name = 'eckertseamans';
$firm_name = 'Eckert Seamans Cherin &amp; Mellott, LLC';

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

    $values['names'] = json_encode(array(
        $pData['first_name'],
        $pData['middle_initial'],
        $pData['last_name']
    ));
    $values['email'] = $pData['office']['people_offices_office_location_email_address'];
    $values['phone_numbers'] = json_encode(array($pData['office']['people_offices_office_location_phone_number']));

    $education = array();
    foreach($html->find('.person_bio__sidebar_set') as $item)
    {
        if(strpos($item, 'Education') !== false)
        {
            foreach($item->find('p') as $item)
            {
                $education[] = $item->plaintext;
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('.person_bio__sidebar_set') as $item)
    {
        if(strpos($item, 'Admissions:') !== false)
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
    if($html->find('.person_bio__sidebar_set ul', 0))
    {
        $list = $html->find('.person_bio__sidebar_set ul', 0);
        foreach($list->find('li') as $item)
        {
            $practice_areas[] = trim($item->plaintext);
        }
    }

    $positions = json_encode(array($pData['title']));

    $values['description'] = trim($html->find('[data-tab="overview"] p', 0)->plaintext);

    $photo = $pData['bioPhoto'];
    $thumb = $pData['bioPhoto'];

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

    if($html->find('.person_bio__sidebar_link', 0))
    {
        $primaryAddress = $html->find('.person_bio__sidebar_link', 0)->plaintext;
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
        $values['description'],
        time(),
        $thumb,
        $photo,
        $spider_name,
        $firm_name,
        $law_school,
        $jd_year,
        0,
        NULL)
    );

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