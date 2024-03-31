<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function superTrim($str)
{
    return trim(preg_replace('/\s+/', ' ', $str));
}

$base_url = 'https://www.susmangodfrey.com';
$spider_name = 'susmangodfrey';
$firm_name = 'Susman Godfrey L.L.P.';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($row['url']));
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

    $pData['location'] = trim($html->find('.mb-0.fs-18.font-body.fw-light', 0)->plaintext);

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'tel:') !== false)
        $pData['phone'] = str_replace('tel:', '', $item->href);
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    $values['phone_numbers'] = json_encode(array($pData['phone']));

    $values['email'] = $pData['email'];

    if($html->find('p.mb-1', 0))
    {
        $positions = json_encode(array(trim($html->find('p.mb-1', 0)->plaintext)));
    }
    else
    {
        $positions = '[]';
    }

    $values['description'] = superTrim($html->find('#overview', 0)->innertext);;
 
    $photo = $pData['image'];
    $thumb = $photo;

    $education = array();
    foreach($html->find('.widget-content') as $item)
    {
        if(strpos(strtolower($item->innertext), 'education') !== false)
        {
            foreach($item->find('li') as $res)
            {
                $education[] = trim($res->plaintext);
            }
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

    foreach($html->find('.widget-content') as $item)
    {
        if(strpos(strtolower($item->innertext), 'admissions') !== false)
        {
            foreach($item->find('li') as $res)
            {
                if(strpos(strtolower($res->plaintext), 'court') !== false)
                {
                    $court_admissions[] = trim($res->plaintext);
                }
                else
                {
                    $bar_admissions[] = trim($res->plaintext);
                }
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

    if(empty($law_school))
    {
        $law_school = '';
    }

    $practice_areas = array();

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