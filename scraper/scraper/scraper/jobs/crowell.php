<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.crowell.com';
$spider_name = 'crowell';
$firm_name = 'Crowell & Moring LLP';

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

    foreach($html->find('span.me-2') as $item)
    {
        if(strpos($item->class, 'styles__type__bioName') !== false)
        $pData['name'] = $item->plaintext;
    }

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'tel:') !== false)
        $pData['phone'] = @urldecode(str_replace('tel:', '', $item->href));
    }

    if(empty($pData['phone']))
    {
        $pData['phone'] = '';
    }

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'mailto:') !== false)
        $pData['email'] = str_replace('mailto:', '', $item->href);
    }

    foreach($html->find('span') as $item)
    {
        if(strpos($item->class, 'styles__type__position') !== false)
        $pData['title'] = $item->plaintext;
    }

    foreach($html->find('a') as $item)
    {
        if(strpos($item->class, 'styles__type__officeName') !== false)
        $pData['location'] = $item->plaintext;
    }

    if($html->find('picture source', 0))
    {
        $pData['image'] = $html->find('picture source', 0)->srcset;
    }
    else
    {
        $pData['image'] = '';
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['name']));

    $values['phone_numbers'] = json_encode(array($pData['phone']));

    $values['email'] = '';

    $positions = json_encode(array($pData['title']));

    $values['description'] = $html->find('.rte', 0)->innertext;
 
    $photo = $pData['image'];
    $thumb = $pData['image'];

    $education = array();
    $education_html = @str_get_html(get_string_between(strtolower($html), 'education', '</ul>'));
    if(!empty($education_html))
    {
        foreach($education_html->find('li') as $item)
        {
            $education[] = trim(str_replace(array('Of', 'And', 'Education'), array('of', 'and', ''), ucwords(trim($item->plaintext))));
        }
    }

    $languages = array();
    $lang_data = @strip_tags(get_string_between($html, '>Languages</p>', '</div>'));
    if(!empty($lang_data))
    {
        $languages = explode(', ', $lang_data);
    }

    $bar_admissions = array();
    $bar_admissions_html = @str_get_html(get_string_between(strtolower($html), 'bar admissions', '</ul>'));
    if(!empty($bar_admissions_html))
    {
        foreach($bar_admissions_html->find('li') as $item)
        {
            $bar_admissions[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
        }
    }

    $court_admissions = array();
    $practice_areas = array();

    $practice_areas = array();
    foreach($html->find('div.color-navy-blue') as $item)
    {
        if(strpos(strtolower($item->innertext), 'practices') !== false)
        {
            foreach($item->find('a') as $practice)
            {
                $practice_areas[] = trim($practice->plaintext);
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

    if(@strlen($pData['email'])<5)
    {
        $pData['email'] = '';
    }
    else
    {
        $pData['email'] = base64_decode($pData['email']);
    }

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));

    try {
        $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array(
            $values['names'],
            $pData['email'],
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
    } catch (Exception $e) {
        
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