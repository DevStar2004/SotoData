<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.shearman.com';
$spider_name = 'shearman';
$firm_name = 'Shearman & Sterling';

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

    if(resCode($row['url']) != 200)
    {
        $q = $pdo->prepare('UPDATE `queue` SET `status`=\'complete\' WHERE `id`=?');
        $q->execute(array($row['id']));
        continue;
    }
    else
    {

        $html = str_get_html($data);

        if(!empty($row['data'])) { $pData = json_decode($row['data'], 1); }

        $names = explode(' ', trim(preg_replace('/\s+/', ' ', $html->find('.page-three-col__col-two h1', 0)->plaintext)));

        if($html->find('.js-ed-email', 0))
        {
            $email = $html->find('.js-ed-email', 0)->plaintext;
        }

        if($html->find('.contact-info td', 1))
        {
            $primaryAddress = trim($html->find('.contact-info td', 1)->plaintext);
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
                $phone_numbers[] = str_replace('tel:', '', $item->href);
                break;
            }
        }

        if($html->find('.qualifications-listing', 0))
        {
            $ul = $html->find('.qualifications-listing', 0);
            foreach($ul->find('.qualifications-listing__school') as $item)
            {
                $education[] = trim($item->plaintext);
            }
        }

        $findUL = str_get_html(get_string_between($html->innertext, '<h2>Admissions</h2>', '</ul>'));
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

        $findUL = str_get_html(get_string_between($html->innertext, '<h2>Languages</h2>', '</ul>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('li') as $li)
            {
                $languages[] = trim($li->plaintext);
            }
        }

        $findUL = str_get_html(get_string_between($html->innertext, '<p class="additional-content-title">Practices</p>', '</ul>'));
        if(!empty($findUL))
        {
            foreach($findUL->find('li a') as $li)
            {
                $practice_areas[] = trim($li->plaintext);
            }
        }

        $positions[] = trim($html->find('p.title', 0)->plaintext);

        $description = $html->find('[data-content-type="overview"] .rtf', 0)->innertext;

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

        if($html->find('picture img', 0))
        {
            $image = $html->find('picture img', 0)->src;
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