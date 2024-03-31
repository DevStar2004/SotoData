<?php
include 'scraper/config.php';

if(isset($_POST['spider_name']))
{
  $q = $pdo->prepare('SELECT * FROM `people` WHERE `spider_name`=? ORDER BY `names` ASC');
  $q->execute(array($_POST['spider_name']));
  $data = $q->fetchAll(PDO::FETCH_ASSOC);

  $file = fopen('exports/'.$_POST['spider_name'].'.csv', 'w+');

  $headerLine = array(
      'names',
      'email',
      'vCard',
      'fullAddress',
      'primaryAddress',
      'LinkedIn',
      'phone_numbers',
      'fax',
      'education',
      'bar_admissions',
      'court_admissions',
      'practice_areas',
      'acknowledgements',
      'memberships',
      'positions',
      'languages',
      'source',
      'description',
      'last_update',
      'photo_headshot',
      'photo',
      'spider_name',
      'firmName',
      'law_school',
      'JD_year',
      'id'
  );
  fputcsv($file, $headerLine);

  foreach ($data as $line) {
      fputcsv($file, $line);
  }

  
  fclose($file);

  header('Location: exports/'.$_POST['spider_name'].'.csv');

}
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  </head>
  <body>
    <div class="container mt-5 mb-5">
      <h4>Sotodata - Admin</h4>
      <hr/>
      <a href="index.php" class="btn btn-default">Home</a>
      <a href="checkCount.php" class="btn btn-default">Check spider crawl count</a>
      <a href="checkFails.php" class="btn btn-default">Check spider crawl failures</a>
      <a href="export.php" class="btn btn-primary">Export spider attorneys</a>
      <a href="cron_config.php" class="btn btn-default mr-5">CRON config</a>
      <a href="index.php?logout=1" class="btn btn-danger ml-5">Logout</a>
      <hr/>
      <h5 class="pt-4 text-muted">Select Firm</h5>
      <form action="" method="post" class="p-2 text-muted">
        <select name="spider_name" class="form-control">
          <?php
          $q = $pdo->prepare('SELECT DISTINCT(spider_name) FROM `people` ORDER BY `spider_name` ASC');
          $q->execute();
          foreach ($q as $row) {

            $q_ = $pdo->prepare('SELECT DISTINCT(firmName) FROM `people` WHERE `spider_name`=? LIMIT 1');
            $q_->execute(array($row['spider_name']));
            $firm = $q_->fetch(PDO::FETCH_ASSOC);

            echo '<option value="'.$row['spider_name'].'">'.$firm['firmName'].'</option>'."\n";

          }
          ?>
        </select>
        <button class="btn btn-primary mt-2">Export</button>
      </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  </body>
</html>