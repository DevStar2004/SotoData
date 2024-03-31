<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.fr.com/';
$spider_name = 'fr';
$firm_name = 'Fish & Richardson P.C.';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

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

    var_dump($pData);

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    $values['phone_numbers'] = json_encode(array($pData['phone']));

    $values['email'] = $pData['email'];

    if(empty($values['email']))
    {
        $values['email'] = '';
    }

    $positions = json_encode(array($pData['title']));

    $values['description'] = $html->find('.mce-editor', 0)->innertext;
 
    $photo = $pData['image'];
    $thumb = $pData['image'];

    $education = array();
    foreach($html->find('section.bio-widget') as $item)
    {
        if(strpos($item->plaintext, 'Education') !== false)
        {
            foreach($item->find('li') as $value)
            {
                $education[] = trim($value->plaintext);
            }
        }
    }

    $languages = array();

    $bar_admissions = array();
    $court_admissions = array();
    foreach($html->find('section.bio-widget') as $item)
    {
        if(strpos($item->plaintext, 'Admissions') !== false)
        {
            foreach($item->find('li') as $value)
            {
                $admission = trim(str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($value->plaintext))));
                if(strpos(strtolower($admission), 'court') !== false)
                {
                    $court_admissions[] = $admission;
                }
                else
                {
                    $bar_admissions[] = $admission;
                }
            }
        }
    }

    $practice_areas = array();
    foreach($html->find('section.bio-widget') as $item)
    {
        if(strpos($item->plaintext, 'Services') !== false)
        {
            foreach($item->find('li') as $value)
            {
                $practice_areas[] = trim($value->plaintext);
            }
        }
    }

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
        if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false)
        {
            $law_school = $value;
            break;
        }
    }

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

    $primaryAddress = $pData['location'];

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
        if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false)
        {
            $law_school = $value;
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
        '',
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
        sEncode(trim($values['description'])),
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