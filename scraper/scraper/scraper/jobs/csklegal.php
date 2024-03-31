<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://csklegal.com';
$spider_name = 'csklegal';
$firm_name = 'Cole, Scott & Kissane, P.A.';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch($row['url']);

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

    if($html->find('[itemprop="telephone"]', 0))
    {
        $values['phone_numbers'] = json_encode(array($html->find('[itemprop="telephone"]', 0)->plaintext));
    }
    else
    {
        $values['phone_numbers'] = '[]';
    }

    $values['email'] = @str_replace('mailto:', '', html_entity_decode($html->find('#bioEmail a', 0)->href));

    if(empty($values['email']))
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }

    $positions = json_encode(array($pData['title']));

    if($html->find('#bio_content p', 0))
    {
        $values['description'] = $html->find('#bio_content p', 0)->innertext;
    }
    else
    {
        $values['description'] = '';
    }

    $pData['image'] = $base_url.'/'.$pData['image'];

    $photo = $pData['image'];
    $thumb = $pData['image'];

    $education = array();
    $ul = $html->find('#bio_education', 0);
    foreach($ul->find('.expandableContent li') as $item)
    {
        $education[] = trim($item->plaintext);
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
    
    $ul = $html->find('#bio_bars', 0);
    if($html->find('#bio_bars', 0))
    {
        foreach($ul->find('li') as $item)
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
    foreach($html->find('#bio_area .results_list a') as $item)
    {
        $practice_areas[] = trim($item->plaintext);
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