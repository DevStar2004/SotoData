<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.jenner.com';
$spider_name = 'jenner';
$firm_name = 'Jenner & Block LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

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

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'vcard') !== false)
        {
            $values['vCard'] = $link->href;
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    $values['phone_numbers'] = json_encode(array($pData['offices_info'][0]['repeater_module_office']['phone']));

    $values['email'] = $pData['email'];

    $fullAddress = '';

    $education = array();
    if($html->find('#credentials ul', 1))
    {
        $ul = $html->find('#credentials ul', 1);
        foreach($ul->find('li') as $item)
        {
            $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
        }
    }

    $bar_admissions = array();

    if($html->find('#credentials ul', 0))
    {
        $ul = $html->find('#credentials ul', 0);
        foreach($ul->find('li') as $item)
        {
            $bar_admissions[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
        }
    }

    $court_admissions = array();

    foreach($html->find('#bio_bar') as $item)
    {
        if(strpos($item->innertext, ' ') !== false)
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
    foreach($html->find('#areas-of-focus') as $item)
    {
        if(strpos($item->innertext, ' ') !== false)
        {
            foreach($item->find('li') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
            }
        }
    }

    $languages = array();
    foreach($html->find('#bio_languages') as $item)
    {
        if(strpos($item->innertext, 'Languages') !== false)
        {
            foreach($item->find('li') as $item)
            {
                $languages[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }
    }

    if(count($languages)<1)
    {
        $languages[] = 'N.A.';
    }

    $positions = json_encode(array(str_replace(',  ', '', $pData['content_data']['position']['name'])));

    if($html->find('#overview', 0))
    {
        $values['description'] = $html->find('#overview', 0)->plaintext;
    }
    else
    {
        $values['description'] = '';
    }

    $str = get_string_between($html, '<img src="/MediaLibraries/icemiller.com/IceMiller/Images/Attorney/Spotlights/', '"');

    if($html->find('img[alt="Attorney Image"]', 0))
    {
        $pData['image'] = $html->find('img[alt="Attorney Image"]', 0)->src;
    }
    else
    {
        $pData['image'] = 'https://sotodata.com/img/nophoto.png';
    }

    $photo = $pData['image'];
    $thumb = $pData['image'];

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
        if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false || strpos($school, 'lawschool') !== false)
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

    $values['vCard'] = '';

    foreach($html->find('div') as $item)
    {
        if(strpos($item->class, 'styles__officeInfo--') !== false)
        {
            $primaryAddress = $item->find('a', 0)->plaintext;
            break;
        }
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
        @$values['vCard'],
        @sEncode($fullAddress),
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
        sEncode(trim(strip_tags($values['description']))),
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