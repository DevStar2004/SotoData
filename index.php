<?php
include 'config.php';

if(!empty($_SEO[1]))
{
    switch ($_SEO[1]) {

        case 'privacy-policy':
            include 'privacy-policy.php';
            break;

        case 'terms-of-service':
            include 'terms-of-service.php';
            break;

        case 'people':
            include 'people.php';
            break;

        case 'crm':
            include 'crm.php';
            break;

        case 'jobs':
            include 'jobs.php';
            break;

        case 'get-started':
            include 'get-started.php';
            break;

        case 'login':
            include 'login.php';
            break;

        case 'contact-us':
            include 'contact-us.php';
            break;

        case 'logout':
            session_destroy();
            header('Location: '.$root);
            break;

        default:
            include 'home.php';
            break;
    }
}
else
{
    include 'home.php';
}
?>