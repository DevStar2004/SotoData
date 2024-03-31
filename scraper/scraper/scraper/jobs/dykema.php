<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.dykema.com';
$spider_name = 'dykema';
$firm_name = 'Dykema Gossett PLLC';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

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

    $values['phone_numbers'] = json_encode(array($pData['offices_info'][0]['repeater_module_office']['phone']));

    $values['email'] = $pData['email'];

    $positions = json_encode(array($pData['content_data']['position']['name']));

    $values['description'] = $html->find('.rte', 0)->innertext;
    
    $photo = $base_url.$pData['professionals_search_photo_url'];
    $thumb = $base_url.$pData['professionals_search_photo_url'];

    $education = array();
    if($ul = $html->find('.rc-collapse-content .space-y-3 ul', 0))
    {
        foreach($ul->find('li') as $item)
        {
            $education[] = $item->plaintext;
        }
    }

    $languages = array();
    $languages_html = @str_get_html(get_string_between(strtolower($html), 'languages', '</ul>'));
    if(!empty($languages_html))
    {
        foreach($languages_html->find('li') as $item)
        {
            if(strpos($item->plaintext, 'See More') === false)
            {
                $languages[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();
    
    $admission_html = @str_get_html(get_string_between($html, '<h3 class="type__body type__body--bold mb-3">Bar Admissions</h3>', '</ul>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('li') as $item)
        {
            $admission = trim(str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext))));
            if(strpos($admission, 'See More') === false)
            {
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
    }

    $practice_areas = array();
    $practice_html = @str_get_html(get_string_between($html, '<h4 class="type__h4 type__h4--bold color-light-blue mt-3">Practices</h4>', '</div>'));
    if(!empty($practice_html))
    {
        foreach($practice_html->find('a') as $item)
        {
            if(strpos($item->plaintext, 'See More') === false)
            {
                $practice_areas[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
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

    $primaryAddress = '';
    $primaryAddress = @$pData['content_data']['offices_info-repeater_module_office-office'][0]['name'];

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