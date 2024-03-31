<?php

include 'config.php';
include 'vCardGen.php';

$q = $pdo->prepare('SELECT * FROM `people` WHERE `id`=?');
$q->execute(array($_GET['person']));
$row = $q->fetch(PDO::FETCH_ASSOC);

$name = trim(implode(' ', json_decode($row['names'], 1)));
$names = explode(' ', $name);

$positions = json_decode($row['positions']);
$phone_numbers = json_decode($row['phone_numbers']);

header("Content-type: text/x-vcard; charset=utf-8");
header("Content-Disposition: attachment; filename=\"".$name.".vcf\";");

$vcard = new vCard;

$vcard->setName($names[0], $names[1]);

if(!empty($row['fullAddress']))
{
    $address = $row['fullAddress'];
}
else
{
    $address = $row['primaryAddress'];
}

// Every set functions below are optional
$vcard->setTitle($positions[0]);
$vcard->setPhone($phone_numbers[0]);
$vcard->setURL($row['source']);
$vcard->setMail($row['email']);
$vcard->setAddress(array(
    "street_address" => $address,
));
$vcard->setNote('Downloaded from https://sotodata.com');

if(!empty($row['photo_headshot']))
{
    $f = file_get_contents($row['photo_headshot']);
    file_put_contents('temp.png', $f);
    $vcard->setPhoto('temp.png');
}

echo $vcard;

?>