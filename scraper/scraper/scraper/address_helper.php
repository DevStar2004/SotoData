<?php

include 'config.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$q = $pdo->prepare('SELECT `names`, `fullAddress`, `primaryAddress`, `phone_numbers`, `id` FROM `people` WHERE `fullAddress`=\'\' AND `phone_numbers` NOT LIKE \'%+%\'
 ORDER BY `id` ASC');
$q->execute();
$people = $q->fetchAll(PDO::FETCH_ASSOC);

$state_codes = json_decode('{"Mississippi": [228, 601, 662, 769], "Northern Mariana Islands": [670], "Oklahoma": [405, 539, 580, 918], "Delaware": [302], "Minnesota": [218, 320, 507, 612, 651, 763, 952], "Illinois": [217, 224, 309, 312, 331, 618, 630, 708, 773, 779, 815, 847, 872], "Arkansas": [479, 501, 870], "New Mexico": [505, 575], "Indiana": [219, 260, 317, 574, 765, 812], "Maryland": [240, 301, 410, 443, 667], "Louisiana": [225, 318, 337, 504, 985], "Idaho": [208], "Wyoming": [307], "Tennessee": [423, 615, 731, 865, 901, 931], "Arizona": [480, 520, 602, 623, 928], "Iowa": [319, 515, 563, 641, 712], "Michigan": [231, 248, 269, 313, 517, 586, 616, 734, 810, 906, 947, 989], "Kansas": [316, 620, 785, 913], "Utah": [385, 435, 801], "American Samoa": [684], "Oregon": [458, 503, 541, 971], "Connecticut": [203, 475, 860], "Montana": [406], "California": [209, 213, 310, 323, 408, 415, 424, 442, 510, 530, 559, 562, 619, 626, 650, 657, 661, 669, 707, 714, 747, 760, 805, 818, 831, 858, 909, 916, 925, 949, 951], "Massachusetts": [339, 351, 413, 508, 617, 774, 781, 857, 978], "Puerto Rico": [787, 939], "South Carolina": [803, 843, 864], "New Hampshire": [603], "Wisconsin": [262, 414, 534, 608, 715, 920], "Vermont": [802], "Georgia": [229, 404, 470, 478, 678, 706, 762, 770, 912], "North Dakota": [701], "Pennsylvania": [215, 267, 272, 412, 484, 570, 610, 717, 724, 814, 878], "West Virginia": [304, 681], "Florida": [239, 305, 321, 352, 386, 407, 561, 727, 754, 772, 786, 813, 850, 863, 904, 941, 954], "Hawaii": [808], "Kentucky": [270, 502, 606, 859], "Alaska": [907], "Nebraska": [308, 402, 531], "Missouri": [314, 417, 573, 636, 660, 816], "Ohio": [216, 234, 330, 419, 440, 513, 567, 614, 740, 937], "Alabama": [205, 251, 256, 334, 938], "Rhode Island": [401], "Washington, DC": [202], "Virgin Islands": [340], "South Dakota": [605], "Colorado": [303, 719, 720, 970], "New Jersey": [201, 551, 609, 732, 848, 856, 862, 908, 973], "Virginia": [276, 434, 540, 571, 703, 757, 804], "Guam": [671], "Washington": [206, 253, 360, 425, 509], "North Carolina": [252, 336, 704, 828, 910, 919, 980, 984], "New York": [212, 315, 347, 516, 518, 585, 607, 631, 646, 716, 718, 845, 914, 917, 929], "Texas": [210, 214, 254, 281, 325, 346, 361, 409, 430, 432, 469, 512, 682, 713, 737, 806, 817, 830, 832, 903, 915, 936, 940, 956, 972, 979], "Nevada": [702, 725, 775], "Maine": [207]}', 1);

foreach ($people as $row) {

    if(strlen($row['phone_numbers'])>5)
    {
        $phone = json_decode($row['phone_numbers'], 1)[0];

        $find = array(
            '(',
            ')',
            '.',
            '1 ',
        );

        $replace = array(
            '',
            '',
            ' ',
            ' ',
        );

        if(!empty($phone))
        {
            $phone = trim(str_replace($find, $replace, preg_replace("/[^0-9.]/", "", $phone)));
            if(strpos($phone, ' ') !== false) { $digits = explode(' ', $phone)[0]; }
            else
            {
                $digits = substr($phone, 0, 3);
            }
            foreach($state_codes as $key => $value)
            {
                foreach($value as $code)
                {
                    if($code == $digits)
                    {
                        $state = $key;
                    }
                }
            }

            if(isset($state))
            {
                $q = $pdo->prepare('UPDATE `people` SET `fullAddress`=? WHERE `id`=?');
                $q->execute(array('United States, '.$state, $row['id']));
                unset($state);
            }

        }
    }

}

?>