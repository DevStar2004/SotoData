<?php
include 'config.php';

$q = $pdo->prepare('SELECT * FROM `people` WHERE `retouched`=0 ORDER BY `id` ASC LIMIT 100');
$q->execute();
$people = $q->fetchAll(PDO::FETCH_ASSOC);

foreach ($people as $row) {

	$name = '+'.implode(' +', json_decode($row['names'], 1));
	$q = $pdo->prepare('SELECT * FROM `external_sources` WHERE MATCH(`name`) AGAINST(\''.str_replace(array('\t', "'"), array('', "\'"), $name).'\' IN BOOLEAN MODE) LIMIT 1');
	$q->execute();

	if($q->rowcount()>0)
	{
		var_dump($row['names']);
		$data = json_decode($q->fetch(PDO::FETCH_ASSOC)['data'], 1);

		$values = array();
		$values['email'] = @$data['email'];
		$values['LinkedIn'] = @$data['linkedin_url'];
		$values['image'] = @$data['photo_url'];
		$values['jd_year'] = @$data['graduation_year'];
		if(!empty($data['law_school']))
		{
			$values['law_school'] = @$data['law_school']['law_school_name'];
		}
		else
		{
			$values['law_school'] = '';
		}
		$values['education'] = @json_encode(explode(';', $data['attorney_education']['education']));
		$values['bar_admissions'] = @json_encode($data['attorneys_bar_admissions']);
		$values['practice_areas'] = @json_encode($data['attorneys_practice_areas']);
		$values['memberships'] = @json_encode($data['raw_attorneys_memberships']);
		$values['acknowledgements'] = @json_encode($data['raw_attorneys_acknowledgements']);

		if(empty($row['email']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `email`=? WHERE `id`=?');
			$q->execute(array($values['email'], $row['id']));
		}

		if(empty($row['LinkedIn']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `id`=?');
			$q->execute(array($values['LinkedIn'], $row['id']));
		}

		if(empty($row['photo']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `photo`=?,`photo_headshot`=? WHERE `id`=?');
			$q->execute(array($values['image'], $values['image'], $row['id']));
		}

		if(strlen($row['practice_areas'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `practice_areas`=? WHERE `id`=?');
			$q->execute(array($values['practice_areas'], $row['id']));
		}

		if(strlen($row['bar_admissions'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `bar_admissions`=? WHERE `id`=?');
			$q->execute(array($values['bar_admissions'], $row['id']));
		}

		if(strlen($row['education'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `education`=? WHERE `id`=?');
			$q->execute(array($values['education'], $row['id']));
		}

		if(strlen($row['acknowledgements'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `acknowledgements`=? WHERE `id`=?');
			$q->execute(array($values['acknowledgements'], $row['id']));
		}

		if(strlen($row['memberships'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `memberships`=? WHERE `id`=?');
			$q->execute(array($values['memberships'], $row['id']));
		}

		if(strlen($row['positions'])<5)
		{
			$q = $pdo->prepare('UPDATE `people` SET `positions`=? WHERE `id`=?');
			$q->execute(array(json_encode($data['attorneys_top_titles']), $row['id']));
		}

		if(!empty($values['jd_year']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `JD_year`=? WHERE `id`=?');
			$q->execute(array($values['jd_year'], $row['id']));
		}

		if(!empty($data['attorney_bio']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `description`=? WHERE `id`=?');
			$q->execute(array($data['attorney_bio'], $row['id']));
		}

		if(!empty($data['location']['city']))
		{
			$q = $pdo->prepare('UPDATE `people` SET `primaryAddress`=? WHERE `id`=?');
			$q->execute(array($data['location']['city'].', '.$data['location']['state'], $row['id']));
		}

	}

	$q = $pdo->prepare('UPDATE `people` SET `retouched`=1 WHERE `id`=?');
	$q->execute(array($row['id']));

}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
<script type="text/javascript">
	window.location.reload();
</script>
</body>
</html>