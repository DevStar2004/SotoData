<?php
$f = scandir('add_to_queue');
foreach($f as $file)
{
	if(is_file('add_to_queue/'.$file))
	{
		if(!file_exists('jobs/'.$file))
		{
			echo $file.'<br/>';
		}
	}
}
die();

$currentFirms = array();
$firmsMissing = array();
$f = scandir('jobs');
unset($f[0]);
unset($f[1]);
foreach($f as $firmFile)
{
	$currentFirms[] = str_replace('.php', '', $firmFile);
}

$f = file_get_contents('firmList.txt');
$lines = explode("\n", $f);
foreach ($lines as $line) {
	$firmName = explode('.', str_replace('www.', '', parse_url($line)['host']))[0];
	if(!in_array($firmName, $currentFirms))
	{
		$firmsMissing[] = $firmName;
		echo $firmName.'<br/>';
	}
}
?>