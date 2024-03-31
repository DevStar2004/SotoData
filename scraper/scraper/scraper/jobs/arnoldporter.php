<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.arnoldporter.com';
$spider_name = 'arnoldporter';
$firm_name = 'Arnold & Porter Kaye Scholer LLP';

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

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['FormattedName']));

    $values['phone_numbers'] = json_encode(array($pData['PrimaryOfficePhone']));

    $values['email'] = $pData['BusinessEmail'];

    $positions = json_encode(array($pData['Title']));

    $values['description'] = $html->find('.rich-text-dropcap.rich-text-intro', 0)->innertext;

    if($html->find('.person-img img', 0))
    {
        $image = $html->find('.person-img img', 0)->src;
    }
    else
    {
        $image = '';
    }
 
    $photo = @$image;
    $thumb = @$photo;

    $education = array();
    if($html->find('.list ul', 0))
    {
        $ul = $html->find('.list ul', 0);
        foreach($ul->find('li') as $item)
        {
            $education[] = $item->plaintext;
        }
    }

    $languages = array();
    $languages_html = @str_get_html(get_string_between(strtolower($html), 'languages', '</ul>'));
    if(!empty($languages_html))
    {
        foreach($languages_html->find('li') as $item)
        {
            $languages[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
    }

    $bar_admissions = array();
    $bar_admissions_html = @str_get_html(get_string_between(strtolower($html), 'admissions', '</ul>'));
    if(!empty($bar_admissions_html))
    {
        foreach($bar_admissions_html->find('li') as $item)
        {
            $bar_admissions[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
    }

    $court_admissions = array();

    $practice_areas = array();
    $practice_areas_html = @str_get_html(get_string_between(strtolower($html), 'focus areas', '</ul>'));
    if(!empty($practice_areas_html))
    {
        foreach($practice_areas_html->find('li') as $item)
        {
            $practice_areas[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
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

    $primaryAddress = $pData['AllRelatedOfficeNames'];

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
        json_encode(array()), //court admissions
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