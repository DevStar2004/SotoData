<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function superTrim($str)
{
    return trim(preg_replace('/\s+/', ' ', $str));
}

$base_url = 'https://www.mofo.com';
$spider_name = 'mofo';
$firm_name = 'Morrison & Foerster LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);

    $pData['name'] = $html->find('.profile-hero__headline', 0)->plaintext;
    $pData['email'] = $html->find('.profile-hero__details a', 0)->href;
    $pData['phone'] = str_replace('tel:', '', $html->find('.profile-hero__details a', 1)->href);
    $pData['image'] = $html->find('.profile-hero__image-inner img', 0)->src;


    $ex = explode('|', superTrim($html->find('.profile-hero__details--title-location', 0)->plaintext));

    $pData['title'] = $ex[0];
    $pData['location'] = $ex[1];    

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

    $values['email'] = str_replace('mailto:', '', $pData['email']);

    $positions = json_encode(array($pData['title']));

    $values['description'] = trim($html->find('.show-more', 0)->plaintext);
 
    $photo = $base_url.urldecode($pData['image']);
    $thumb = $photo;

    $education = array();
    $education_html = @str_get_html(substr(get_string_between($html, '<p class="profile-overview__display-label profile-overview__display-label--practices">Education</p>', '</ul>'), 2));
    if(!empty($education_html))
    {
        foreach($education_html->find('li') as $item)
        {
            $education[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
        foreach($education_html->find('p') as $item)
        {
            $education[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
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
    $court_admissions = array();
    
    $admission_html = @str_get_html(get_string_between($html, '<h3>Admissions</h3>', '</ul>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('li') as $item)
        {
            $admission = trim(str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext))));
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
    $practice_areas = array();
    $practice_area_html = @str_get_html(get_string_between($html, '<p class="profile-overview__display-label profile-overview__display-label--practices">Practices</p>', '</ul>'), 2);
    if(!empty($practice_area_html))
    {
        foreach($practice_area_html->find('li') as $item)
        {
            $practice_areas[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
        foreach($practice_area_html->find('p') as $item)
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