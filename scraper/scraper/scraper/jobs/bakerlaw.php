<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_url = 'https://www.bakerlaw.com';
$spider_name = 'bakerlaw';
$firm_name = 'Baker & Hostetler LLP';

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

    if($html->find('.showbiophoto img', 0))
    {
        $pData['image'] = $html->find('.showbiophoto img', 0)->src;
    }

    if(empty($linkedIn)) { $linkedIn = ''; }

    $values['names'] = json_encode(explode(' ', $pData['post_title']));

    $values['phone_numbers'] = json_encode(array($pData['post_contact']['poa_person_contact_phone']));

    $values['email'] = $pData['post_contact']['poa_person_contact_email'];

    $positions = json_encode(array($pData['taxonomies']['poa_position_taxonomy'][0]));

    $values['description'] = $pData['content'];

    if(!empty($pData['images']['bh_headshot']['url']))
    {
        $image = $pData['images']['bh_headshot']['url'];
    }
    else
    {
        $image = '';
    }
 
    $photo = $image;
    $thumb = $image;

    $education = array();
    $education_html = @str_get_html(get_string_between($html, '<h3>Education</h3>', '</ul>'));
    if(!empty($education_html))
    {
        foreach($education_html->find('li') as $item)
        {
            $education[] = str_replace(array('Of', 'And'), array('of', 'and'), ucwords(trim($item->plaintext)));
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

    $admission_html = @str_get_html(get_string_between($html, '<h3>Bar Admissions</h3>', '</ul>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('li') as $item)
        {
            $bar_admissions[] = $admission;
        }
    }

    $admission_html = @str_get_html(get_string_between($html, '<h3>Court Admissions</h3>', '</ul>'));
    if(!empty($admission_html))
    {
        foreach($admission_html->find('li') as $item)
        {
            $court_admissions[] = $admission;
        }
    }

    $practice_areas = array();

    if($html->find('[aria-label="Areas of Focus"]', 0))
    {
        $ul = $html->find('[aria-label="Areas of Focus"]', 0);
        foreach($ul->find('a') as $item)
        {
            $practice_areas[] = trim($item->plaintext);
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

    $primaryAddress = $pData['taxonomies']['poa_admission_state_taxonomy'][0];

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