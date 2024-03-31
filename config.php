<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
ob_start();

header('Access-Control-Allow-Origin: *'); 

if(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] !== 'https'){
    //header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    //exit();
}

$dir = realpath(dirname(__FILE__));

$dbhost = "24.199.117.42";
$dbname = "sotodata";
$dbuser = "amos";
$dbpass = "Amoslkj123456";

$stripe_sk = 'sk_test_51OcSkcK2GnXQPeBFUmMumtMyfYeTHyGJAW3YCqSAHuH2VrsBPn3lvIBceeQlBFhKMbalDOHnXFNd19bqUmPUVgxt00tgDRG1pH';
$stripe_monthly = 'https://buy.stripe.com/test_8wM5kLg5salw2c06oo';
$stripe_yearly = 'https://buy.stripe.com/test_4gw9B1aL8fFQ7wk145';

function send_mail($to, $subject, $message, $from)
{

  $headers = "From: ".strip_tags($from)."\r\n";
  $headers .= "Reply-To: ".strip_tags($from)."\r\n";
  $headers .= "BCC: devin@feis.link\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  mail($to, $subject, $message, $headers);

}

function firstXChars($string, $chars = 500)
{
    preg_match('/^.{0,' . $chars. '}(?:.*?)\b/iu', $string, $matches);
    return @$matches[0];
}

$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname;port=3306;charset=utf8mb4", $dbuser, $dbpass, array(PDO::ATTR_PERSISTENT => false));

$script_location = '/sotodata'; //for folders /test or /test1/test2

$root = 'https://'.$_SERVER['HTTP_HOST'].$script_location;
$_SEO = array();

if(strpos($script_location, '/') !== false)
{
  $in_folder = explode($script_location, $_SERVER['REQUEST_URI']);
  $next = $in_folder[1];
  foreach (explode('/', $next) as $key => $value)
  {
    $_SEO[$key] = $value;
  }
}
else
{
  foreach (explode('/', $_SERVER['REQUEST_URI']) as $key => $value)
  {
    $_SEO[$key] = $value;
  }
}

unset($_SEO[0]);

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

?>