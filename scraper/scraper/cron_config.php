<?php
include 'scraper/config.php';
if(empty($_SESSION['admin']))
{
	header('Location: login.php');
	exit();
}

if(!empty($_GET['logout']))
{
	session_destroy();
	header('Location: index.php');
	exit();
}

$config = explode(':', file_get_contents('crawl_config.txt'));
?>
<!doctype html>
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
      <a href="checkFails.php" class="btn btn-default">Check spider crawl failures</a>
      <a href="export.php" class="btn btn-default">Export attorneys</a>
      <a href="cron_config.php" class="btn btn-primary mr-5">CRON config</a>
      <a href="index.php?logout=1" class="btn btn-danger ml-5">Logout</a>
      <hr/>
      <form action="" method="post">
        <p>Config applies to all spiders example:<br/>
        100 spiders with config crawl 5 every 1 minute = crawl 500 every 1 minute</p>
        <table class="table table-striped table-hover">
          <tr>
            <td style="vertical-align: middle; text-align: center;">Crawl</td>
            <td style="vertical-align: middle; text-align: center;">
              <input type="number" name="number" value="<?php echo $config[0]; ?>" class="form-control" />
            </td>
            <td style="vertical-align: middle; text-align: center;">
              attorneys
            </td>
            <td style="vertical-align: middle; text-align: center;">every</td>
            <td style="vertical-align: middle; text-align: center;">
              <input type="number" name="number" value="<?php echo $config[1]; ?>" class="form-control" />
            </td>
            <td style="vertical-align: middle; text-align: center;">Minutes</td>
          </tr>
        </table>
        <button class="btn btn-primary">Save</button>
      </form>
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
</html><?php
exit();
$f = scandir('scraper/add_to_queue');
foreach ($f as $file) {
	if(is_file('scraper/add_to_queue/'.$file))
	{
		//echo str_replace('.php', '', $file).'<br/>';
		echo '*/5 * * * * wget -q -O /dev/null "http://24.199.117.42/scraper/jobs/'.$file.'" > /dev/null 2>&1<br/>'."\n";
	}
}
?>