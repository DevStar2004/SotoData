<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$spider_name = 'ropesgray';
$firm_name = 'Ropes &amp; Gray LLP';
$base_url = 'https://www.ropesgray.com';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);

    if(!empty($pData['name']))
    {

        foreach($html->find('a') as $link)
        {
            if(strpos(strtolower($link->href), 'linkedin') !== false)
            {
                $linkedIn = $link->href;
                break;
            }
        }
        if(empty($linkedIn)) { $linkedIn = 'https://www.linkedin.com/company/ropes-&-gray-llp/'; }

        $pData['phone'] = str_replace('T', '', $pData['phone']);

        $values['phone_numbers'] = json_encode(array($pData['phone']));
        $values['names'] = json_encode(explode(' ', $pData['name']));
        $values['email'] = $pData['email'];

        $education = array();
        if($html->find('#education-content ul', 0))
        {
            $list = $html->find('#education-content ul', 0);
            foreach($list->find('li') as $item)
            {
                $education[] = trim($item->plaintext);
            }
        }

        $bar_admissions = array();
        $court_admissions = array();

        if($html->find('#admissions-content ul', 0))
        {
            $list = $html->find('#admissions-content ul', 0);
            foreach($list->find('li') as $item)
            {
                if(strpos(strtolower($item->plaintext), 'court') !== false)
                {
                    $court_admissions[] = trim($item->plaintext);
                }
                else
                {
                    $bar_admissions[] = trim($item->plaintext);
                }
            }
        }

        $memberships = array();

        $languages = array();
        $languages[] = 'N.A.';

        $practice_areas = array();
        if($html->find('#practices-content ul', 0))
        {
            $list = $html->find('#practices-content ul', 0);
            foreach($list->find('li') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
            }
        }

        $positions = array();
        $positions[] = $pData['title'];

        $values['description'] = trim(get_string_between($html->find('#main-content .column-inner', 0)->innertext, '</hgroup>', '<ul class="nav nav-tabs hidden-phone">'));

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

        $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);

        foreach($html->find('.module-content-inner') as $item)
        {
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, '/en/locations/') !== false)
                {
                    $primaryAddress = $link->plaintext;
                    break;
                }
            }
        }

        if(empty($primaryAddress))
        {
            $primaryAddress = '';
        }

        if(empty($values['email']))
        {
            $values['email'] = '';
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
            json_encode($education),
            json_encode($bar_admissions), //bar admissions
            json_encode($court_admissions), //court admissions
            json_encode($practice_areas),
            '[]',
            json_encode($memberships),
            json_encode($positions),
            json_encode($languages),
            $row['url'],
            $values['description'],
            time(),
            $pData['image'],
            $pData['image'],
            $spider_name,
            $firm_name,
            $law_school,
            str_replace('-', '', $jd_year),
            0,
            NULL
        ));

    }

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