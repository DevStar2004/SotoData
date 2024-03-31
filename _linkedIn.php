<?php
include 'config.php';
include 'simple_html_dom.php';
set_time_limit(20);
error_reporting(E_ALL);

$q = $pdo->prepare('SELECT * FROM `people` WHERE `id`=?');
$q->execute(array($_GET['id']));
$person = $q->fetch(PDO::FETCH_ASSOC);

$name = '+'.implode(' +', json_decode($person['names'], 1));
$q = $pdo->prepare('SELECT * FROM `external_sources` WHERE MATCH(`name`) AGAINST(\''.str_replace(array('\t', "'"), array('', "\'"), $name).'\' IN BOOLEAN MODE) LIMIT 1');
$q->execute();
if($q->rowcount()>0)
{
    $data = json_decode($q->fetch(PDO::FETCH_ASSOC)['data'], 1);

    echo '<h5 style="border-bottom: 1px solid #444; margin-bottom: 20px;">'.json_decode($person['positions'], 1)[0].' at <a href="'.$person['source'].'" target="_blank" class="text-warning">'.$person['firmName'].'</a> <span class="text-muted">Present</span></h5>';

    $ex = explode(' ', $person['firmName']);
    $part = strtolower($ex[0]);

    foreach($data['experience']['items'] as $item)
    {
    	if(strpos(strtolower($item['entity_name']), $part) === false)
        {
        	echo '<h5 style="border-bottom: 1px solid #444; margin-bottom: 20px;">'.$item['positions'][0]['title'].' at <a href="'.$item['website'].'" target="_blank" class="text-warning">'.$item['entity_name'].'</a> <span class="text-muted">'.$item['duration'].'</span></h5>';
        }
    }
}

else
{
	if(empty($_GET['url']) || $_GET['url'] == 'not_found' || strpos($_GET['url'], 'linkedin.com') === false)
	{
		echo '<div class="card card-body mt-4" style="background: #333; color: #FFF;">Experience will be updated soon.</div>';
		exit();
	}

	if(!empty($_GET['url']))
	{

		$url = $_GET['url'];
		$sUrl = substr($url, 0, -3);

		$q = $pdo->prepare('SELECT * FROM `linkedIn` WHERE `url` LIKE \'%'.$sUrl.'%\' AND `content`<>\'\' LIMIT 1');
		$q->execute();

		if($q->rowcount()>0)
		{
			$html = str_get_html($q->fetch(PDO::FETCH_ASSOC)['content']);
		}
		else
		{
			file_put_contents('__linkedInQueue_temp.txt', $url);
			sleep(10);

			$html = str_get_html(file_get_contents('__linkedInQueueData.txt'));
		}

		if(empty($html))
		{
			echo '<div class="card card-body mt-4" style="background: #333; color: #FFF;">Experience will be updated soon.</div>';
			exit();
		}

		echo '
		<style> 
			.pvs-list
			{
				max-width: 90%;
				list-style-type: none;
				margin-left: -20px !important;
			}
			.profile-section-card__title, .date-range
			{
				color: #AAA !important;
			}
			.experience-group__positions
			{
				list-style-type: none;
			}
			.artdeco-entity-image--ghost, .show-more-less-text__text--less, .show-more-less-text__button
			{
				display: none !important;
			}
			.visually-hidden
			{
				display: none;
			}
			[data-field="experience_company_logo"]
			{
				padding: 5px;
			}
			.pvs-list__outer-container
			{
				padding-left: 0;
			}
			.flex-wrap {
			    flex-wrap: wrap !important;
			}
			.full-height {
			    height: 100% !important;
			}
			.display-flex {
			    display: flex !important;
			}
			.align-items-center {
			    align-items: center !important;
			}
			.flex-column {
			    flex-direction: column !important;
			}
			.mr1 {
			    margin-right: 0.2rem !important;
			}
			.ivm-view-attr__img--centered {
			    background-position: 50%;
			    background-size: cover;
			    object-position: center;
			    object-fit: cover;
			}
			.pvs-entity {
			    padding: 10px 0;
			    display: flex;
			}
			.pvs-entity--padded {
			    padding-left: 0px;
			    padding-right: 0px;
			}
			ul {
			    display: block;
			    list-style-type: disc;
			    margin-block-start: 0;
			    margin-block-end: 0;
			    margin-inline-start: 0px;
			    margin-inline-end: 0px;
			    padding-inline-start: 10px;
			}

			.pvs-list__item--one-column {
			    display: flex;
			    flex-direction: column;
			    width: 100%;
			}
			.pvs-list__item--line-separated {
			    padding: 0;
			}
			.artdeco-list__item {
			    border: 0;
			    position: relative;
			    background-color: transparent;
			    margin: 0;
			    padding: 0px 0px 0px;
			}
			.justify-space-between {
			    justify-content: space-between!important;
			}
			.flex-row {
			    flex-direction: row!important;
			}

			.justify-space-between {
			    justify-content: space-between!important;
			}

			.t-bold
			{
				font-weight: 400;
				font-size: 20px;
			}

			.t-14
			{
				font-size: 15px;
			}

			.inline-show-more-text, .pvs-thumbnail
			{
				display: none;
			}

			.t-black--light
			{
				color: #AAA;
			}

			.pvs-entity--padded
			{
				padding-top: 20px;
				border-bottom: 1px solid #333;
			}

			.inline-show-more-text--is-collapsed
			{
				display: none;
			}

			.pvs-list__outer-container.pvs-entity__sub-components
			{
				padding-left: 15px;
			}

			ul, li
			{
				list-style-type: none !important;
			}
		</style>
		';

		if(empty($html->find('ul.pvs-list', 0)))
		{
			exit('');
		}

		foreach($html->find('section') as $item)
		{
			if(strpos($item->innertext, 'id="education"') !== false)
			{
				$year = @$item->find('.t-black--light', 0)->innertext;
				$str = strtolower($item->find('.t-normal', 0)->innertext);
				if(strpos($str, 'jd') !== false || strpos($str, 'j.d') !== false || strpos($str, 'doctor') !== false || strpos($str, 'law school') !== false || strpos($str, 'doctor') !== false)
				{
					preg_match_all('/([0-9]+)/', $year, $matches);
					$jd = (int) $matches[0][count($matches[0])-1];
					break;
				}
			}
		}

		if(!empty($jd))
		{
			$q = $pdo->prepare('UPDATE `people` SET `JD_year`=? WHERE `LinkedIn`=? LIMIT 1');
			$q->execute(array($jd, $_GET['url']));
		}

		$content = $html->find('ul.pvs-list', 0)->innertext;
	    $content = str_replace('data-delayed-url', 'src', $content);
	    $content = str_replace('<a ', '<a target="_blank" ', $content);
	    echo '<ul class="pvs-list" style="padding-left: 20px;">'.$content.'</ul>';
	    if(!empty($jd) && is_numeric($jd))
	    {
	    	echo '<span style="display: none;" id="jd_year">'.$jd.'</span>';
	    }

	}
	else
	{
		echo '<div class="card card-body mt-4" style="background: #333; color: #FFF;">Experience will be updated soon.</div>';
	}
}

?>