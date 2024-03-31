<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.bsk.com';
$spider_name = 'bsk';
$firm_name = 'Bond, Schoeneck &amp; King PLLC';

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
    $values['phone_numbers'] = json_encode(array($pData['phone']));

    if(empty($values['email']))
    {
        $values['email'] = '';
    }

    $education = array();
    foreach($html->find('.right-sidebar.right-sidebar-alt .accordion-container') as $item)
    {
        if(strpos($item->innertext, 'Education') !== false)
        {
            foreach($item->find('li') as $item)
            {
                $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('.right-sidebar.right-sidebar-alt .accordion-container') as $item)
    {
        if(strpos($item->innertext, 'Admissions') !== false)
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
    }

    $practice_areas = array();
    foreach($html->find('.right-sidebar.right-sidebar-alt .accordion-container') as $item)
    {
        if(strpos($item->innertext, 'Practice') !== false)
        {
            foreach($item->find('li a') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
            }
        }
    }

    $positions = json_encode(array($pData['position']));

    if($html->find('#profile .lead', 0))
    {
        $values['description'] = $html->find('#profile .lead', 0)->plaintext;
    }
    else
    {
        $values['description'] = '';
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

    $primaryAddress = $html->find('.panel-body a', 0)->plaintext;

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