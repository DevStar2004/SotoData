<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mslaw.com';
$spider_name = 'mslaw';
$firm_name = 'Miles & Stockbridge';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($row['url']));
    $html = str_get_html($data);

    $pData = json_decode('{'.get_string_between($html, '"@context":"https://schema.org",', ']}</script></head>').']}', 1);

    $primaryAddress = '';

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'locations/') !== false)
        {
            $primaryAddress = str_replace(' ', '', $item->plaintext);
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

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'vcard') !== false)
        {
            $values['vCard'] = $link->href;
            break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', trim($html->find('h1', 0)->plaintext)));

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'tel:') !== false)
        {
            $values['phone_numbers'] = json_encode(array($item->href));
        }
    }

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'mailto:') !== false)
        {
            $values['email'] = $item->href;
        }
    }

    $fullAddress = '';

    $i = 0;
    $education = array();
    foreach($html->find('ul') as $item)
    {
        if(strpos($item->class, 'BulletedList-') !== false)
        {
            if($i == 2)
            {
                foreach($item->find('li') as $res)
                {
                    $education[] = trim($res->innertext);
                }
            }

            $i++;

        }
    }

    $i = 0;
    $bar_admissions = array();
    foreach($html->find('ul') as $item)
    {
        if(strpos($item->class, 'BulletedList-') !== false)
        {
            if($i == 3)
            {
                foreach($item->find('li') as $res)
                {
                    $bar_admissions[] = trim($res->innertext);
                }
            }

            $i++;

        }
    }

    $i = 0;
    $court_admissions = array();
    foreach($html->find('ul') as $item)
    {
        if(strpos($item->class, 'BulletedList-') !== false)
        {
            if($i == 6)
            {
                foreach($item->find('li') as $res)
                {
                    $court_admissions[] = trim($res->innertext);
                }
            }

            $i++;

        }
    }

    $i = 0;
    $practice_areas = array();
    foreach($html->find('ul') as $item)
    {
        if(strpos($item->class, 'BulletedList-') !== false)
        {
            if($i == 0)
            {
                foreach($item->find('li') as $res)
                {
                    $practice_areas[] = trim($res->plaintext);
                }
            }

            $i++;

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

    $positions = json_encode(array(str_replace(',  ', '', $html->find('#main div div div div div', 0)->plaintext)));

    $str = '';
    foreach($html->find('p') as $item)
    {
        $str .= $item->plaintext.'<br/>';
    }

    $values['description'] = $str;

    if(empty($pData['image']))
    {
        $pData['image'] = '';
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