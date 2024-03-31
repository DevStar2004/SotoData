<?php
if(empty($_SESSION['name']))
{
  header('Location: '.$root.'/logout');
  exit();
}
?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SotoData - Home</title>
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

    <input type="hidden" name="page" value="0" id="page">

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
                <ul class="dropdown-menu" id="upgrade" aria-labelledby="dropdownMenuButton1">
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
      <div class="card-body" style="background: #DDD;">

        <div class="row">

          <?php
          $q = $pdo->prepare('SELECT DISTINCT(`firmName`) FROM `people`');
          $q->execute();
          $firmCount = $q->rowcount();
          ?>

          <div class="col-sm-4">
            <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;">
              <span id="attorney_count">
                <!-- <?php echo file_get_contents($root.'/_count.php').' attorneys'; ?> -->
              </span>
            </div>
          </div>

          <?php
          $q = $pdo->prepare('SELECT COUNT(*) as count FROM `jobs`');
          $q->execute();
          $count = $q->fetch(PDO::FETCH_ASSOC)['count'];
          ?>
          <div class="col-sm-4">
            <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;">
              <?php echo number_format($count); ?> active jobs
            </div>
          </div>

          <div class="col-sm-4">
            <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;">
              <span id="attorney_count"><?php echo $firmCount.' firms'; ?></span>
            </div>
          </div>

          <div class="col-md-6 d-none d-md-block mt-4">
            <div class="card card-header" style="background: #118E9B; font-weight: bold;">
              Latest news
            </div>
            <div class="card card-body" style="max-height: 200px; color: #222 !important; overflow-y: scroll;">
              <?php
              $feed = implode(file('https://abovethelaw.com/feed/'));
              $xml = simplexml_load_string($feed);
              $json = json_encode($xml);
              $array = json_decode($json, TRUE);
              foreach($array['channel']['item'] as $item)
              {
                echo '<p class="text-black" style="border-bottom: 1px inset #CCC;"><a href="'.$item['link'].'" class="text-black" target="_blank">'.$item['title'].'</a></p>';
              }
              ?>
            </div>
          </div>

          <div class="col-md-6 d-none d-md-block mt-4">
            <div class="card card-header" style="background: #118E9B; font-weight: bold;">
              Trending now
            </div>
            <div class="card card-body" style="max-height: 200px; color: #222 !important; overflow-y: scroll;">
              <?php
              $feed = implode(file('http://feeds.feedburner.com/abajournal/topstories'));
              $xml = simplexml_load_string($feed);
              $json = json_encode($xml);
              $array = json_decode($json, TRUE);
              foreach($array['channel']['item'] as $item)
              {
                echo '<p class="text-black" style="border-bottom: 1px inset #CCC;"><a href="'.$item['link'].'" class="text-black" target="_blank">'.$item['title'].'</a></p>';
              }
              ?>
            </div>
          </div>

          <div class="col-md-6 d-none d-md-block mt-4">
            <div class="card card-header" style="background: #118E9B; font-weight: bold;">
               Jobs by State
            </div>
            <div class="card card-body" style="max-height: 200px; color: #222 !important; overflow-y: scroll;">
              Coming soon
            </div>
          </div>

          <div class="col-md-6 d-none d-md-block mt-4">
            <div class="card card-header" style="background: #118E9B; font-weight: bold;">
              Recently Updated Firms
            </div>
            <div class="card card-body" style="max-height: 200px; color: #222 !important; overflow-y: scroll;">
              Coming soon
            </div>
          </div>

        </div>

      </div>
    </div>

    <div class="card mb-4 shadow" id="search_">

      <div class="card-header bg-dark text-white">
        <span style="font-weight: 500; font-size: x-large;">Attorney Search</span>
      </div>
      <div class="card-body" style="background: #DDD;" id="search_master">
        <?php
        if(empty($_GET['searchID']))
        {
          ?>


          <div style="float: right;">
            <button class="btn btn-light text-black btn-sm mb-2 mr-2" type="button" id="save_search" value="1" style="border: 1px solid #555;" data-bs-toggle="modal" data-bs-target="#saveModal">Save Search</button>
            <button class="btn btn-light text-black btn-sm mb-2 mr-2" type="button" data-bs-toggle="modal" data-bs-target="#savedModal" style="border: 1px solid #555;">Saved Searches</button>
          </div>
          <div style="clear: both;"></div>

          <div class="row">
            <style type="text/css">
              .tooltip-inner {
                  max-width: 500px;
                  /* If max-width does not work, try using width instead */
                  width: 500px;
                  padding-bottom: 20px;
              }
            </style>
            <div class="col-sm-12" style="padding-right: 40px;">
              <table style="width: 100%">
                <tr>
                  <td style="padding-right: 20px;">
                    <input class="form-control" type="text" id="keywords" placeholder="Keywords" autocomplete="nope">
                  </td>
                  <td style="width: 20px;">
                    <button type="button" class="btn btn-secondary" style="margin-top: -10px; border-radius: 0 !important;" data-bs-toggle="tooltip" data-bs-html="true" title="<?php 
                    $str = '
                    AND: Narrow results - e.g., keyword1 AND keyword2
                    OR: Broaden search - e.g., keyword1 OR keyword2
                    Wildcard: Use * for partial matches - e.g., keyword*
                    Exclude: Minus sign - e.g., apple -pie
                    Include: Plus sign - e.g., +important +document
                    Exact Match: Enclose in double quotes - e.g., &ldquo;Exact keyword&ldquo;
                    ';
                    $str = trim(str_replace("\n", '<br/>', $str));
                    echo $str;
                    ?>">?
                    </button>
                  </td>
                </tr>
              </table>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">

              <div style="position: relative;">
                <table style="width: 100%;">
                  <tr>
                    <td>
                      <input class="form-control" type="text" id="names" placeholder="Name">
                    </td>
                    <td>
                      <select class="form-control" id="positions" style="color: #777 !important;">
                        <option value="" selected>Title </option>
                        <?php
                        $titles = explode("\n", file_get_contents('config/titles.txt'));
                        foreach($titles as $title)
                        {
                          echo '<option value="'.trim($title).'">'.trim($title).'</option>';
                        }
                        ?>
                      </select>
                    </td>
                  </tr>
                </table>
                <div style="position: absolute; bottom: -200px; height: 250px; width: 100%; background: #FFF; border-left: 1px solid #333; border-bottom: 1px solid #333; border-right: 1px solid #333; color: #333; padding: 10px; overflow-y: scroll; display: none;" id="autocomplete"></div>
              </div>

              <select id="practice_areas" class="form-control" style="color: #777 !important;">
              <option value="" disabled selected>Practice area </option>
              <?php
              $q = $pdo->prepare("SELECT * FROM `categories` ORDER BY `name` ASC");
              $q->execute();
              foreach($q as $row)
              {
                echo '<option value="'.$row['name'].'" style="font-weight: bold;">'.$row['name'].'</option>';

                $sq = $pdo->prepare("SELECT * FROM `sub_categories` WHERE `parent_id`=? ORDER BY `name` ASC");
                $sq->execute(array($row['id']));
                foreach($sq as $cat)
                {
                  echo '<option value="'.$row['name'].'-'.$cat['name'].'">&nbsp;&nbsp; '.$cat['name'].'</option>';
                }

              }
              ?>
              </select>

              <select class="form-control" id="firmName" style="color: #777 !important;">
              <option value="" disabled selected>Firm name (<?php echo $firmCount.')'; ?> </option>
              <?php
              $q = $pdo->prepare('SELECT DISTINCT(firmName) FROM `people` ORDER BY `firmName` ASC');
              $q->execute();
              foreach ($q as $row) {
                echo '<option value="'.$row['firmName'].'">'.$row['firmName'].'</option>';
              }
              ?>
              </select>

              <?php
              $languages = array(
                'Spanish',
                'Mandarin',
                'Arabic',
                'Hindi',
                'Russian',
              );
              ?>

              <select class="form-control" id="language" style="color: #777 !important;">
                 <option value="" selected>Additional languages</option>
                 <?php
                 foreach($languages as $language)
                 {
                  echo '<option value="'.$language.'">'.$language.'</option>';
                 }
                 ?>
              </select>

            </div>

            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">

              <input class="form-control" type="text" id="undergraduate_school" placeholder="Undergraduate School ">

              <select class="form-control" id="degree" style="color: #777 !important;">
                <option value="" selected>Degrees </option>
                 <option value="BS">BS</option>
                 <option value="BA">BA</option>
                 <option value="MA">MA</option>
                 <option value="MS">MS</option>
                 <option value="PHD">PHD</option>
              </select>
              
              <div class="row">
                <div class="col-sm-6" style="padding-right: 0; margin: 0;">
                  <select class="form-control" id="jd_from" style="color: #777 !important;">
                    <option value="" selected disabled>JD from</option>
                    <?php
                    for ($i=1940; $i < date('Y'); $i++) { 
                      echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="col-sm-6" style="padding-left: 0 !important; margin: 0;">
                  <select class="form-control" id="jd_to" style="color: #777 !important;">
                    <option value="" selected disabled>JD to</option>
                    <?php
                    for ($i=1940; $i < date('Y'); $i++) { 
                      echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-check" style="background: #FFF; margin-top: 2.5px; padding: 5px; border: 1px solid #ced4da; border-radius: 0.375rem; margin-bottom: 10px !important;">
                <input class="form-check-input" type="checkbox" value="" id="include_missing_jd" style="margin-left: 10px;" name="include_missing_jd">
                <label class="form-check-label" for="include_missing_jd" style="color: #555; padding-left: 10px;">
                  Include attorneys with no JD
                </label>
              </div>

            </div>

            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">

              <select class="form-control" id="country" style="color: #777 !important;">
                  <option value="" selected>Country </option>
                  <option value="233">United States</option>
                  <?php
                  $q = $pdo->prepare('SELECT * FROM `countries` ORDER BY `name` ASC');
                  $q->execute();
                  foreach($q as $row)
                  {
                    if($row['id'] != 233 && $row['name'] != '233')
                    {
                      echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                    }
                  }
                  ?>
              </select>

              <select class="form-control" id="state" style="color: #777 !important; display: none;"></select>

              <select class="form-control" id="city" style="color: #777 !important; display: none;"></select>

              <input class="form-control" type="text" id="law_school" placeholder="Law school">

              <select class="form-control" id="bar_association" style="color: #777 !important;">
                  <option value="" selected>State bar association </option>
                  <?php
                  $q = $pdo->prepare('SELECT * FROM `states` WHERE `country_id`=233 ORDER BY `name` ASC');
                  $q->execute();
                  foreach($q as $row)
                  {
                    echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
                  }
                  ?>
              </select>

              <select class="form-control" id="honors" style="color: #777 !important;">
                  <option value="" selected="">Distinctions and Achievements</option>
                  <option value="Cum Lauda">Cum Lauda</option>
                  <option value="Magna Cum Lauda">Magna Cum Lauda</option>
                  <option value="Summa Cum Lauda">Summa Cum Lauda</option>
                  <option value="Honors">Honors</option>
                  <option value="High Honors">High Honors</option>
                  <option value="Law Review">Law Review</option>
                  <option value="Order of the Coif">Order of the Coif</option>
              </select>

            </div>

          </div>

          <div style="float: left; padding-left: 10px;">
            <div id="search_filters" style="display: inline-block; max-width: 700px;"></div>
          </div>

          <div style="float: right;">
            <button class="btn btn-success btn mb-2 mr-2" type="button" id="doSearch" style="width: 200px;">Search</button>
            <a href="<?php echo $root; ?>/#search_" class="btn btn-light text-black btn mb-2 mr-2" style="width: 200px; border: 1px solid #555;">Clear All Filters</a>
          </div>

          <div style="clear: both;"></div>
          
          <?php
        }
        else
        {
          $q = $pdo->prepare('SELECT * FROM `saved_searches` WHERE `member_id`=? AND `id`=?');
          $q->execute(array($_SESSION['id'], $_GET['searchID']));
          $search = $q->fetch(PDO::FETCH_ASSOC);
          echo $search['content'];
        }
        ?>
      </div>
    </div>

    <?php
    $search_settings = json_decode($_SESSION['search_settings'], 1);
    ?>

    <div class="accordion" id="search_settings" style="margin-bottom: 10px; +">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button collapsed" style="background: #EFEFEF;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Search settings
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#search_settings">
          <div class="accordion-body">

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="show_profile_image"<?php if($search_settings['show_profile_image'] == true) { echo ' checked'; } ?>>
              <label class="form-check-label" for="show_profile_image">
                Show profile image
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="show_firm_name"<?php if($search_settings['show_firm_name'] == true) { echo ' checked'; } ?>>
              <label class="form-check-label" for="show_firm_name">
                Show firm name
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="show_primary_location"<?php if($search_settings['show_primary_location'] == true) { echo ' checked'; } ?>>
              <label class="form-check-label" for="show_primary_location">
                Show primary location
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="show_practice_area"<?php if($search_settings['show_practice_area'] == true) { echo ' checked'; } ?>>
              <label class="form-check-label" for="show_practice_area">
                Show practice area
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="show_actions"<?php if($search_settings['show_actions'] == true) { echo ' checked'; } ?>>
              <label class="form-check-label" for="show_actions">
                Show actions
              </label>
            </div>
            <hr/>

            <button class="btn btn-dark" id="save_search_settings">Save</button>

          </div>

        </div>
      </div>
    </div>

  <div class="card card-body bg-white text-dark shadow mb-2 p-4" id="results" style="min-height: 500px; background: #DDDDDD !important;">
    <div style="margin: 0 auto; text-align: center; margin-top: 50px;">
      <div class="spinner-border text-dark" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>

  <div id="person" style="display: none;"></div>

  <!-- Modal -->
  <div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-black" id="saveModalLabel">Save Search</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-black">
          <input type="text" class="form-control" id="save_search_name" placeholder="Title">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="save_search_do">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <div class="dropdown" style="display: none;" id="with_selected">
    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
      With selected
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
      <li><a class="dropdown-item" href="#" style="color: #333 !important;">Action</a></li>
      <li><a class="dropdown-item" href="#" style="color: #333 !important;">Another action</a></li>
      <li><a class="dropdown-item" href="#" style="color: #333 !important;">Something else here</a></li>
    </ul>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="savedModal" tabindex="-1" aria-labelledby="savedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-black" id="savedModalLabel">Saved Searches</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-black">
          <?php
          $q = $pdo->prepare('SELECT * FROM `saved_searches` WHERE `member_id`=?');
          $q->execute(array($_SESSION['id']));
          foreach($q as $row)
          {
            echo '<a href="'.$root.'/people/?searchID='.$row['id'].'" class="btn btn-light text-dark m-2" style="border: 2px solid #333;">'.$row['title'].'</a>';
          }
          ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){

      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });

      $('body').on('click', '#save_search_settings', function() {
        $(this).text('Please wait...');
        $.post('<?php echo $root; ?>/_ajax.php', {
          show_profile_image: $('#show_profile_image').is(':checked'),
          show_firm_name: $('#show_firm_name').is(':checked'),
          show_primary_location: $('#show_primary_location').is(':checked'),
          show_practice_area: $('#show_practice_area').is(':checked'),
          show_actions: $('#show_actions').is(':checked'),
        })
        .done(function(){
          $('#save_search_settings').text('Save');
          $('[class="accordion-button"]').click();
          $('#doSearch').click();
        });
      });

      var checked = [];

      setInterval(function(){
        checked = [];
        $('body').find('[attr-checked]').each(function(){
          if($(this).is(':checked'))
          {
            checked.push($(this).attr('attr-checked'));
          }
        });
        console.log(checked);
        if(checked.length>0)
        {
          $('#with_selected').attr('style', 'position: fixed; bottom: 20px; right: 100px;');
        }
        else
        {
          $('#with_selected').attr('style', 'display: none;');
        }
      }, 1000);

      $('body').on('change', '#per_page', function() {
        $.post('<?php echo $root; ?>/_ajax.php', { per_page: $(this).val() })
        .done(function(){
          $('#doSearch').click();
        });
      });

      $('body').on('click', '#close_autocomplete', function() {
        $('#autocomplete').hide();
      });

      $('body').on('click', '[attr-customFilter]', function() {
        $('#keywords').val('');
        $('#autocomplete').hide();
      });

      $('body').on('change', '#select_all', function() {
        if($(this).is(':checked'))
        {
          $('[attr-checked]').each(function(){
            $(this).prop('checked', true);
          });
        }
        else
        {
          $('[attr-checked]').each(function(){
            $(this).removeAttr('checked');
            $(this).prop('checked', false);
          });
        }
      });

      function lazyload()
      {
        $('.lazyload').each(function() {

            var lazy = $(this);
            var src = lazy.attr('data-src');

            $('<img>').attr('src', src).load(function(){
                lazy.find('.spinner').remove();
                lazy.css('background-image', 'url("'+src+'")');
            });
        
        });
      }

      $('#results').html('<h4 style="text-align: center; margin-top: 10%;">Please input your search parameters and press the search button to begin.</h4>');

      $('body').on('click', '[filter-type]', function() {

        var filterType = $(this).attr('filter-type');
        var filterValue = $(this).attr('filter-value');

        switch(filterType)
        {

          case 'primaryAddress':

            $('#search_filters').html('<span class="bg-dark p-2 selectedF m-1" value="'+filterValue+'" datatype="location">'+filterValue+' x</span>');
            $('#doSearch').click();

            $('html, body').animate({
              scrollTop: $("#results").offset().top
            }, 'fast');

            break;

          case 'firmName':

            $('#search_filters').html('<span class="bg-dark p-2 selectedF m-1" value="'+filterValue+'" datatype="firmName">'+filterValue+' x</span>');
            $('#doSearch').click();

            $('html, body').animate({
              scrollTop: $("#results").offset().top
            }, 'fast');

            break;

          case 'practice_areas':

            $('#search_filters').html('<span class="bg-dark p-2 selectedF m-1" value="'+filterValue+'" datatype="practice_areas">'+filterValue+' x</span>');
            $('#doSearch').click();

            $('html, body').animate({
              scrollTop: $("#results").offset().top
            }, 'fast');

            break;

        }

      });

      /*
      setInterval(function(){
        $.post('<?php echo $root; ?>/_count.php')
        .done(function(data){
          $('#attorney_count').html(data+' attorneys');
        });
      }, 1000);
      */

      history.pushState(null, document.title, location.href);
      window.addEventListener('popstate', function (event)
      {

        history.pushState(null, document.title, location.href);
        $('#person').html('').hide();
        $('#results').fadeIn();

        $('html, body').animate({
          scrollTop: $("#results").offset().top
        }, 'fast');

      });

      $('body').on('click', '[attr-goto]', function() {

        var id = $(this).attr('attr-goto');
        $('#person').html('').fadeIn();
        $('#results').show();
        $('html, body').animate({
          scrollTop: $('[attr-person="'+id+'"]').offset().top
        }, 'fast');

      });

      $('body').on('click', '[attr-person]', function() {

        var id = $(this).attr('attr-person');
        var linkedIn = $(this).attr('attr-linkedIn');

        $.post('<?php echo $root; ?>/person.php', { id: id })
        .done(function(data){

          $.get('<?php echo $root; ?>/_linkedIn.php?url='+linkedIn+'&id='+id)
          .done(function(res){
            if(res.length>5)
            {
              $('#activity_tab').html(res);
            }
          });

          $('#results').hide();
          $('#person').html(data).fadeIn();
          $('html, body').animate({
            scrollTop: $("#person").offset().top
          }, 'fast');

        });

      });

      $('body').on('click', '[attr-person]', function() {

        var id = $(this).attr('attr-person');
        $.post('<?php echo $root; ?>/person.php', { id: id })
        .done(function(data){

          $('#results').hide();
          $('#person').html(data).fadeIn();

        });

      });

      $('body').on('click', '[attr-readmore]', function() {
        $(this).parent().parent().find('[attr-smallbio]').hide();
        $(this).parent().parent().find('[attr-fullbio]').show();
      });

      $('body').on('click', '[attr-readless]', function() {
        $(this).parent().parent().find('[attr-smallbio]').show();
        $(this).parent().parent().find('[attr-fullbio]').hide();
      });

      // s-f
      $('#doSearch').click(function() {
        $('#person').html('').hide();
        $('#results').show();

        $('#results').html('\
          <div style="margin: 0 auto; text-align: center; margin-top: 50px;">\
            <div class="spinner-border text-dark" role="status">\
              <span class="visually-hidden">Loading...</span>\
            </div>\
          </div>\
          ');

        var data = {
          'keywords':$('#keywords').val(),
          'names':$('#names').val(),
          'law_school':$('#law_school').val(),
          'country':$('#country').val(),
          'state':$('#state').val(),
          'city':$('#city').val(),
          'jd_from':$('#jd_from').val(),
          'jd_to':$('#jd_to').val(),
          'include_missing_jd':$('#include_missing_jd').is(':checked'),
          // 'undergraduate_school':$('#undergraduate_school').val(),
          'page':$('#page').val(),
        };

        data['firmName'] = [];
        $('body').find('[datatype="firmName"]').each(function(){
          var value = $(this).attr('value');
          data['firmName'].push(value);
        });

        data['practice_areas'] = [];
        $('body').find('[datatype="practice_areas"]').each(function(){
          var value = $(this).attr('value');
          data['practice_areas'].push(value);
        });

        data['positions'] = [];
        $('body').find('[datatype="positions"]').each(function(){
          var value = $(this).attr('value');
          data['positions'].push(value);
        });

        data['location'] = [];
        $('body').find('[datatype="location"]').each(function(){
          var value = $(this).attr('value');
          data['location'].push(value);
        });

        data['bar_association'] = [];
        $('body').find('[datatype="bar_association"]').each(function(){
          var value = $(this).attr('value');
          data['bar_association'].push(value);
        });

        data['honors'] = [];
        $('body').find('[datatype="honors"]').each(function(){
          var value = $(this).attr('value');
          data['honors'].push(value);
        });

        data['degree'] = [];
        $('body').find('[datatype="degree"]').each(function(){
          var value = $(this).attr('value');
          data['degree'].push(value);
        });

        data['language'] = [];
        $('body').find('[datatype="language"]').each(function(){
          var value = $(this).attr('value');
          data['language'].push(value);
        });

        $.post('<?php echo $root; ?>/_ajax.php', { data: data })
        .done(function(data){

          $('#results').html(data);

          $('html, body').animate({
            scrollTop: $("#results").offset().top
          }, 'fast');

          lazyload();
        });

      });

      $('body').on('click', '.selectedF', function() {

        $('#page').val(0);

        var type = $(this).attr('dataType');
        var value = $(this).attr('value');

        $(this).remove();
        //$('#'+type).append('<option value="'+value+'">'+value+'</option>');

        if(type == 'country')
        {
          $('#state, #city').hide();
          $('#country').fadeIn().prop('selectedIndex', 0);;
          $('[datatype="state"],[datatype="city"]').remove();
        }

        if(type == 'state')
        {
          $('#city').hide();
          $('#state').fadeIn().prop('selectedIndex', 0);
          $('[datatype="city"]').remove();
        }

      });

      $('#firmName').change(function(){
        var value = $(this).val();
        //$(this).find('option[value="'+value+'"]').remove();
        $(this).find('option[value=""]').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="firmName">Law Firm: '+value+' x</span>');
      });

      $('#practice_areas').change(function(){
        var value = $(this).val();
        //$(this).find('option[value="'+value+'"]').remove();
        $(this).find('option[value=""]').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="practice_areas">Practice: '+value+' x</span>');
      });

      $('#positions').change(function(){
        var value = $(this).val();
        //$(this).find('option[value="'+value+'"]').remove();
        $(this).find('option[value=""]').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="positions">Title: '+value+' x</span>');
      });

      $('#country').change(function(){

        $(this).hide();

        $('#state').html('');

        var value = $(this).val();
        var name = $('#country option:selected').text();
        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="country">Country: '+name+' x</span>');

        $.get('<?php echo $root; ?>/_states.php', { country: value })
        .done(function(data){

          var states = JSON.parse(data);
          $('#state').append('<option value="" selected>Select state (optional)</option>');

          states.forEach(function(value){
            $('#state').append('<option value="'+value.id+'">'+value.name+'</option>');
          });
          $('#state').fadeIn();
        });

      });

      $('#state').change(function(){

        $(this).hide();

        $('#city').html('');

        var value = $(this).val();
        var name = $('#state option:selected').text();
        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="state">City: '+name+' x</span>');

        $.get('<?php echo $root; ?>/_cities.php', { city: value })
        .done(function(data){

          var cities = JSON.parse(data);
          $('#city').append('<option value="" selected>Select city (optional)</option>');

          cities.forEach(function(value){
            $('#city').append('<option value="'+value.id+'">'+value.name+'</option>');
          });
          $('#city').fadeIn();
        });

      });

      $('#city').change(function(){

        var value = $(this).val();
        var name = $('#city option:selected').text();

        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="city">City: '+name+' x</span>');

      });

      $('#bar_association').change(function(){

        var value = $(this).val();

        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="bar_association">Bar association: '+value+' x</span>');

        $('#bar_association option:selected').removeAttr("selected");

      });

      $('#honors').change(function(){

        var value = $(this).val();

        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="honors">Honor: '+value+' x</span>');

        $('#honors option:selected').removeAttr("selected");

      });

      $('#degree').change(function(){

        var value = $(this).val();

        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="degree">Degree: '+value+' x</span>');

        $('#degree option:selected').removeAttr("selected");

      });

      $('#language').change(function(){

        var value = $(this).val();

        $(this).find('option[value=""] selected').prop('selected', true);
        $('#search_filters').append('<span class="bg-dark p-2 selectedF m-1" value="'+value+'" dataType="language">Language: '+value+' x</span>');

        $('#language option:selected').removeAttr("selected");

      });

      $('body').on('click', '[attr-page]', function() {
        var page = $(this).attr('attr-page');
        $('#page').val(page);
        $('#doSearch').click();
      });

      var add_action = '';
      var add_id = '';

      $('body').on('click', '[attr-add]', function() {

        add_action = $(this).attr('attr-add');
        add_id = $(this).attr('attr-add-value');

        $('#add-action').text('Add '+add_action);
        $('#add-box').fadeIn();

      });

      $('body').on('click', '#add-cancel', function() {
        $('#add-box').hide();
      });

      $('body').on('click', '[attr-setstatus]', function() {
        var member_id = $(this).attr('attr-setstatus');

        $.post('<?php echo $root; ?>/_crm_actions.php?action=status&person='+member_id+'&content='+$(this).text())
        .done(function(){

          $.post('<?php echo $root; ?>/person.php', { id: member_id })
          .done(function(data){

            $('#results').hide();
            $('#person').html(data).fadeIn();
            $('html, body').animate({
              scrollTop: $("#person").offset().top
            }, 'fast');

          });

        });

      });

      

      $('body').on('click', '#add-save', function() {

        $(this).text('Please wait...');
        $.get('_crm_actions.php', { action: add_action, person: add_id, content: $('#add-content').val() })

        .done(function(){

          $.post('<?php echo $root; ?>/person.php', { id: add_id })
          .done(function(data){

            $('#results').hide();
            $('#person').html(data).fadeIn();
            $('html, body').animate({
              scrollTop: $("#person").offset().top
            }, 'fast');

          });

        });

      });

      $('body').on('change', '#pageSelect', function() {
        var page = $(this).val()-1;
        $('#page').val(page);
        $('#doSearch').click();
      });

      $(document).on('keypress',function(e) {
          if(e.which == 13) {
              $('#doSearch').click();
          }
      });

      $('#save_search_do').click(function(){
        var name = $('#save_search_name').val();
        var content = $('#search_master').html();
        if(name.length>0)
        {
          $(this).text('Please wait...');
          $.post('<?php echo $root; ?>/_save_search.php', { title: name, content: content, member_id: <?php echo $_SESSION['id']; ?> })
          .done(function(){
            $('#save_search_do').text('Save');
            $('#saveModal').modal('toggle');
          });
        }
        else
        {
          alert('Enter a name for your search.');
        }
      });

      $('#keywords').focus();

      $('#keywords').keyup(function(){

        $('#autocomplete').html('<div class="d-flex align-items-center"><strong>Loading...</strong><div class="spinner-border ms-auto" role="status" aria-hidden="true"></div></div>');

        $('#autocomplete').fadeIn();

        $.get('<?php echo $root; ?>/_autocomplete.php', { keyword: $(this).val() })
        .done(function(data){
          if(data == 0) { $('#autocomplete').hide(); }
          $('#autocomplete').html(data);
        });

      });
      
    });
  </script>
</body>
</html>