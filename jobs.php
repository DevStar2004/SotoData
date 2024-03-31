<?php
if(empty($_SESSION['name']))
{
  header('Location: '.$root.'/logout');
  exit();
}

if(!empty($_POST))
{
  $_SESSION['post'] = $_POST;
}

if(isset($_SESSION['post']) && empty($_POST))
{
  $_POST = $_SESSION['post'];
}

?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SotoData - Jobs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;1,100;1,700&display=swap" rel="stylesheet">
  <style type="text/css">
    a
    {
      text-decoration: none !important;
      color: #FFF !important;
    }
    p, h1, h2, h3, h4, h5, body
    {
      font-family: 'Roboto', sans-serif;
    }
    .nav-item
    {
      overflow-y: hidden !important;
    }
    .nav-tabs
    {
      border-color: #000 !important;
    }
    .nav-link:not(.active)
    {
      color: #FFF !important;
    }
    .nav-link
    {
      padding: 5px !important;
      font-size: 16px;
      border-radius: 0 !important;
    }
    *
    {
      overflow-wrap: break-word;
    }
    img
    {
      max-width: 100% !important;
    }
    td
    {
      border-bottom-width: 0 !important;
    }
    table
    {
      table-layout: fixed !important;
    }
    @media only screen and (max-width: 700px) {
      *
      {
        overflow-x: hidden;
      }
      #navbar-left {
        display: none !important;
      }
      #main-container
      {
        padding-left: 20px !important;
      }
      .card-body
      {
        padding: 20px !important;
      }
      #headshot
      {
        padding: 0 !important;
        margin-bottom: 20px;
      }
      #photo_headshot
      {
        width: 100px !important;
      }
      #m-navbar
      {
        display: block !important;
        background: #004578;
      }
      .hide-on-small
      {
        display: none;
      }
    }

    .btn-success
    {
      background: #118E9B;
    }

    .btn-success:hover, .btn-success:active, .btn-success:focus, .btn-success:active:focus
    {
      background: #0e7d87 !important;
    }

    .selectedF
    {
      font-size: small;
      border-radius: 5px;
      cursor: pointer;
      display: inline-block;
      margin: 10px;
    }

    .bg-dark
    {
      background: #222 !important;
    }

    .practice-area, .badge
    {
      display: inline-block;
      font-size: 10px;
      background: #FECA05;
      color: #333;
      font-weight: bold;
      padding: 2.5px 10px;
      max-width: 200px;
      word-wrap: break-word;
      border-radius: 0.375rem;
      margin-right: 5px;
      margin-bottom: 5px;
    }

    .form-control
    {
      margin-bottom: 10px;
    }
  </style>
  <link rel="shortcut icon" type="image/png" href="<?php echo $root; ?>/img/favicon.png"/>
</head>
<body class="text-white" style="background: #FFF;">

  <div class="shadow p-2" id="m-navbar" style="display: none; background: #118E9B;">
    <div style="float: left;">
      <a class="btn btn-dark text-white" href="<?php echo $root; ?>/people">Home</a>
      <a class="btn btn-dark text-white" href="<?php echo $root; ?>/crm">CRM</a>
    </div>
    <div style="float: right;">
      <a href="<?php echo $root; ?>/logout" class="btn btn-dark">Logout</a>
    </div>
  </div>

  <div style="position: fixed; left: 0; top: 0; width: 5vw; background: #118E9B; height: 100vh;" id="navbar-left">
    <div style="position: relative; height: 100vh; width: 100%;">
      <div style="position: absolute; top: 2vw; left: 0; right: 0; margin: 0 auto; text-align: center;">
        <a href="<?php echo $root; ?>/people" style="color: #FFF;"><i class="fa-solid fa-house" style="font-size: xx-large;"></i></a>
        <hr/>
        <a href="<?php echo $root; ?>/crm" style="color: #FFF;">
          <i class="fa-solid fa-list" style="font-size: xx-large;"></i>
        </a>
        <hr/>
        <a href="<?php echo $root; ?>/jobs" style="color: #FFF;">
          <i class="fa-solid fa-briefcase" style="font-size: xx-large;"></i>
        </a>
      </div>
      <div style="position: absolute; bottom: 2vw; left: 0; right: 0; margin: 0 auto; text-align: center;">
        <a href="<?php echo $root; ?>/logout">
          <i class="fa-solid fa-right-from-bracket" style="font-size: xx-large;"></i>
        </a>
      </div>
    </div>
  </div>

  <div id="main-container" style="padding-left: 7vw; padding-top: 20px; padding-right: 20px; padding-bottom: 50px;">

    <div class="card mb-4 shadow">

      <div class="card-header bg-dark text-white" style="padding-top: 20px;">
        <div style="float: left;">
          <span style="font-weight: 500; font-size: x-large;">Welcome <?php echo explode(' ', $_SESSION['name'])[0]; ?>!</span>
        </div>
        <div style="float: right;" class="d-none d-md-block d-lg-block">
          <p style="font-weight: 500; font-size: large;">
            <?php
            if($_SESSION['account_type'] == 'FREE')
            {
              echo '
              <table style="margin-top: -10px;">
              <tr>
              <td style="padding: 5px;">
              Account type: FREE 
              </td>
              <td style="padding: 5px;">
              <div class="dropdown d-inline">
                <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                  Upgrade
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                  <li><a class="dropdown-item" style="color: #333 !important;" href="'.$stripe_monthly.'">Monthly ($399/month)</a></li>
                  <li><a class="dropdown-item" style="color: #333 !important;" href="'.$stripe_yearly.'">Yearly ($3999/yr 16.5% discount)</a></li>
                </ul>
              </div>
              </td>
              <td style="padding: 5px;"><a href="'.$root.'/logout" class="btn btn-success">Logout</a></td>
              </tr>
              </table>
              ';
            }
            else
            {
              echo 'Account type: PAID ('.$_SESSION['account_type'].')
              <a href="'.$root.'/logout" class="btn btn-success">Logout</a>';
            }
            ?>
          </p>
        </div>
        <div style="clear: both;"></div>
      </div>

      <?php

      if(!isset($_POST['title']))
      {
        $q = $pdo->prepare('SELECT * FROM `jobs` ORDER BY `time` DESC LIMIT 50');
      }
      else
      {

        $title = str_replace(' ', '%', $_POST['title']);
        $firm = str_replace('\'', '\\\'', $_POST['firm']);
        $firm = str_replace('&', '&amp;', $firm);

        if(!empty($_POST['location']))
        {
          $ex = explode(':', $_POST['location']);
          $sql = 'SELECT * FROM `jobs` WHERE `title` LIKE \'%'.$title.'%\' AND (`location` LIKE \'%, '.$ex[0].'%\' OR `location` LIKE \'%'.$ex[1].'%\') AND `company` LIKE \'%'.$firm.'%\' ORDER BY `time` DESC';
          $q = $pdo->prepare($sql);
        }
        else
        {
          $sql = 'SELECT * FROM `jobs` WHERE `title` LIKE \'%'.$title.'%\' AND `company` LIKE \'%'.$firm.'%\' ORDER BY `time` DESC LIMIT 100';
          $q = $pdo->prepare($sql);
        }
      }
      $q->execute();
      ?>

      <div class="card-body" style="background: #DDD;">

        <?php
        $q_ = $pdo->prepare('SELECT DISTINCT(`company`) FROM `jobs`');
        $q_->execute();
        $firmCount = $q_->rowcount();

        $q_ = $pdo->prepare('SELECT COUNT(*) as count FROM `jobs`');
        $q_->execute();
        $count = $q_->fetch(PDO::FETCH_ASSOC)['count'];
        ?>
        <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;"><?php
        echo ' '.$count.' jobs in '.$firmCount.' firms';
        ?></div>

        <?php
        if(!empty($_POST['location']))
        {
          ?>
          <div class="card card-body mb-2" style="background: #EFEFEF; font-weight: bold; font-size: 25px; color: #555;"><?php
          echo $q->rowcount().' results';
          ?></div>
          <?php
        }
        ?>

        <form action="<?php echo $root; ?>/jobs/" method="post">
          <table class="table table-striped table-hover">
            <tr>
              <td>
                <h4>Job title</h4>
                <input type="text" name="title" class="form-control" placeholder="Example: Paralegal">
              </td>
              <td>
                <h4>Job location</h4>
                <select class="form-control" name="location">
                  <option value="" selected>Any</option>
                  <?php
                  $states = json_decode(file_get_contents('states.json'), 1);
                  foreach ($states as $key => $value) {
                    echo '<option value="'.$key.':'.$value.'">'.$value.'</option>';
                  }
                  ?>
                </select>
              </td>
              <td>
                <h4>Firm name</h4>
                <select name="firm" class="form-control">
                  <option value="" selected>Any</option>
                  <?php
                  $companies = $pdo->prepare('SELECT DISTINCT(`company`) FROM `jobs` ORDER BY `company` ASC');
                  $companies->execute();
                  foreach($companies as $row)
                  {
                    echo '<option value="'.$row['company'].'">'.$row['company'].'</option>';
                  }
                  ?>
                </select>
              </td>
              <td style="padding-top: 45px;">
                <button class="btn btn-success">Find Jobs</button>
              </td>
            </tr>
          </table>
        </form>

        <?php
        if(empty($_SEO[2]))
        {
          ?>
          <table class="table table-hover table-striped bg-dark text-white">
            <thead style="background: #333;">
              <tr>
                <th style="width: 80px;">Title</th>
                <th></th>
                <th>Firm</th>
                <th>Location</th>
                <th>Date Posted</th>
                <th>Status</th>
              </tr>
            </thead>
          </table>

          <div style="overflow-y: scroll; height: 700px; margin-top: -20px;">
            <table class="table table-hover table-striped bg-dark text-white">
              <tbody>
                <?php
                foreach($q as $row)
                {
                  if(!empty($row['time']))
                  {
                    $time = date('d-m-Y', $row['time']);
                  }
                  else
                  {
                    $time = '';
                  }
                  echo '
                  <tr>
                  <td style="width: 80px;">
                  <a href="'.$root.'/jobs/'.$row['job_id'].'">
                  <div class="lazyload" style="width: 70px; height: 70px; background-position: center top; background-size: cover; background-image: url('.urldecode($row['image']).')">
                      <div style="text-align: center; margin: 0 auto; padding-top: 35px;">
                      </div>
                  </div>
                  </a>
                  </td>
                  <td class="text-white"><a href="'.$root.'/jobs/'.$row['job_id'].'" style="font-weight: bold;">'.$row['title'].'</a></td>
                  <td class="text-white" style="padding-left: 10px;">'.$row['company'].'</td>
                  <td class="text-white" style="padding-left: 15px;">'.$row['location'].'</td>
                  <td class="text-white" style="padding-left: 20px;">'.$time.'</td>
                  <td class="text-white" style="padding-left: 20px;">'.$row['applicants'].'</td>
                  </tr>
                  ';
                }
                ?>
              </tbody>
            </table>
          </div>
          <?php
        }
        else
        {
          include 'simple_html_dom.php';

          $url = 'https://www.linkedin.com/jobs/view/'.$_SEO[2];

          $q = $pdo->prepare('SELECT * FROM `jobs` WHERE `job_id`=? AND `content`<>\'\' LIMIT 1');
          $q->execute(array($_SEO[2]));

          if($q->rowcount()<1)
          {
            file_put_contents('__jobTemp.txt', $url);
            sleep(10);

            $q = $pdo->prepare('SELECT * FROM `jobs` WHERE `job_id`=? LIMIT 1');
            $q->execute(array($_SEO[2]));
            $html = str_get_html($q->fetch(PDO::FETCH_ASSOC)['content']);

          }
          else
          {
            $html = str_get_html($q->fetch(PDO::FETCH_ASSOC)['content']);
          }

          $apply = 'https://'.get_string_between($html, '"companyApplyUrl":"https://', '"');

          ?>
          <style type="text/css">
            .tvm__text--neutral
            {
              font-size: 20px;
              color: #CCC;
            }
          </style>
          <div class="bg-dark p-4">
            <h1><?php echo $html->find('h1', 0)->plaintext; ?> <a href="<?php echo $apply; ?>" class="btn btn-success btn-lg" target="_blank">Apply now</a></h1>
            <hr/>
            <h4><?php echo @$html->find('.job-details-jobs-unified-top-card__primary-description', 0)->innertext; ?></h4>
            <br/>
            <div style="background: #444; padding: 20px;"><?php
            echo $html->find('.jobs-description__content', 0)->innertext;
            ?></div>
          </div>
          <?php
        }
        ?>

      </div>
      
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){
      <?php 
      foreach ($_POST as $key => $value) {
        echo '$(\'[name="'.$key.'"]\').val(\''.$value.'\'); ';
      }
      ?>
    });
  </script>
</body>
</html>