<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.bhfs.com';
$spider_name = 'bhfs';
$firm_name = 'Brownstein Hyatt Farber Schreck, LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch_quick($row['url']);
    $html = str_get_html($data);

    

    $pData = json_decode($row['data'], 1);

    $values = array();

    if($html->find('.bio-intro-title h1', 0))
    {
        foreach($html->find('a') as $link)
        {
            if(strpos(strtolower($link->href), 'linkedin') !== false)
            {
                $linkedIn = $link->href;
                break;
            }
        }

        $pData['image'] = @$html->find('.bio-intro-info img', 0)->src;

        if(empty($linkedIn)) { $linkedIn = ''; }

        $values['names'] = json_encode(explode(' ', $html->find('.bio-intro-title h1', 0)->plaintext));

        $values['phone_numbers'] = json_encode(array($html->find('.bio-data-list a', 1)->plaintext));

        $values['email'] = @html_entity_decode($html->find('.bio-data-list a', 0)->plaintext);

        $positions = json_encode(array(@$html->find('.bio-intro-category', 0)->plaintext));

        $values['description'] = @$html->find('.text-content.assetWrapper.TextAsset', 0)->innertext;

        $photo = $base_url.$pData['image'];
        $thumb = $base_url.$pData['image'];

        $education = array();
        $education_html = @str_get_html(get_string_between($html, '<h3>Education</h3>', '</div>'));
        if(!empty($education_html))
        {
            foreach($education_html->find('li') as $item)
            {
                $education[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
            }
        }

        $languages = array();

        $bar_admissions = array();
        $court_admissions = array();

        $admission_html = @str_get_html(get_string_between($html, '<h3>Admissions</h3>', '</ul>'));
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

        if(empty($law_school))
        {
            $law_school = '';
        }

        $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);

        $primaryAddress = $html->find('.bio-data-list li', 0)->plaintext;

        if(count($education)>0)
        {
            foreach($education as $value)
            {
                $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
                if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false)
                {
                    $law_school = $value;
                    break;
                }
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

    }

    $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
    $q->execute(array($row['id']));

    unset($values);
    unset($law_school);
    unset($jd_year);
    unset($fullAddress);
    unset($primaryAddress);
    unset($linkedIn);

    sleep(1);

}

?>