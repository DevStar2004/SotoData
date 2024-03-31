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
  <title>SotoData - CRM</title>
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

      <div class="card-body" style="background: #DDD;">
        <?php
        if(isset($_GET['delete']))
        {
          $q = $pdo->prepare('DELETE FROM `crm` WHERE `member_id`=? AND `id`=?');
          $q->execute(array($_SESSION['id'], $_GET['delete']));
        }

        if(empty($_GET['manage']))
        {
          ?>


          <?php
          $q = $pdo->prepare('SELECT * FROM `crm` WHERE `member_id`=? AND `type`=\'status\' AND `content` LIKE \'%'.@$_GET['status'].'%\'');
          $q->execute(array($_SESSION['id']));
          if($q->rowcount()>0)
          {
            $people = $q->fetchAll();
          }
          ?>

          <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;">CRM</div>
          <div style="float: right;">
            <a href="<?php echo $root; ?>/crm/?manage=1" class="btn btn-dark text-white">Manage</a>
          </div>
          <div style="clear: both;"></div>

          <?php
          if(empty(@$_GET['status']))
          {
            echo '<a class="btn btn-dark btn-sm text-white" style="margin: 2.5px;" href="'.$root.'/crm/?status=">All</a>';
          }
          else
          {
            echo '<a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="'.$root.'/crm/?status=">All</a>';
          }
          ?>

          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Contacted">Contacted</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Follow Up">Follow Up</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Interested">Interested</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Interviewing">Interviewing</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Offer made">Offer made</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Placed">Placed</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Submitted">Submitted</a>
          <a class="btn btn-light btn-sm text-dark" style="margin: 2.5px;" href="<?php echo $root; ?>/crm/?status=Do not contact">Do not contact</a>

          <?php

          if(!empty($_GET['status']))
          {
            echo '<h2 class="mt-2 text-black">'.$_GET['status'].' - '.$q->rowcount().'</h2>';
          }
          else
          {
            echo '<h2 class="mt-2 text-black">All - '.$q->rowcount().'</h2>';
          }
          ?>

          <div style="min-height: 500px; margin-top: 10px; background: #DDDDDD !important;" id="results">
              <table class="table table-hover table-striped bg-dark text-white" id="table">

                  <tbody>
                    <?php
                    if(!empty($people))
                    {
                      foreach ($people as $item) {
                        $q = $pdo->prepare('SELECT * FROM `people` WHERE `names`=?');
                        $q->execute(array($item['person_name']));
                        $row = $q->fetch(PDO::FETCH_ASSOC);

                        if(strpos($row['LinkedIn'], 'linkedin.com/in') === false)
                        {
                          $row['LinkedIn'] = '';
                        }

                        if(strpos($row['photo_headshot'], 'http') === false)
                        {
                          $row['photo_headshot'] = $root.'/img/nophoto.png';
                        }
                        ?>
                        <tr>
                        <td style="width: 350px !important;">
                          <table style="width: 100%;">
                            <tr>
                              <td style="width: 100px; color: #FFF;" id="photo_headshot">
                                <div class="lazyload" style="width: 100px; height: 100px; background-position: top center; background-size: cover;" data-src="<?php echo $root.'/thumb.php?url='.urlencode($row['photo_headshot']); ?>">
                                    <div style="text-align: center; margin: 0 auto; padding-top: 35px;">
                                      <div class="spinner-grow spinner text-white text-center" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                      </div>
                                    </div>
                                </div>
                              </td>
                              <td style="padding-left: 20px; color: #FFF;">
                                <div style="font-size: large; display: block; font-weight: 700; cursor: pointer;" attr-person="<?php echo $row['id']; ?>" attr-linkedIn="<?php echo $row['LinkedIn']; ?>">
                                  <?php echo implode(' ', json_decode($row['names'], 1)); ?>
                                </div>
                                <span class="practice-area"><?php echo @json_decode($row['positions'], 1)[0]; ?></span>
                                <div><?php echo @json_decode($row['phone_numbers'], 1)[0]; ?></div>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td class="hide-on-small pt-4" style="color: #FFF;" filter-type="firmName" filter-value="<?php echo $row['firmName']; ?>">
                          <span style="cursor: pointer;"><?php echo $row['firmName']; ?></span>
                        </td>
                        <td class="hide-on-small pt-4" style="color: #FFF;">
                          <span style="cursor: pointer;"><?php echo $row['primaryAddress']; ?></span>
                        </td>
                        <td class="hide-on-small pt-4" style="color: #FFF;" filter-type="practice_areas" filter-value="<?php echo @json_decode($row['practice_areas'], 1)[0]; ?>">
                          <span style="cursor: pointer;"><?php echo @json_decode($row['practice_areas'], 1)[0]; ?></span>
                        </td>
                        <td class="hide-on-small pt-4" style="color: #FFF;">
                          <?php
                          $q = $pdo->prepare('SELECT * FROM `crm` WHERE `person_id`=? AND `member_id`=? AND `type`=\'status\'');
                          $q->execute(array($row['id'], $_SESSION['id']));
                          $data = $q->fetch(PDO::FETCH_ASSOC);
                          echo $data['content'];
                          ?>
                        </td>
                        </tr>
                        <?php

                      }
                    }
                    ?>
                  </tbody>
              </table>

          </div>

          <div id="person" style="display: none;"></div>

          <?php
        }
        else
        {
          ?>
          <div class="card card-body mb-2" style="background: #118E9B; font-weight: bold; font-size: 25px;">CRM</div>
          <div class="p-2">
            <table class="table table-hover table-striped">
              <thead class="bg-dark text-white">
                <tr>
                  <th>Attorney</th>
                  <th>Type</th>
                  <th>Content</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $q = $pdo->prepare('SELECT * FROM `crm` WHERE `member_id`=? ORDER BY `person_name` ASC');
                $q->execute(array($_SESSION['id']));
                foreach($q as $row)
                {
                  echo '
                  <tr>
                    <td>'.implode(' ', json_decode($row['person_name'], 1)).'</td>
                    <td>'.ucwords($row['type']).'</td>
                    <td>'.$row['content'].'</td>
                    <td><a href="'.$root.'/crm/?manage=1&delete='.$row['id'].'" class="btn btn-sm btn-dark">Delete</a></td>
                  </tr>
                  ';
                }
                ?>
              </tbody>
            </table>
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

      $('.btn-light').each(function(i, obj) {
        console.log($(this).text());
        var status = '<?php echo @$_GET['status']; ?>';
        if($(this).text() == status)
        {
          $(this).removeClass('btn-light').removeClass('text-dark');
          $(this).addClass('btn-dark').addClass('text-white');
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

      $('body').on('click', '[attr-person]', function() {

        var id = $(this).attr('attr-person');
        var linkedIn = $(this).attr('attr-linkedIn');

        $.post('<?php echo $root; ?>/person.php', { id: id })
        .done(function(data){

          $.get('<?php echo $root; ?>/_linkedIn.php?url='+linkedIn)
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

      $('body').on('click', '[attr-goto]', function() {

        var id = $(this).attr('attr-goto');
        $('#person').html('').fadeIn();
        $('#results').show();
        $('html, body').animate({
          scrollTop: $('[attr-person="'+id+'"]').offset().top
        }, 'fast');

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

      lazyload();

    });
  </script>
</body>
</html>