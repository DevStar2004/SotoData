<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.fisherphillips.com';
$spider_name = 'fisherphillips';
$firm_name = 'Fisher & Phillips LLP';

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

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    if(!empty($pData['name']))
    {

        if(!empty($pData['offices_info'][0]['repeater_module_office']['phone']))
        {
            $values['phone_numbers'] = json_encode(array($pData['offices_info'][0]['repeater_module_office']['phone']));
        }
        else
        {
            $values['phone_numbers'] = '';
        }

        $values['email'] = $pData['email'];

        $fullAddress = '';

        $education = array();
        if($html->find('#panel-credentials ul', 0))
        {
            $ul = $html->find('#panel-credentials ul', 0);
            foreach($ul->find('li') as $item)
            {
                $education[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }

        $bar_admissions = array();
        $court_admissions = array();

        if($html->find('#panel-credentials ul', 1))
        {
            $ul = $html->find('#panel-credentials ul', 1);
            foreach($ul->find('li') as $item)
            {
                $bar_admissions[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }

        if($html->find('#panel-credentials ul', 2))
        {
            $ul = $html->find('#panel-credentials ul', 2);
            foreach($ul->find('li') as $item)
            {
                $court_admissions[] = trim(preg_replace('/\s+/', ' ', $item->plaintext));
            }
        }

        $practice_areas = array();
        foreach($html->find('ul.paddingBottomStandard.type__body-small') as $item)
        {
            foreach($item->find('li') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
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

        if(!empty($pData['content_data']['position']['name']))
        {
             $positions = json_encode(array(str_replace(',  ', '', $pData['content_data']['position']['name'])));
        }
        else
        {
            $positions = '[]';
        }

        $values['description'] = trim(str_replace('Overview', '', $html->find('#panel-overview', 0)->plaintext));

        if($html->find('img[height="552"]', 0))
        {
            $pData['image'] = $html->find('img[height="552"]', 0)->src;
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

        if($html->find('.u-underline-on-hover.u-standard-hover-bright-red', 0))
        {
            $primaryAddress = $html->find('.u-underline-on-hover.u-standard-hover-bright-red', 0)->plaintext;
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
            '',
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
    }

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