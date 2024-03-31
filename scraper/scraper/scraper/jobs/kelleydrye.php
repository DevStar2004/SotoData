<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.kelleydrye.com';
$spider_name = 'kelleydrye';
$firm_name = 'Kelley Drye &amp; Warren LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

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

    if($html->find('.flex.gap-5.items-center a', 0))
    {

        var_dump($row);

        $values['vCard'] = $base_url.$html->find('.flex.gap-5.items-center a', 0)->href;

        if(empty($linkedIn)) { $linkedIn = ''; }

        $values['names'] = json_encode(explode(' ', $pData['name']));

        foreach($html->find('.presentation') as $item)
        {
            if(strpos($item->plaintext, 'Phone number') !== false)
            {
                $values['phone_numbers'] = json_encode(array($item->find('.items-center span', 0)->plaintext));
                break;
            }
        }

        if(empty($values['phone_numbers']))
        {
            $values['phone_numbers'] = '';
        }

        foreach($html->find('a') as $item)
        {
            if(strpos($item->href, 'mailto') !== false)
            {
                $values['email'] = str_replace('mailto:', '', $item->href);
                break;
            }
        }

        if(empty($values['email']))
        {
            $values['email'] = '';
        }

        $fullAddress = '';

        $education = array();
        foreach($html->find('.schools .school') as $item)
        {
            $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
        }

        $bar_admissions = array();
        $court_admissions = array();

        foreach($html->find('.bar-admissions') as $item)
        {
            if(strpos($item->innertext, 'bar-admission') !== false)
            {
                foreach($item->find('.bar-admission') as $item)
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
        foreach($html->find('.practice-wrapper ul li') as $item)
        {
            foreach($item->find('a') as $item)
            {
                $practice_areas[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }

        $languages = array();
        foreach($html->find('.languages .language') as $item)
        {
            if(strpos($item->innertext, 'p') !== false)
            {
                foreach($item->find('.language-name') as $item)
                {
                    $languages[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
                }
            }
        }

        if(count($languages)<1)
        {
            $languages[] = 'N.A.';
        }

        $positions = json_encode(array($html->find('.flex.relative.flex-col.gap-10 p', 0)->plaintext));

        $values['description'] = $html->find('.prose', 0)->plaintext;

        $photo = $base_url.$html->find('.relative.object-contain', 0)->src;
        $thumb = $base_url.$html->find('.relative.object-contain', 0)->src;

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

        $values['vCard'] = '';

        foreach($html->find('.presentation') as $item)
        {
            if(strpos($item->plaintext, 'Location') !== false)
            {
                $primaryAddress = json_encode(array($item->find('a', 1)->plaintext));
                break;
            }
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

        var_dump($q->errorInfo());

        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));

        unset($values);
        unset($law_school);
        unset($jd_year);
        unset($fullAddress);
        unset($primaryAddress);
    }

}

@unlink($spider_name.'_temp.vcf');
?>