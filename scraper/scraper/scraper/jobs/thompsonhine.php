<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.thompsonhine.com';
$spider_name = 'thompsonhine';
$firm_name = 'Thompson Hine LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    $names = '';
    $email = '';
    $primaryAddress = '';
    $phone_numbers = array();
    $education = array();
    $bar_admissions = array();
    $court_admissions = array();
    $practice_areas = array();
    $positions = array();
    $languages = array();
    $description = '';
    $image = '';
    $law_school = '';
    $jd_year = 0;

    $data = @file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($row['url']));
    $pData = @json_decode($row['data'], 1);
    $html = @str_get_html($data);

    if(!$html->find('h1', 0))
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }
    else
    {

        if(!empty($row['data'])) { $pData = json_decode($row['data'], 1); }

        $names = explode(' ', trim($html->find('h1.text-white', 0)->plaintext));

        foreach($html->find('a') as $item)
        {
            if(strpos($item->class, 'PersonHeader_email__') !== false)
            {
                $email = strrev($item->plaintext);
                break;
            }
        }

        foreach($html->find('p') as $item)
        {
            if(strpos($item->class, 'Locations_phone__') !== false)
            {
                $phone_numbers[] = $item->plaintext;
                break;
            }
        }

        foreach($html->find('a') as $item)
        {
            if(strpos($item->href, '/locations/') !== false)
            {
                $primaryAddress = $item->plaintext;
                break;
            }
        }

        foreach($html->find('p') as $item)
        {
            if(stripos($item->class, 'RichText_richtext') !== false)
            {
                $description .= $item->plaintext;
            }
        }

        foreach($html->find('a') as $item)
        {
            if(strpos(strtolower($item->href), 'linkedin.com') !== false)
            {
                $linkedIn = $item->href;
                break;
            }
        }

        $ul = str_get_html(get_string_between($html, '<h3>Education</h3>', '</ul>'));
        if(!empty($ul))
        {
            foreach($ul->find('li') as $li)
            {
                $education[] = trim($li->innertext);
            }
        }

        $findUL = $html->find('.bar-admissions ul', 0);
        if(!empty($findUL))
        {
            foreach($findUL->find('li') as $li)
            {
                if(strpos(strtolower($li->plaintext), 'court') === false)
                {
                    $bar_admissions[] = trim($li->plaintext);
                }
                else
                {
                    $court_admissions[] = trim($li->plaintext);
                }
            }
        }

        $findUL = str_get_html(get_string_between($html->innertext, 'Languages</h3>', '</ul>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('li') as $li)
            {
                $languages[] = trim($li->plaintext);
            }
        }

        $findUL = str_get_html(get_string_between($html->innertext, 'Focus Areas</h2>', '</ul>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('li') as $li)
            {
                $practice_areas[] = trim($li->plaintext);
            }
        }

        $positions[] = $html->find('.Position_position__a06H5', 0)->plaintext;

        foreach($html->find('img') as $item)
        {
            if(strpos(strtolower($item->src), 'headshot') !== false)
            {
                $image = $item->src;
                break;
            }
        }

        foreach($education as $value)
        {
            $school = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
            if(strpos($school, 'jd') !== false || strpos($school, 'doctor') !== false || strpos($school, 'lawschool') !== false)
            {
                $law_school = $value;
                break;
            }
        }

        if($html->find('img[alt="Biography image"]', 0))
        {
            $image = html_entity_decode(urldecode($base_url.$html->find('img[alt="Biography image"]', 0)->src));
        }

        if(resCode($image) != 200)
        {
            $image = '';
        }

        $jd_year = (int) @str_replace('-', '', filter_var($law_school, FILTER_SANITIZE_NUMBER_INT));

        $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
        $q->execute(array(json_encode($names)));

        $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array(
            json_encode($names), //names (JSON)
            $email, //email
            '', //vCard
            '', //fullAddress
            $primaryAddress, //primaryAddress
            $linkedIn, //linkedIn
            json_encode($phone_numbers), //phone numbers (JSON)
            '', //fax
            json_encode($education), //education (JSON)
            json_encode($bar_admissions), //bar admissions (JSON)
            json_encode($court_admissions), //court admissions (JSON)
            json_encode($practice_areas), //practice areas (JSON)
            '[]', //acknowledgements (JSON)
            '[]', //memberships (JSON)
            json_encode($positions), //positions (JSON)
            json_encode($languages), //languages (JSON)
            $row['url'], //source
            $description, //description
            time(), //last update epoch timestamp
            $image, //photo headshot url
            $image, //photo url
            $spider_name, //spider name
            $firm_name, //firm name
            $law_school, //law school
            $jd_year, //jd year
            0,
            NULL, //id (NULL)
        ));

        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
    }

}

?><br/>