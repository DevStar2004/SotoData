<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.friedfrank.com';
$spider_name = 'friedfrank';
$firm_name = 'Fried, Frank, Harris, Shriver & Jacobson LLP';

$q = $pdo->prepare('SELECT * FROM `queue` WHERE `status`=\'pending\' AND `spider_name`=? LIMIT 20');
$q->execute(array($spider_name));

foreach ($q as $row) {

    var_dump($row);

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

    $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&useProxy=1&url='.urlencode($row['url']));
    $pData = @json_decode($row['data'], 1);

    if(empty($data) || strpos($data, 'Page not found') !== false)
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }
    else
    {

        $html = str_get_html($data);

        if(!empty($row['data'])) { $pData = json_decode($row['data'], 1); }

        $names = explode(' ', $pData['name']);

        $email = @$pData['email'];

        $primaryAddress = @$pData['location'];

        foreach($html->find('a') as $item)
        {
            if(strpos(strtolower($item->href), 'linkedin.com') !== false)
            {
                $linkedIn = $item->href;
                break;
            }
        }

        $phone_numbers[] = $pData['phone'];

        $findUL = str_get_html(get_string_between($html->innertext, 'Education</h3>', '</div>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('p') as $li)
            {
                $education[] = trim($li->plaintext);
            }
        }

        $findUL = str_get_html(get_string_between($html->innertext, 'Bar admissions</h3>', '</div>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('p') as $li)
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

        $practice_areas[] = trim($pData['practice']);

        $positions[] = $pData['position'];

        foreach($html->find('p') as $item)
        {
            if(strlen($item->plaintext)>120)
            {
                $description .= $item->innertext;
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

        if(!empty($pData['image']))
        {
            $image = $pData['image'];
        }

        $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);
        
        if(!$html->find('.hero-bio__info-name', 0))
        {
            continue;
        }

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
            json_encode(array()), //languages (JSON)
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