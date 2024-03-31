<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.foley.com';
$spider_name = 'foley';
$firm_name = 'Foley & Lardner LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    $pData = json_decode($row['data'], 1);
    foreach ($pData as $key => $value) {
       if(empty($value))
       {
        $pData[$key] = '';
       }   
    }

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

    $values['names'] = json_encode(explode(' ', $html->find('.people-single__name', 0)->plaintext));

    if($html->find('.people-single__offices-number', 0))
    {
        $values['phone_numbers'] = json_encode(array($html->find('.people-single__offices-number', 0)->plaintext));
    }
    else
    {
        $values['phone_numbers'] = json_encode(array([]));
    }

    if($html->find('.people-single__emails a', 0))
    {
        $values['email'] = cfDecodeEmail(str_replace('/cdn-cgi/l/email-protection#', '', $html->find('.people-single__emails a', 0)->href));
    }
    else
    {
        $values['email'] = '';
    }

    $positions = json_encode(array($html->find('.people-single__title', 0)->plaintext));

    $values['description'] = $html->find('.block-content-section', 0)->innertext;

    if($html->find('.people-single__figure img', 0))
    {
      $pData['Image'] = $html->find('.people-single__figure img', 0)->src;   
    }
 
    $photo = $base_url.$pData['Image'];
    $thumb = $base_url.$pData['Image'];

    $education = array();
    $education_html = @str_get_html(get_string_between($html, 'Education', '</ul>'));
    $ex = explode('\n', strip_tags($education_html));
    foreach($ex as $item)
    {
        if(!empty(trim($item)))
        {
            $education[] = trim($item);
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
    
    $admission_html = @str_get_html(get_string_between($html, '<p class="BioMarketingHeader">Admissions</p>', '</div>'));
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

    if($html->find('.people-single__office a', 0))
    {
        $primaryAddress = $html->find('.people-single__office a', 0)->plaintext;
    }
    else
    {
        $primaryAddress = '';
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