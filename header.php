<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SotoData - Attorney Data Solutions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
  <style type="text/css">
    .nav-item
    {
      padding: 10px;
    }
    .btn-info, .bg-info
    {
      border: none !important;
      background: rgba(17, 142, 155, 1) !important;
      color: #FFF !important;
    }
    .btn-info:hover
    {
      opacity: 0.85;
    }
    .text-info
    {
      color: rgb(17, 142, 155) !important;
    }
    body, h1, h2, h3, h4, p
    {
      font-family: 'Roboto', sans-serif;
    }
    img
    {
      max-width: 100%;
    }
    .box-icon
    {
      width: 50px !important;
    }
    .item-t-bottom {
        width: 45px;
        height: 45px;
        object-fit: cover;
        object-position: center;
        border-radius: 100%;
        margin-left: 15px;
        margin-bottom: 15px;
        border: 4px solid rgb(17, 142, 155);
    }
  </style>
</head>
<body style="padding-top: 70px;" id="home">

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999999; border-bottom: 2px solid #888;">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $root; ?>">
            <img src="<?php echo $root; ?>/img/logo.png">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="<?php echo $root; ?>/#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root; ?>/#pricing">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root; ?>/#testimonials">Testimonials</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root; ?>/#about-us">About us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tel:(315) 210-4190">(315) 210-4190</a>
                </li>
            </ul>
            <a class="btn btn-info btn-lg" href="<?php echo $root; ?>/login">Login</a>
        </div>
    </div>
</nav>