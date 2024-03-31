<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.cooley.com';
$spider_name = 'cooley';
$firm_name = 'Cooley LLP and Cooley (UK) LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $data = fetch($row['url']);

    $html = str_get_html($data);

    $pData['Name'] = $html->find('.hero-person-info-details.-name', 0)->plaintext;

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'tel:') !== false)
        {
            $pData['Phone'] = str_replace('tel:', '', $link->href);
            break;
        }
    }

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), 'mailto:') !== false)
        {
            $pData['Email'] = str_replace('mailto:', '', $link->href);
            break;
        }
    }

    foreach($html->find('a') as $link)
    {
        if(strpos(strtolower($link->href), '/about/geographies/') !== false)
        {
            $pData['Location'] = $link->plaintext;
            break;
        }
    }

    $pData['Title'] = $html->find('.hero-person-info-details.h5', 0)->plaintext;

    if($html->find('.hero-person-image source', 0))
    {
        $pData['image'] = $html->find('.hero-person-image source', 0)->srcset;
    }
    else
    {
        $pData['image'] = '';
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

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['Name']));

    $values['phone_numbers'] = json_encode(array($pData['Phone']));

    $values['email'] = $pData['Email'];

    $positions = json_encode(array($pData['Title']));

    $values['description'] = $html->find('.wysiwyg-content', 0)->innertext;
 
    $photo = $base_url.$pData['image'];
    $thumb = $base_url.$pData['image'];

    $education = array();
    if($ul = $html->find('.people-credential-item', 0))
    {
        foreach($ul->find('p') as $item)
        {
            $education[] = trim($item->plaintext);
        }
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

    $admission_html = @str_get_html(get_string_between($html, '<h2 class="h2">Admissions &amp; credentials</h2>', '</div>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('p') as $item)
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
    foreach($html->find('.related-services') as $item)
    {
        if(strpos(strtolower($item->innertext), 'related services') !== false)
        {
            foreach($item->find('a') as $res)
            {
                $practice_areas[] = trim($res->plaintext);
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

    $primaryAddress = $pData['Location'];

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