<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.bakermckenzie.com';
$spider_name = 'bakermckenzie';
$firm_name = 'Baker McKenzie';

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

    $values['names'] = json_encode(explode(' ', $pData['Name']));

    $values['phone_numbers'] = json_encode(array($pData['BusinessPhone']));

    if($html->find('[data-email]', 0))
    {
        $values['email'] = $html->find('[data-email]', 0)->{'data-email'}.'@'.$html->find('[data-email]', 0)->{'data-emaildom'};
    }
    else
    {
        $values['email'] = '';
    }

    $positions = json_encode(array($pData['AttorneyLevelTitle']));

    if($html->find('.text-copy', 0))
    {
        $values['description'] = $html->find('.text-copy', 0)->innertext;
    }
    else
    {
        $values['description'] = '';
    }
 
    $photo = $base_url.$pData['PhotoUrl'];
    $thumb = $base_url.$pData['PhotoUrl'];

    $education = array();
    $education_html = @str_get_html(get_string_between($html, 'Education', '</ul>'));
    if(!empty($education_html))
    {
        foreach($education_html->find('li') as $item)
        {
            $education[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
    }

    $languages = array();
    $languages_html = @str_get_html(get_string_between($html, 'Languages', '</ul>'));
    if(!empty($languages_html))
    {
        foreach($languages_html->find('li') as $item)
        {
            $languages[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    $admission_html = @str_get_html(get_string_between($html, 'Admissions', '</ul>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('li') as $item)
        {
            $admission = trim($item->plaintext);
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
    if($html->find('.list-content.links-list', 0))
    {
        $ul = $html->find('.list-content.links-list', 0);
        foreach($ul->find('a') as $item)
        {
            $practice_areas[] = trim($item->plaintext);
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

    $primaryAddress = $pData['Offices'][0]['Name'];

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