<?php
include '../config.php';
include '../simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$spider_name = 'mayerbrown';
$firm_name = 'Mayer Brown';
$base_url = 'https://www.mayerbrown.com';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $fullAddress = '';
    $primaryAddress = '';

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

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'tel:') !== false)
        {
            $pData['number'] = str_replace('tel:', '', $item->href);
        }
    }

    $values['names'] = json_encode(explode(' ', $pData['name']));
    $values['phone_numbers'] = json_encode(array($pData['number']));

    $education = array();
    foreach($html->find('.block') as $item)
    {
        if(strpos($item->innertext, 'Education') !== false)
        {
            foreach($item->find('.richtext p') as $value)
            {
                $education[] = $value->plaintext;
            }
        }
    }

    $bar_admissions = array();
    $court_admissions = array();

    if($html->find('[aria-label="Credentials"] ul', 0))
    {
        $list = $html->find('[aria-label="Credentials"] ul', 0);
        foreach($list->find('li') as $item)
        {
            $text = trim($item->plaintext);
            if(strpos(strtolower($text), 'court') !== false)
            {
                $court_admissions[] = $text;
            }
            else
            {
                $bar_admissions[] = $text;
            }
        }
    }

    $practice_areas = array();
    if($html->find('ul.styled-list__list', 0))
    {
        $list = $html->find('ul.styled-list__list', 0);
        foreach($list->find('li') as $item)
        {
            $practice_areas[] = trim($item->plaintext);
        }
    }

    $positions = '';
    foreach($html->find('p') as $item)
    {
        if(strpos($item->innertext, 'PeopleProfessionalHeader_role') !== false)
        {
            $positions = json_encode(array($item->plaintext));
        }
    }

    $values['description'] = '';

    if($html->find('[property="og:image"]', 0))
    {
        $pData['image'] = $html->find('[property="og:image"]', 0)->content;
    }
    else
    {
        $pData['image'] = '';
    }

    $photo = $pData['image'];
    $thumb = $pData['image'];

    foreach($education as $value)
    {
        $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', ' ', $value));
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

    foreach($html->find('a') as $item)
    {
        if(strpos($item->href, 'locations/') !== false)
        {
            $primaryAddress = str_replace(' ', '', $item->plaintext);
        }
    }

    foreach($html->find('a') as $item)
    {
        if(strpos($item->innertext, 'class="PeopleProfessionalHeader_email') !== false)
        {
            $values['email'] = str_replace(' ', '', $item->plaintext);
        }
    }

    $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
    $q->execute(array($values['names']));

    $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute(array(
        $values['names'],
        $values['email'],
        '',
        $fullAddress,
        $primaryAddress,
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