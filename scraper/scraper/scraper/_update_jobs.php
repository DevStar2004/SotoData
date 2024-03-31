<?php
include 'config.php';
include 'simple_html_dom.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$q = $pdo->prepare('SELECT * FROM `job_status` WHERE `last_crawl`=0 ORDER BY `id` ASC LIMIT 10');
$q->execute(array());

foreach ($q as $row) {

	$company_id = $row['company_id'];
	$image = $row['company_image'];
	$id = $row['id'];
	$start = 0;

	$q = $pdo->prepare('DELETE FROM `jobs` WHERE `image`=?');
	$q->execute(array($row['company_image']));

	while ($start < 200) {
		
		$values = array();
		
		$data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search?keywords=&f_C='.$company_id.'&location=Worldwide&trk=public_jobs_jobs-search-bar_search-submit&geoId=92000000&pageNum=1&start='.$start));

		if(strpos($data, 'base-search-card__title') === false)
		{

			$data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search?keywords=&f_C='.$company_id.'&location=Worldwide&trk=public_jobs_jobs-search-bar_search-submit&geoId=92000000&pageNum=1&start='.$start));

			if(strpos($data, 'base-search-card__title') === false)
			{

				$data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search?keywords=&f_C='.$company_id.'&location=Worldwide&trk=public_jobs_jobs-search-bar_search-submit&geoId=92000000&pageNum=1&start='.$start));

				if(strpos($data, 'base-search-card__title') === false)
				{

					$data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search?keywords=&f_C='.$company_id.'&location=Worldwide&trk=public_jobs_jobs-search-bar_search-submit&geoId=92000000&pageNum=1&start='.$start));

				}


			}
		}

		if(strpos($data, 'base-search-card__title') !== false)
		{
			$html = str_get_html($data);
		}
		else
		{
			break;
		}

		foreach($html->find('li') as $item)
		{
			$value = array();
			$value['title'] = trim($item->find('.base-search-card__title', 0)->plaintext);
			$value['image'] = html_entity_decode($image);
			$value['company'] = trim($item->find('.hidden-nested-link', 0)->plaintext);
			$value['location'] = trim($item->find('.job-search-card__location', 0)->plaintext);
			if($item->find('.job-search-card__listdate', 0))
			{
				if($item->find('.job-search-card__listdate', 0))
				{
					$value['time'] = strtotime($item->find('.job-search-card__listdate', 0)->plaintext);
				}
				else
				{
					$value['time'] = '';
				}
			}
			else
			{
				$value['time'] = '';
			}
			if($item->find('.result-benefits__text', 0))
			{
				$value['applicants'] = trim($item->find('.result-benefits__text', 0)->plaintext);
			}
			else
			{
				$value['applicants'] = 'Be an early applicant';
			}
			$value['job_id'] = get_string_between($item->innertext, 'urn:li:jobPosting:', '"');
			$values[] = $value;
		}

		foreach($values as $row)
		{
			$q = $pdo->prepare('INSERT INTO `jobs` VALUES (?,?,?,?,?,?,?,?,?)');
			$q->execute(array(
				$row['title'],
				$row['image'],
				$row['company'], 
				$row['location'],
				$row['time'],
				$row['applicants'],
				'',
				$row['job_id'],
				NULL
			));
		}

		$start = $start+25;

	}

	$q = $pdo->prepare('UPDATE `job_status` SET `last_crawl`=? WHERE `id`=?');
	$q->execute(array(time(), $id));

}

?>