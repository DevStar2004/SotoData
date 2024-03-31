<?php
include 'scraper/config.php';
?><!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title></title>

  <body>

    <div class="container mt-5 mb-5">
      
      <h4>Sotodata - Admin</h4>
      <hr/>
      <a href="index.php" class="btn btn-default">Home</a>
      <a href="checkCount.php" class="btn btn-primary">Check spider crawl count</a>
      <a href="checkFails.php" class="btn btn-default">Check spider crawl failures</a>
      <a href="export.php" class="btn btn-default">Export attorneys</a>
      <a href="cron_config.php" class="btn btn-default mr-5">CRON config</a>
      <a href="index.php?logout=1" class="btn btn-danger ml-5">Logout</a>
      <hr/>

      <table class="table table-hover table-striped">
        <thead class="bg-primary text-white">
          <tr>
            <th style="width: 70%;">Firm</th>
            <th>Attorneys</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $arr = array();

          $q = $pdo->prepare('SELECT DISTINCT(spider_name) FROM `queue` ORDER BY `spider_name` ASC');
          $q->execute(array());

          $people = $pdo->prepare('SELECT `spider_name` FROM `people`');
          $people->execute(array());
          foreach($people as $row)
          {
            $arr[$row['spider_name']][] = $row;
          }

          foreach ($q as $row) {

            if(!isset($arr[$row['spider_name']]))
            {
              $res = ' 0 <span class="text-danger">spider failed</span>';
            }
            else
            {
              $res = count($arr[$row['spider_name']]);
            }

            echo '
            <tr>
              <td style="width: 70%;">'.$row['spider_name'].'</td>
              <td>'.$res.'</td>
            </tr>
            ';
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
  </body>
</html>