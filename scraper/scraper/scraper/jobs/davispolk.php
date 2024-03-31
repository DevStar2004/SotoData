<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.davispolk.com';
$spider_name = 'davispolk';
$firm_name = 'Davis Polk & Wardwell LLP';

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

    $data = @file_get_contents('http://137.184.158.149:3000/?api=get0&url='.urlencode($row['url']));
    $pData = @json_decode($row['data'], 1);
    $html = @str_get_html($data);

    if(!$html->find('.field-name--title', 0))
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }
    else
    {

        if(!empty($row['data'])) { $pData = json_decode($row['data'], 1); }

        $names = explode(' ', trim($html->find('.field-name--title', 0)->plaintext));

        foreach($html->find('a') as $item)
        {
            if(strpos($item->href, 'mailto:') !== false)
            {
                $email = str_replace('mailto:', '', $item->href);
                break;
            }
        }

        if($html->find('.field-name--field_lawyer_offices', 0))
        {
            $primaryAddress = $html->find('.field-name--field_lawyer_offices', 0)->plaintext;
        }

        foreach($html->find('a') as $item)
        {
            if(strpos(strtolower($item->href), 'linkedin.com') !== false)
            {
                $linkedIn = $item->href;
                break;
            }
        }

        foreach($html->find('a') as $item)
        {
            if(strpos($item->href, 'tel:') !== false)
            {
                $phone_numbers[] = urldecode(str_replace('tel:', '', $item->href));
                break;
            }
        }

        foreach($html->find('.lawyer--education .field-item') as $item)
        {
            $education[] = trim($item->innertext);
        }

        $findUL = str_get_html(get_string_between($html->innertext, 'class="lawyer--licenses">', '</ul>'));
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

        $findUL = $html->find('.lawyer--sidebar-languages', 0);
        if(!empty($findUL))
        {
            foreach($findUL->find('.field-item') as $li)
            {
                $languages[] = trim($li->plaintext);
            }
        }
        
        $findUL = $html->find('.lawyer--sidebar-capabilities', 0);
        if(!empty($findUL))
        {
            foreach($findUL->find('.field-item') as $li)
            {
                $practice_areas[] = trim($li->plaintext);
            }
        }

        if($html->find('.field-name--field_job_title.field-item', 0))
        {
            $positions[] = $html->find('.field-name--field_job_title.field-item', 0)->plaintext;
        }

        if($html->find('.field-name--field_job_title_custom.field-item', 0))
        {
            $positions[] = $html->find('.field-name--field_job_title_custom.field-item', 0)->plaintext;
        }

        if($html->find('.lawyer--bio', 0))
        {
            $description = $html->find('.lawyer--bio', 0)->innertext;
        }

        if($html->find('.lawyer--headshot', 0))
        {
            $image = $base_url.@$html->find('.lawyer--headshot img', 0)->src;
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

        if(resCode($image) != 200)
        {
            $image = '';
        }

        $jd_year = (int) @filter_var($law_school, FILTER_SANITIZE_NUMBER_INT);

        $q = $pdo->prepare('DELETE FROM `people` WHERE `names`=? LIMIT 1');
        $q->execute(array(json_encode($names)));

        $q = $pdo->prepare('INSERT INTO `people` VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array(
            json_encode($names), //names (JSON)
            $email, //email
            '', //vCard
            '', //fullAddress
            trim($primaryAddress), //primaryAddress
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