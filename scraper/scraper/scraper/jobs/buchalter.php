<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.buchalter.com';
$spider_name = 'buchalter';
$firm_name = 'Buchalter Law Firm';

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

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'vcard') !== false)
        {
            //$values['vCard'] = $base_url.$link->href;
            //break;
        }
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $html->find('[itemprop="name"]', 0)->plaintext));

    if(!$html->find('[itemprop="name"]', 0))
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }

    $values['phone_numbers'] = json_encode(array($html->find('[itemprop="telephone"]', 0)->plaintext));

    $values['email'] = $html->find('[itemprop="email"]', 0)->href;

    $values['email'] = cfDecodeEmail(str_replace('/cdn-cgi/l/email-protection#', '', $values['email']));

    $fullAddress = '';

    $education = array();
    $education[] = trim($html->find('#aside-education', 0)->plaintext);

    $bar_admissions = array();
    $court_admissions = array();

    foreach($html->find('#aside-court-admissions') as $item)
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
    foreach($html->find('#practice-areas') as $item)
    {
        if(strpos($item->innertext, ' ') !== false)
        {
            foreach($item->find('li a') as $item)
            {
                $practice_areas[] = trim($item->plaintext);
            }
        }
    }

    $practice_areas = array_unique($practice_areas);

    $languages = array();
    foreach($html->find('.field') as $item)
    {
        if(strpos($item->innertext, 'Languages') !== false)
        {
            foreach($item->find('.field__item') as $item)
            {
                $languages[] = sEncode(trim(preg_replace('/\s+/', ' ', $item->plaintext)));
            }
        }
    }

    if(count($languages)<1)
    {
        $languages[] = 'N.A.';
    }

    $positions = json_encode(array(str_replace(',  ', '', $html->find('[itemprop="jobTitle"]', 0)->plaintext)));

    $values['description'] = $html->find('#aside-overview', 0)->plaintext;

    $photo = $html->find('#attorney-photo', 0)->src;
    $thumb = $html->find('#attorney-photo', 0)->src;

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

    $primaryAddress = $html->find('#attorney-offices a', 0)->plaintext;

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