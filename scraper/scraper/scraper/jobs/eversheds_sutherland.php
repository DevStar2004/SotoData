<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.eversheds-sutherland.com';
$spider_name = 'eversheds_sutherland';
$firm_name = 'Eversheds Sutherland';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($row['url']));
    $html = @str_get_html($data);

    $pData = json_decode($row['data'], 1);

    $values = array();

    if(!empty($data))
    {

        foreach($html->find('a') as $link)
        {
            if(strpos(strtolower($link->href), 'linkedin') !== false)
            {
                $linkedIn = $link->href;
                break;
            }
        }

        if(empty($linkedIn)) { $linkedIn = ''; }

        $values['names'] = json_encode(explode(' ', str_replace(array('&nbsp;', ','), array(' ', ''), $pData['name'])));
        $values['phone_numbers'] = json_encode(array($pData['phone']));

        if($html->find('.peopleEmail a', 0))
        {
            $values['email'] = $html->find('.peopleEmail a', 0)->plaintext;
        }
        else
        {
            $values['email'] = '';
        }

        $primaryAddress = $pData['country'];

        $education = array();

        $bar_admissions = array();
        $court_admissions = array();
        $practice_areas = array();

        foreach($html->find('.practiseArea') as $item)
        {
            $practice_areas[] = trim(sEncode($item->plaintext));
        }

        $positions = json_encode(array($html->find('.peopleContent h2', 0)->plaintext));

        if($html->find('#people_notes', 0))
        {
            $values['description'] = $html->find('#people_notes', 0)->plaintext;
        }
        else
        {
            $values['description'] = '';
        }

        if($html->find('.peopleImage', 0))
        {
            $image = $base_url.$html->find('.peopleImage', 0)->src;
        }
        else
        {
            $image = '';
        }

        $photo = $image;
        $thumb = $image;

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

        $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
        $q->execute(array($values['names']));

        $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array(
            $values['names'],
            $values['email'],
            '',
            $fullAddress,
            preg_replace('/\s+/', ' ', $primaryAddress),
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
            json_encode(array('N.A.')),
            $row['url'],
            $values['description'],
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