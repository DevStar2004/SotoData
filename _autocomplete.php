<?php
include 'config.php';

$results = array();

$q = $pdo->prepare('SELECT * FROM `sub_categories` WHERE `name` LIKE \'%'.$_GET['keyword'].'%\' LIMIT 10');
$q->execute();
foreach($q as $row)
{
  $results[] = '<a href="#" filter-type="practice_areas" filter-value="'.trim($row['name']).'" class="btn btn-dark text-white btn-sm" style="margin: 2.5px;" attr-customFilter>'.trim($row['name']).'</a>';
}

$results = array_unique($results);

$namesearch = $_GET['keyword'];
if(strpos($namesearch, ' ') !== false)
{
  $namesearch = str_replace(' ', '%', $namesearch);
}

$q = $pdo->prepare('SELECT * FROM `people` WHERE `names` LIKE \'%'.$namesearch.'%\' LIMIT 10');
$q->execute();
foreach($q as $row)
{
  if(strpos($row['LinkedIn'], 'linkedin.com/in') === false)
  {
    $row['LinkedIn'] = '';
  }
  $results[] = '<a attr-person="'.$row['id'].'" attr-linkedIn="'.$row['LinkedIn'].'" href="#" class="btn btn-dark text-white btn-sm" style="margin: 2.5px;" attr-customFilter>'.implode(' ', json_decode($row['names'], 1)).'</a></div>';
}

if(empty($results))
{
  die(0);
}

foreach($results as $result)
{
  echo $result;
}

?>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 5px; right: 5px; z-index: 9999999;" id="close_autocomplete"></button>