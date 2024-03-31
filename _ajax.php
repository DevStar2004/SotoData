<?php
include 'config.php';

if(empty($_SESSION['account_type']))
{
  echo '<script type="text/javascript">window.location.href="'.$root.'/login";</script>';
  exit();
}

if(!empty($_POST['show_profile_image']))
{
  $q = $pdo->prepare('UPDATE `customers` SET `search_settings`=? WHERE `id`=?');
  $q->execute(array(json_encode($_POST), $_SESSION['id']));
  $_SESSION['search_settings'] = json_encode($_POST);
}

if(!empty($_POST['per_page']))
{
  $_SESSION['per_page'] = $_POST['per_page'];
  exit();
}

if(empty($_SESSION['per_page']))
{
  $per_page = 10;
}
else
{
  $per_page = $_SESSION['per_page'];
}

$offset = 0;

if(isset($_POST['data'])){

  $data = $_POST['data'];

  if($data['page']>0)
  {
    $offset = ($data['page'])*$per_page;
  }

  if(!empty($data['keywords']))
  {
    $sql = 'SELECT * FROM `people` WHERE MATCH(`names`, `email`, `fullAddress`, `primaryAddress`, `education`, `practice_areas`, `positions`, `languages`, `description`, `firmName`, `law_school`) AGAINST(\''.$data['keywords'].'\' IN BOOLEAN MODE)';
  }
  else
  {
    $sql = 'SELECT * FROM `people` WHERE `id`<>0';
  }

  if(!empty($data['names']))
  {
    $sql = 'SELECT * FROM `people` WHERE MATCH(`names`) AGAINST(\'+'.str_replace(' ', ' +', $data['names']).'\' IN BOOLEAN MODE)';
  }

  if(!empty($data['country']))
  {

    $q = $pdo->prepare('SELECT * FROM `countries` WHERE `id`=\''.$data['country'].'\'');
    $q->execute();
    $country = $q->fetch(PDO::FETCH_ASSOC);

    $states = array();
    $states[] = $country['name'];

    $q = $pdo->prepare('SELECT * FROM `states` WHERE `country_id`=?');
    $q->execute(array($data['country']));
    foreach($q as $row)
    {
      $states[] = str_replace("'", "\'", $row['name']);
    }

    $location = ' AND (MATCH(`fullAddress`, `primaryAddress`) AGAINST (\'';
    foreach($states as $state)
    {
      $location .= '"*'.$state.'*" ';
    }
    $location .= '\' IN BOOLEAN MODE))';

  }

  if(!empty($data['country']) && !empty($data['state']))
  {

    $q = $pdo->prepare('SELECT * FROM `states` WHERE `id`=?');
    $q->execute(array($data['state']));
    $state = $q->fetch(PDO::FETCH_ASSOC);

    $cities = array();
    $cities[] = $state['name'];

    $q = $pdo->prepare('SELECT * FROM `cities` WHERE `state_id`=?');
    $q->execute(array($data['state']));
    foreach($q as $row)
    {
      $cities[] = str_replace("'", "\'", $row['name']);
    }

    if(@count($cities)>0)
    {
        $area_codes = json_decode(file_get_contents('us_area_codes.json'), 1);
        
        $phoneStr = '';
        if(!empty($area_codes[$state['name']]))
        {
          foreach($area_codes[$state['name']] as $code)
          {
            $phoneStr .= ' OR `phone_numbers` LIKE \'%"'.$code.'%\'';
            $phoneStr .= ' OR `phone_numbers` LIKE \'%('.$code.')%\'';
            $phoneStr .= ' OR `phone_numbers` LIKE \'%+1 '.$code.' %\'';
          }
        }

        $location = ' AND (`fullAddress` LIKE \'%'.$state['name'].'%\' OR `primaryAddress` LIKE \'%'.$state['name'].'%\' '.$phoneStr.')';

    }

  }

  if(!empty($data['country']) && !empty($data['state']) && !empty($data['city']))
  {

    $q = $pdo->prepare('SELECT * FROM `cities` WHERE `id`=?');
    $q->execute(array($data['city']));
    $city = $q->fetch(PDO::FETCH_ASSOC);

    $location = ' AND `primaryAddress` LIKE \'%'.$city['name'].'%\'';

  }

  $sql .= @$location;

  if(!empty($data['names']))
  {
    //$sql .= ' AND `names` LIKE \'%'.str_replace(' ', '%', $data['names']).'%\'';
  }

  if(!empty($data['law_school']))
  {
    $sql .= ' AND `law_school` LIKE \'%'.str_replace(' ', '%', $data['law_school']).'%\'';
  }

  if(!empty($data['undergraduate_school']))
  {
    $sql .= ' AND `education` LIKE \'%'.str_replace(' ', '%', $data['undergraduate_school']).'%\' AND (`education` LIKE \'%B.A.%\' OR `education` LIKE \'%BA %\' OR `education` LIKE \'%B.S.%\' OR `education` LIKE \'%BS %\' OR `education` LIKE \'%undergrad%\' OR `education` LIKE \'%bachelor%\')';
  }

  if(!empty($data['firmName']))
  {
    $sql .= ' AND (`firmName` <> 0';
    foreach($data['firmName'] as $item)
    {
      $sql .= ' OR `firmName` LIKE \'%'.str_replace(array(' ', 'and', '&'), '%', $item).'%\'';
    }
    $sql .= ')';
  }

  if(!empty($data['bar_association']))
  {
    $sql .= ' AND (`bar_admissions` <> 0';
    foreach($data['bar_association'] as $item)
    {
      $sql .= ' OR `bar_admissions` LIKE \'%'.$item.'%\'';
    }
    $sql .= ')';
  }

  if(!empty($data['honors']))
  {
    $sql .= ' AND (`education` <> 0';
    foreach($data['honors'] as $item)
    {
      $item = str_replace(' ', '%', $item);
      $sql .= ' OR `education` LIKE \'%'.str_replace('lauda', 'laud', strtolower($item)).'%\'';
    }
    $sql .= ')';
  }

  if(!empty($data['degree']))
  {
    $sql .= ' AND (`education` <> 0';
    foreach($data['degree'] as $item)
    {
      switch ($item) {
        case 'PHD':
          $sql .= ' OR LOWER(`names`) LIKE \'%phd%\'';
          $sql .= ' OR LOWER(`names`) LIKE \'%ph.d%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%phd%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%ph.d%\'';
          break;
        case 'BS':
          $sql .= ' OR LOWER(`names`) LIKE \'%bs%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%b.s%\'';
          break;
        case 'BA':
          $sql .= ' OR LOWER(`names`) LIKE \'%ba%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%b.a%\'';
          break;
        case 'MA':
          $sql .= ' OR LOWER(`names`) LIKE \'%ma%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%m.a%\'';
          break;
        case 'MS':
          $sql .= ' OR LOWER(`names`) LIKE \'%ms%\'';
          $sql .= ' OR LOWER(`education`) LIKE \'%m.s%\'';
          break;
      }
    }
    $sql .= ')';
  }

  if(!empty($data['language']))
  {
    $sql .= ' AND (`languages` <> 0';
    foreach($data['language'] as $item)
    {
      if($item == 'English')
      {
        $sql .= ' OR `languages` LIKE \'%["N.A."]%\'';
        $sql .= ' OR `languages` LIKE \'%[""]%\'';
        $sql .= ' OR `languages` LIKE \'%America%\'';
        $sql .= ' OR `languages` = \'[]\'';
      }
      $sql .= ' OR `languages` LIKE \'%'.$item.'%\'';
    }
    $sql .= ')';
  }

  if(!empty($data['practice_areas']))
  {
    foreach($data['practice_areas'] as $item)
    {

      $sq = $pdo->prepare("SELECT * FROM `categories` WHERE LOWER(`name`)=? LIMIT 1");
      $sq->execute(array(strtolower($item)));
      if($sq->rowcount()>0)
      {
        $id = $sq->fetch(PDO::FETCH_ASSOC)['id'];
      }

      $str = trim(str_replace('&', '%', $item));
      if(strpos($str, '-') !== false)
      {
        $split = explode('-', $str);
        $sql .= ' AND ((`practice_areas` LIKE \'%'.$split[0].'%\' AND `practice_areas` LIKE \'%'.$split[1].'%\') OR (`description` LIKE \'%'.$split[0].'%\' AND `description` LIKE \'%'.$split[1].'%\')) ';
      }
      else
      {
        $sql .= ' AND (`practice_areas` LIKE \'%'.$str.'%\' OR `description` LIKE \'%'.$str.'%\' OR `education` LIKE \'%'.$str.'%\') ';
      }

      if($sq->rowcount()>0)
      {

        $sql .= 'AND (`practice_areas`<>0';

        $sq = $pdo->prepare("SELECT * FROM `sub_categories` WHERE `parent_id`=?");
        $sq->execute(array($id));

        foreach($sq as $row)
        {
          $str = trim(str_replace('&', '%', $row['name']));
          $sql .= ' OR `practice_areas` LIKE \'%'.$str.'%\' OR `description` LIKE \'%'.$str.'%\' OR `education` LIKE \'%'.$str.'%\'';
        }

        $sql .= ')';

      }

    }
  }

  if(!empty($data['jd_from']))
  {
    if(empty($data['jd_to'])) { $data['jd_to'] = 99999; }
    $sql .= ' AND (`jd_year` BETWEEN '.$data['jd_from'].' AND '.$data['jd_to'];
    if(isset($data['include_missing_jd']) && $data['include_missing_jd'] == 'true') { $sql .= ' OR `jd_year`=0'; }
    $sql .= ')';
  }

  if(!empty($data['positions']))
  {
    $sql .= ' AND (`positions`<>0 ';

    foreach($data['positions'] as $item)
    {
      if($item == 'Other')
      {
        $arr = array(
          'Chief',
          'CEO',
          'COO',
          'CMO',
          'CCFO',
          'CIO',
          'CTO',
          'CCO',
          'CDO',
          'CSO',
          'CPO',
          'CGO',
          'CECO',
        );
        foreach($arr as $corp)
        {
          $data['positions'][] = $corp;
        }
      }
    }

    foreach($data['positions'] as $key => $position)
    {

      if($position != 'Other')
      {
        $sql .= ' OR `positions` LIKE \'%'.$position.'%\'';
      }
      else
      {
        $sql .= ' OR `positions` LIKE \'%Other%\'';
      }

    }

    $sql .= ')';
  }

  $sql = str_replace('`positions` LIKE \'%Chief%\' OR `positions` LIKE \'%Chief%\'', '`positions` LIKE \'%Chief%\'', $sql);

  $char = strlen($sql);

  echo '<div class="alert alert-warning">Debug: '.$char.'
  <textarea class="form-control" style="height: 40vh;">'.$sql.'</textarea></div>';

  //var_dump($_POST);
  //echo $sql;

  //$sql .= ' AND `LinkedIn` LIKE \'%linkedin.com/in%\'';

  if($_SESSION['account_type'] == 'FREE')
  {
    $per_page = 10;
  }
  
  $sql .= ' ORDER BY `names` ASC LIMIT '.$per_page.' OFFSET '.$offset;

  if($char != 36)
  {
    $q = $pdo->prepare($sql);
    $q->execute();
    $persons = $q->fetchAll();
  }
  else
  {
    echo '<div class="alert alert-warning">Search criteria empty.</div><br/>';
    $q = $pdo->prepare('SELECT * FROM `people` ORDER BY RAND() LIMIT '.$per_page);
    $q->execute();
    $persons = $q->fetchAll();
  }

}
else
{
  $q = $pdo->prepare('SELECT * FROM `people` ORDER BY RAND() LIMIT '.$per_page);
  $q->execute();
  $persons = $q->fetchAll();
}

if($_SESSION['account_type'] !== 'FREE')
{
  $values = array(10, 25, 50, 100);
  ?>
  <div style="width: 200px; margin-bottom: 10px;">
    <table>
      <tr>
        <td>
          Show
        </td>
        <td>
          <select class="form-control" id="per_page" style="margin-bottom: -2.5px;">
            <?php
            foreach($values as $value)
            {
              echo '<option value="'.$value.'"';
              if($value == $per_page)
              {
                echo ' selected';
              }
              echo '>'.$value.'</option>';
            }
            ?>
          </select>
        </td>
        <td>
          per page
        </td>
      </tr>
    </table>
  </div>
  <?php
}

?>

<style type="text/css">
  .blur
  {
    -webkit-filter: blur(5px);
    -moz-filter: blur(5px);
    -o-filter: blur(5px);
    -ms-filter: blur(5px);
    filter: blur(5px);
  }
</style>

<div style="position: relative;">
  <?php
  if($_SESSION['account_type'] == 'FREE')
  {
    ?>
    <h4 style="position: absolute; bottom: 25%; text-shadow: 1px 1px #888; z-index: 999999; left: 0; right: 0; margin: 0 auto; text-align: center; color: #FFF;">
      Please
      <div class="dropdown d-inline">
        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" style="color: #FFF; font-weight: bold; font-size: large;">
          Upgrade
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
          <li><a class="dropdown-item" style="color: #333 !important;" href="<?php echo $stripe_monthly; ?>">Monthly ($399/month)</a></li>
          <li><a class="dropdown-item" style="color: #333 !important;" href="<?php echo $stripe_yearly; ?>">Yearly ($3999/yr 16.5% discount)</a></li>
        </ul>
      </div>
      to get full access to all our attorneys and jobs!
    </h4>
    <?php
  }

  $search_settings = json_decode($_SESSION['search_settings'], 1);
  ?>
  <table class="table table-hover table-striped bg-dark text-white" id="table" style="z-index: 100;">
    <thead>
      <th style="width: 400px; padding: 10px !important; padding-left: 10px !important;">Name</th>
      <?php
      if($search_settings['show_firm_name'] == 'true')
      {
        echo '<th class="hide-on-small" style=" padding: 10px !important;">Firm</th>';
      }
      if($search_settings['show_primary_location'] == 'true')
      {
        echo '<th class="hide-on-small" style=" padding: 10px !important;">Primary Location</th>';
      }
      if($search_settings['show_practice_area'] == 'true')
      {
        echo '<th class="hide-on-small" style=" padding: 10px !important;">Practice Area</th>';
      }
      if($search_settings['show_actions'] == 'true')
      {
        echo '
        <th class="hide-on-small" style=" padding: 10px !important; width: 55px !important; padding-left: 8.5px !important;">
          <div style="margin: 0 auto; text-align: center;">
              <div class="form-check" style="padding-top: 10px;">
                <input class="form-check-input" style="width: 25px; height: 25px; font-size: large;" type="checkbox" value="1" id="select_all">
              </div>
          </div>
        </th>
        ';
      }
      ?>
    </thead>
    <tbody>
      <?php
      $i = 0;
      foreach($persons as $row)
      {

        $i++;

        if(strpos($row['photo_headshot'], 'http') === false)
        {
          $row['photo_headshot'] = $root.'/img/nophoto.png';
        }

        if($_SESSION['account_type'] == 'FREE' && $i>5)
        {
          echo '<tr class="blur">';
        }
        else
        {
          echo '<tr>';
        }
        ?>

        <td>
          <table style="width: 100%;">
            <tr>
              <?php
              if($search_settings['show_profile_image'] == 'true')
              {
              ?>
              <td style="width: 100px; color: #FFF;" id="photo_headshot">
                <div class="lazyload" style="width: 100px; height: 100px; background-position: top center; background-size: cover;" data-src="<?php echo $root.'/thumb.php?url='.urlencode($row['photo_headshot']); ?>">
                    <div style="text-align: center; margin: 0 auto; padding-top: 35px;">
                      <div class="spinner-grow spinner text-white text-center" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </div>
                </div>
              </td>
              <?php
              }
              ?>
              <td style="padding-left: 20px; color: #FFF;">
                  <?php
                  if($_SESSION['account_type'] == 'FREE' && $i>5)
                  {
                    echo '<div style="font-size: large; display: block; font-weight: 700;"> ';
                  }
                  else
                  {
                    echo '<div style="font-size: large; display: block; font-weight: 700; cursor: pointer;" attr-person="'.$row['id'].'" attr-linkedIn="'.$row['LinkedIn'].'"> ';
                  }
                  echo implode(' ', json_decode($row['names'], 1));
                  if(!empty($row['LinkedIn']))
                  {
                    echo ' <a href="'.$row['LinkedIn'].'" target="_blank"><img src="'.$root.'/img/linkedin.svg" style="width: 25px;"/></a>';
                  }
                  ?>
                </div>
                <span class="practice-area"><?php echo @json_decode($row['positions'], 1)[0]; ?></span>
                <?php
                if($row['JD_year']>0)
                {
                  echo '<span class="practice-area">JD '.$row['JD_year'].'</span>';
                }
                ?>
                <div><?php echo @json_decode($row['phone_numbers'], 1)[0]; ?></div>
              </td>
            </tr>
          </table>
        </td>
        <?php
        if($search_settings['show_firm_name'] == 'true')
        {
        ?>
        <td class="hide-on-small pt-4" style="color: #FFF;" filter-type="firmName" filter-value="<?php echo $row['firmName']; ?>">
          <span style="cursor: pointer;"><?php echo $row['firmName']; ?></span>
        </td>
        <?php
        }
        ?>
        <?php
        if($search_settings['show_primary_location'] == 'true')
        {
        ?>
        <td class="hide-on-small pt-4" style="color: #FFF;">
          <span style="cursor: default;"><?php echo $row['primaryAddress']; ?></span>
        </td>
        <?php
        }
        ?>
        <?php
        if($search_settings['show_practice_area'] == 'true')
        {
        ?>
        <td class="hide-on-small pt-4" style="color: #FFF;" filter-type="practice_areas" filter-value="<?php echo @json_decode($row['practice_areas'], 1)[0]; ?>">
          <span style="cursor: pointer;"><?php echo @json_decode($row['practice_areas'], 1)[0]; ?></span>
        </td>
        <?php
        }
        ?>
        <?php
        if($search_settings['show_actions'] == 'true')
        {
        ?>
        <td>
          <div style="margin: 0 auto; text-align: center;">
              <div class="form-check" style="padding-top: 10px;">
                <input class="form-check-input" style="width: 25px; height: 25px; font-size: large;" type="checkbox" value="" id="checked_<?php echo $row['id']; ?>" attr-checked="<?php echo $row['id']; ?>">
              </div>
          </div>
        </td>
        <?php
        }
        ?>
        </tr>
        <?php
      }
      ?>
    </tbody>
  </table>
</div>

<?php

if(isset($sql) && $char != 36)
{
  ?>
  <div style="background: #555; padding: 20px 20px;">
    <div style="float: left; font-size: x-large; color: #FFF;">
      <?php
      $countSQL = explode(' LIMIT', str_replace('SELECT * FROM `people`', 'SELECT COUNT(*) as `results` FROM `people`', $sql))[0];
      $q = $pdo->prepare($countSQL);
      $q->execute();

      $results = $q->fetch(PDO::FETCH_ASSOC)['results'];

      $page = $data['page'];

      $totalPages = floor($results/$per_page);

      echo 'Page '.($page+1).' of '.number_format(($totalPages+1)).' / '.number_format($results).' results';

      ?>
    </div>
    <div style="float: right;">
      <?php
      if($_SESSION['account_type'] != 'FREE')
      {
        if($page != 0)
        {
          echo ' <button class="btn btn-dark text-white" style="width: 100px;" attr-page="'.($page-1).'">Prev</button>';
        }
        ?>
        <select name="pageSelect" id="pageSelect" class="form-control" style="display: inline-block; width: 100px; text-align: center;">
          <?php
          $num = $totalPages+1;
          for ($i=1; $i <= $num; $i++) { 
            echo '<option value="'.$i.'" attr-page="'.$i.'"';
            if($i == ($page+1)) { echo ' selected'; }
            echo '>'.$i.'</option>';
          }
          ?>
        </select>
        <?php
        if($page < $totalPages)
        {
          echo ' <button class="btn btn-dark text-white" style="width: 100px;" attr-page="'.($page+1).'">Next</button>';
        }
      }
      else
      {
        echo '<div style="float: left; font-size: x-large; color: #FFF;"><a href="'.$root.'/upgrade">Upgrade</a> to see all results.</div>';
      }
      ?>
    </div>
    <div style="clear: both;"></div>
  </div>
  <?php
}
?>