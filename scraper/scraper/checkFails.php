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
      <a href="checkCount.php" class="btn btn-default">Check spider crawl count</a>
      <a href="checkFails.php" class="btn btn-primary">Check spider crawl failures</a>
      <a href="export.php" class="btn btn-default">Export attorneys</a>
      <a href="cron_config.php" class="btn btn-default mr-5">CRON config</a>
      <a href="index.php?logout=1" class="btn btn-danger ml-5">Logout</a>
      <hr/>
      <?php
      $dir = 'scraper/jobs';
      $f = scandir($dir);
      foreach ($f as $file) {
        if(is_file($dir.'/'.$file))
        {
          $spider_name = str_replace('.php', '', $file);
          if($spider_name != '_cron')
          {
            $q = $pdo->prepare('SELECT * FROM `people` WHERE `spider_name`=? LIMIT 1');
            $q->execute(array($spider_name));
            if($q->rowcount()<1)
            {
              echo $spider_name.'<br/>';
            }
          }
        }
      }
      ?>

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