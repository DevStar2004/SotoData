<?php

include 'config.php';
include 'simple_html_dom.php';

if(isset($_GET['id']))
{
    $_POST['id'] = $_GET['id'];
}

$q = $pdo->prepare('SELECT * FROM `people` WHERE `id`=?');
$q->execute(array($_POST['id']));
$row = $q->fetch(PDO::FETCH_ASSOC);

$name = trim(implode(' ', json_decode($row['names'], 1)));

$q = $pdo->prepare('SELECT * FROM `crm` WHERE `member_id`=? AND `person_id`=? ORDER BY `time` DESC');
$q->execute(array($_SESSION['id'], $row['id']));

$crm = array();
$crm['status'] = 'Set Status';

foreach($q as $item)
{
    switch ($item['type']) {
        case 'note':
            $crm['notes'][] = $item;
            break;
        case 'tag':
            $crm['tag'][] = $item['content'];
            break;
        case 'email':
            $crm['email'] = $item['content'];
            break;
        case 'phone':
            $crm['phone'] = $item['content'];
            break;
        case 'status':
            $crm['status'] = $item['content'];
            break;
    }
}

?>

<div style="background: #222222;">
    <div class="card shadow bg-dark" style="color: #FFF; position: relative;">
        <div class="card-body p-4">
            <a href="javascript:void(0);" attr-goto="<?php echo $row['id']; ?>" style="position: absolute; top: 5px; right: 10px; font-size: x-large;"><i class="fa-solid fa-xmark"></i></a>
            <div class="row">
                <div class="col-md-7" style="border-right: 0.1vw solid #000;">
                    <div class="row mt-3">
                        <div class="col-lg-5 mb-2">
                            <div id="headshot" style="padding-left: 20px;">
                                <div style="width: 200px; height: 200px; background: url(<?php echo $root.'/thumb.php?url='.urlencode($row['photo_headshot']); ?>); background-position: top center; background-size: cover;"></div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <h4 class="text-white">
                                <span style="font-weight: 700;">
                                    <?php echo $name; ?>
                                    <?php
                                    if(!empty($row['LinkedIn']))
                                    {
                                      echo '<a href="'.$row['LinkedIn'].'" target="_blank"><img src="'.$root.'/img/linkedin.svg" style="width: 25px;"></a>';
                                    }
                                    ?>
                                </span>
                                <span style="font-size: 20px !important;">
                                    <a href="<?php echo $row['source']; ?>" target="_blank"><i class="fa-sharp fa-solid fa-link" style="font-size: 17.5px; color: #CCC;"></i></a>
                                </span>
                                <span style="font-size: 20px !important;" id="linkedIn_link"></span>
                            </h4>
                            <a href="#" style="color: #EFEFEF; font-weight: bold;">
                                <a href="javascript:void(0);" filter-type="firmName" filter-value="<?php echo $row['firmName']; ?>">
                                <?php echo $row['firmName']; ?>
                                </a>
                                <br />
                                <?php
                $positions = json_decode($row['positions'], 1);
                foreach($positions as $position)
                {
                  echo '<span class="practice-area">'.$position.'</span>';
                }

                if(!empty($crm['tag']))
                {
                    foreach($crm['tag'] as $tag)
                    {
                      echo '<span class="practice-area">'.$tag.'</span>';
                    }
                }
                ?>
                            </a><br /><br />
                            <?php
                $phone_numbers = json_decode($row['phone_numbers'], 1);
                if(!empty($crm['phone']))
                {
                    $phone_numbers[] = $crm['phone'];
                }
                foreach($phone_numbers as $phone_number)
                {
                  echo '<a href="#" style="color: #EFEFEF;"><i class="fa-solid fa-phone"></i> '.$phone_number.' </a><br/>';
                }
                ?>
                            <span style="color: #EFEFEF;"><i class="fa-solid fa-location-pin"></i>
                                <?php echo $row['primaryAddress']; ?></span>
                            <hr/>
                            <a href="download_vCard.php?person=<?php echo $row['id']; ?>">Download vCard</a>
                        </div>
                    </div>
                    <div>
                      <p style="margin: 0 !important; line-height: 20px; padding: 20px; display: none;" attr-fullbio="fullBio">
                          <?php echo strip_tags(html_entity_decode($row['description'])).' <a href="javascript:void(0);" style="color: #FECA05 !important; font-weight: 500;" attr-readless="read_less">Read less</a>'; ?>
                      </p>
                      <p style="margin: 0 !important; line-height: 20px; padding: 20px;" attr-smallbio="smallBio">
                          <?php echo substr(strip_tags(html_entity_decode($row['description'])), 0, 400);
                          if(strlen($row['description'])>400)
                          {
                            echo '<a href="javascript:void(0);" style="color: #FECA05 !important; font-weight: 500;" attr-readmore="read_more"><span style="color: #FFF !important;">...</span> Read more</a>';
                          }
                      ?>
                      </p>
                    </div>
                </div>
                <div class="col-md-5 mt-2" style="position: relative;">

                    <div style="position: absolute; z-index: 999999; top: 0; right: 20px; background: #444; color: #FFF; height: 200px; min-width: 440px; padding: 20px; display: none;" id="add-box">
                        <h2 id="add-action"></h2>
                        <div style="position: relative; min-height: 100%;">
                            <textarea id="add-content" class="form-control"></textarea>
                            <div style="position: absolute; bottom: 40px; right: 0;">
                                <button class="btn btn-warning" id="add-save">Save</button>
                                <button class="btn btn-light text-dark" id="add-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <div style="max-height: 250px; overflow-y: scroll;">
                        <?php
                        if(!empty($crm['notes']))
                        {
                            foreach($crm['notes'] as $item)
                            {
                                echo '<div class="p-2">
                                <div class="card card-body text-black">
                                '.$item['content'].' 
                                <hr/>
                                <span style="color: #333;">'.date('d/m/Y', $item['time']).' <em class="text-muted">'.@time_elapsed_string('@'.$item['time']).' </em></span>
                                </div>
                                </div>
                                ';
                            }
                        }
                        ?>
                    </div>

                    <button style="width: 120px;" class="btn btn-warning btn-sm m-2" attr-add="note" attr-add-value="<?php echo $row['id']; ?>">Add Note</button>
                    <button style="width: 120px;" class="btn btn-warning btn-sm m-2" attr-add="tag" attr-add-value="<?php echo $row['id']; ?>">Add Tag</button>
                    <button style="width: 120px;" class="btn btn-warning btn-sm m-2" attr-add="email" attr-add-value="<?php echo $row['id']; ?>">Add Email</button>
                    <button style="width: 120px;" class="btn btn-warning btn-sm m-2" attr-add="phone" attr-add-value="<?php echo $row['id']; ?>">Add Phone</button>
                    <a style="width: 120px; color: #000 !important;" target="_blank" class="btn btn-warning btn-sm m-2" href="mailto:<?php echO $row['email']; ?>">Send Email</a>
                    <div class="dropdown d-inline">
                      <button style="width: 120px;" class="btn btn-warning btn-sm m-2 dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $crm['status']; ?>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Contacted</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Follow Up</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Interested</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Interviewing</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Offer made</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Placed</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Submitted</a></li>
                        <li><button class="dropdown-item text-black" attr-setStatus="<?php echo $row['id']; ?>">Do not contact</a></li>
                      </ul>
                    </div>

                    <hr/>

                    <?php
                    if(!empty($row['practice_areas']) && $row['practice_areas'] != '[]' && $row['practice_areas'] != 'null')
                    {
                        ?>
                            <div class="row">
                                <div class="col-md-4" style="font-weight: 500;">
                                    Practice Areas
                                </div>
                                <div class="col-md-8">
                                    <?php
                        $practice_areas = @json_decode($row['practice_areas'], 1);
                        $practice_areas = array_unique($practice_areas);
                        $i = 1;
                        foreach($practice_areas as $area)
                        {
                          echo '<span class="practice-area">'.$area.'</span>';
                          $i++;
                          if($i == 10)
                          {
                            if(count($practice_areas)>10)
                            {
                                echo '<br/>and more...';
                            }
                            break;
                          }
                        }
                        ?>
                                </div>
                            </div>
                        <?php
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-4 pt-2" style="font-weight: 500;">
                            Email
                        </div>
                        <div class="col-md-8 pt-2">
                            <?php echo $row['email']; ?>
                            <?php
                            if(!empty($crm['email']))
                            {
                                echo '<br/>'.$crm['email'];
                            }
                            ?>
                        </div>
                    </div>
                    <?php
            if(!empty($row['fullAddress']))
            {
              echo '
              <div class="row">
                <div class="col-md-4 pt-2" style="font-weight: 500;">
                  Address
                </div>
                <div class="col-md-8 pt-2">
                '.str_replace(array('\n', '|'), ' ', $row['fullAddress']).'
                </div>
              </div>
              ';
            }
            ?>
                    <?php
            if(!empty($row['law_school']))
            {
              echo '
              <div class="row">
                <div class="col-md-4 pt-2" style="font-weight: 500;">
                  Law School
                </div>
                <div class="col-md-8 pt-2">
                '.$row['law_school'].'
                </div>
              </div>
              ';
            }

            if($row['JD_year']>1000)
            {
                echo '
              <div class="row">
                <div class="col-md-4 pt-2" style="font-weight: 500;">
                  JD Year
                </div>
                <div class="col-md-8 pt-2">
                '.$row['JD_year'].'
                </div>
              </div>
              ';
            }
            ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mt-4 bg-dark" style="color: #FFF;">
        <div class="card-body p-4">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements" type="button" role="tab" aria-controls="movements" aria-selected="true">Experience</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab" aria-controls="education" aria-selected="falsea">Education</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bar-tab" data-bs-toggle="tab" data-bs-target="#bar" type="button" role="tab" aria-controls="bar" aria-selected="false">Bar Admissions</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="court-tab" data-bs-toggle="tab" data-bs-target="#court" type="button" role="tab" aria-controls="court" aria-selected="false">Court Admissions</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acknowledgements-tab" data-bs-toggle="tab" data-bs-target="#acknowledgements" type="button" role="tab" aria-controls="acknowledgements" aria-selected="false">Acknowledgements</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="memberships-tab" data-bs-toggle="tab" data-bs-target="#memberships" type="button" role="tab" aria-controls="memberships" aria-selected="false">Memberships & Associations</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="languages-tab" data-bs-toggle="tab" data-bs-target="#languages" type="button" role="tab" aria-controls="languages" aria-selected="false">Languages</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade show active" id="movements" role="tabpanel" aria-labelledby="movements-tab">
                        <hr/>
                        <div id="activity_tab">
                            <div class="d-flex align-items-center">
                              <strong>Loading...</strong>
                              <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                            </div>
                        </div>
                    </div>

                <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                    <?php
                    
            $education = json_decode($row['education'], 1);
            $education = array_unique($education);

            foreach ($education as $key => $value) {
                if(empty($value))
                {
                    unset($education[$key]);
                }
            }

            if(count($education)>1)
            {
              foreach($education as $item)
              {
                echo '
                <div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$item.'</div>
                ';
              }
            }
            else
            {
              echo '
              <div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>
              ';
            }
            ?>
                </div>
                <div class="tab-pane fade" id="bar" role="tabpanel" aria-labelledby="bar-tab">
                    <?php
            $bar_admissions = json_decode($row['bar_admissions'], 1);
            if(count($bar_admissions)>0)
            {
              foreach($bar_admissions as $item)
              {
                echo '
                <div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$item.'</div>
                ';
              }
            }
            else
            {
              echo '
              <div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>
              ';
            }
            ?>
                </div>
                <div class="tab-pane fade" id="court" role="tabpanel" aria-labelledby="court-tab">
                    <?php
            $court_admissions = json_decode($row['court_admissions'], 1);
            if(count($court_admissions)>1)
            {
              foreach($court_admissions as $item)
              {
                echo '
                <div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$item.'</div>
                ';
              }
            }
            else
            {
              echo '
              <div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>
              ';
            }
            ?>
                </div>
                <div class="tab-pane fade" id="acknowledgements" role="tabpanel" aria-labelledby="acknowledgements-tab">
                    <?php
            $acknowledgements = json_decode($row['acknowledgements'], 1);
            if(count($acknowledgements)>1)
            {
              foreach($acknowledgements as $item)
              {
                echo '
                <div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$item.'</div>
                ';
              }
            }
            else
            {
              echo '
              <div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>
              ';
            }
            ?>
                </div>
                <div class="tab-pane fade" id="memberships" role="tabpanel" aria-labelledby="memberships-tab">
                    <?php
            $memberships = json_decode($row['memberships'], 1);
            if(count($memberships)>1)
            {
              foreach($memberships as $item)
              {
                echo '
                <div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$item.'</div>
                ';
              }
            }
            else
            {
              echo '
              <div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>
              ';
            }
            ?>
                </div>
                <div class="tab-pane fade" id="languages" role="tabpanel" aria-labelledby="languages-tab">
                    <?php
                    $languages = @json_decode($row['languages'], 1);
                    if(@count($languages)>0)
                    {
                        foreach($languages as $language)
                        {
                            echo '<div class="card card-body mt-4" style="background: #333; color: #FFF;">'.$language.'</div>';
                        }
                    }
                    else
                    {
                        echo '<div class="card card-body mt-4" style="background: #333; color: #FFF;">N.A.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>