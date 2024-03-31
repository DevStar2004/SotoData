<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.goodwinlaw.com/';
$spider_name = 'goodwinlaw';
$firm_name = 'Goodwin Procter LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);

    $values = array();

    if(!$html->find('h1 span', 0))
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }

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
        if(strpos($link->href, 'mailto:') !== false || strpos($link->href, '&#109;&#97;&#105;') !== false)
        {
            $pData['email'] = str_replace('mailto:', '', html_entity_decode($link->href));
            break;
        }
    }

    foreach($html->find('a') as $link)
    {
        if(strpos($link->href, 'tel:') !== false || strpos($link->href, '&#109;&#97;&#105;') !== false)
        {
            $pData['phone'] = str_replace('tel:', '', html_entity_decode($link->href));
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $html->find('h1 span', 0)->plaintext));

    $values['phone_numbers'] = json_encode(array($pData['phone']));

    $values['email'] = $pData['email'];

    $positions = json_encode(array($pData['type']));

    if($html->find('.rich-text', 0))
    {
        $values['description'] = $html->find('.rich-text', 0)->innertext;
    }
    else
    {
        $values['description'] = '';
    }

    if($html->find('.util__alternatingBackground img', 1))
    {
        $image = $base_url.$html->find('.util__alternatingBackground img', 1)->src;
    }
    else
    {
        $image = '';
    }

    $photo = $image;
    $thumb = $image;

    $education = array();
    $education_html = @str_get_html(get_string_between($html, '<h3 class="type__h4">Education<!-- --></h3>', '<div class="PeopleAdmissions'));
    if(!empty($education_html))
    {
        foreach($education_html->find('div') as $item)
        {
            if(strpos($item->class, 'PeopleEducationItem_contentBlockListItem__') !== false)
            {
                $education[] = $item->plaintext.'<hr/>';
            }
        }
    }

    $languages = array();

    $bar_admissions = array();
    $court_admissions = array();
    
    $admission_html = @str_get_html(get_string_between($html, 'Bars', '</ul>'));
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
    $practice_html = @str_get_html(get_string_between($html, '<h2 class="type__h2 type--spaced-md">Areas of Practice<!-- --></h2>', '</ul>'));
    if(!empty($practice_html))
    {
        foreach($practice_html->find('li') as $item)
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

    $primaryAddress = $html->find('.rich-text__link', 0)->plaintext;

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