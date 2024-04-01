<?php
if(!empty($_SESSION['name']))
{
  header('Location: '.$root.'/people');
  exit();
}
?>

  <?php include 'header.php' ?>

  <div style="background: url(img/bg-header.jpg); min-height: 70vh; background-size: cover; background-attachment: fixed;">
    <div class="p-5 text-center m-auto">
      <img src="img/logo-header-box.png" style="width: 150px;"><br/><br/>
      <h2 class="text-white text-center">#1 Resource for High Quality Data On Top Law Firms and Attorneys.</h2>
      <p class="lead text-white text-center">Over 140,000 Attorneys from the Top AM Law 200<br/>
      Data Updated Daily!</p>
      <div style="max-width: 700px; margin: 0 auto; text-align: center;">
        <a class="btn btn-info btn-lg" href="<?php echo $root; ?>/get-started" style="padding: 10px 50px">GET STARTED</a><br/><br/>
        <em class="text-white">"Access to such a high quality database makes my job better. I'm closing more deals and life is better because I'm making more money." - Daniel S. NY, New York</em>
      </div>
    </div>
  </div>

  <div class="container p-5">
    <h4 class="text-center text-info" id="pricing">
      SIMPLE PRICING: MONTHLY AND YEARLY
    </h4>
    <div style="margin: 0 auto; width: 200px; height: 2px;" class="bg-info"></div>
    <br/>
    <p class="lead text-center">
      Search By: Location, Title, Practice Area, Law School, etc..
      <br/>
      Save 10% when paying yearly!
    </p>
    <div class="row">

      <div class="col-md-4 mb-2">
        <div class="card text-center" style="height: 200px;">
          <div class="card-header">
            FREE
          </div>
          <div class="card-body">
            <p>Limited to 10 results</p>
            <p>&nbsp;</p>
            <a href="<?php echo $root; ?>/get-started" class="btn bg-info mb-5 w-100">GET STARTED</a>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-2">
        <div class="card text-center" style="height: 200px; border: 5px solid #CCC;">
          <div class="card-header">
            MONTHLY ($399/month)
          </div>
          <div class="card-body">
            <p>Unlimited results</p>
            <p>CRM access</p>
            <a href="<?php echo $root; ?>/get-started/?plan=MONTHLY" class="btn bg-info mb-5 w-100">GET STARTED</a>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-2">
        <div class="card text-center" style="height: 200px;">
          <div class="card-header">
            YEARLY ($3999/yr 16.5% discount)
          </div>
          <div class="card-body">
            <p>Unlimited results</p>
            <p>CRM access</p>
            <a href="<?php echo $root; ?>/get-started/?plan=YEARLY" class="btn bg-info mb-5 w-100">GET STARTED</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="container p-5">

    <h4 class="text-center text-info">
      ACCESS THE MOST TRUSTED & HIGHEST QUALITY LEGAL DATABASE
    </h4>
    <div style="margin: 0 auto; width: 200px; height: 2px;" class="bg-info"></div><br/>
    <p class="lead text-center">
      Each Attorney Record Includes All of the Following Data
    </p>
    <br/>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Top AM 200.svg" class="box-icon">
                  </td>
                  <td>
                    Top AM 200 Law Firms
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Location.svg" class="box-icon">
                  </td>
                  <td>
                    Location
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Associates.svg" class="box-icon">
                  </td>
                  <td>
                    Associates, Partners, Counsel
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Practice Area.svg" class="box-icon">
                  </td>
                  <td>
                    Practice Area and Experience
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Speciality.svg" class="box-icon">
                  </td>
                  <td>
                    Specialty
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Law School.svg" class="box-icon">
                  </td>
                  <td>
                    Law School
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/Undergraduate Education.svg" class="box-icon">
                  </td>
                  <td>
                    Undergraduate Education*
                  </td>
                </tr>
              </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body mb-4">
              <table style="width: 100%;">
                <tr>
                  <td style="width: 70px;">
                    <img src="img/icons/JD Year.svg" class="box-icon">
                  </td>
                  <td>
                    JD Year
                  </td>
                </tr>
              </table>
            </div>
        </div>
    </div>

  </div>

  <div class="container p-5">
    
    <h4 class="text-center text-info" id="testimonials">
      SEE WHAT PROFESSIONALS ARE SAYING ABOUT US!
    </h4>
    <div style="margin: 0 auto; width: 200px; height: 2px;" class="bg-info"></div><br/>
    <p class="lead text-center">Read how SotoData has improved recruiters income.</p>
    <br/>

    <div class="bg-info text-white p-4" style="padding-top: 50px;">
      <div class="row">

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">John F., Englewood NJ <img src="img/testimonial/pexels-photo-614810.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"One place for accurate info - has made the recruiting process 1000x easier with excellent quality data."</p>
            </div>
            <div class="card-footer">
              21 February, 2021
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">Shmuel G., New York, NY <img src="img/testimonial/pexels-photo-428331.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"Excellent service at a fair price. So glad I switched over. I've been able to increase my connections, productivity and income tremendously."</p>
            </div>
            <div class="card-footer">
              6th March, 2021
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">Lenka P., Boston, MA. <img src="img/testimonial/pexels-photo-415829.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"Exactly as described! Up to date info delivered to my inbox every other week. I started off with a sample trial, and then once I realized the power of the information, it was a no brainer to go for the subscription plan."</p>
            </div>
            <div class="card-footer">
              9th August, 2021
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">Verona D., Los Angeles, CA. <img src="img/testimonial/pexels-photo-247322.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"I love the ease of use and the quality of information. There's been no shinangans with pricing and the data is always up to date. I've been able to increase my working hours productivity. So happy I found SotoData!"</p>
            </div>
            <div class="card-footer">
              5th April, 2022
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">Dhylles B., Detroit MI. <img src="img/testimonial/pexels-photo-355164.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"Amazing, fresh, clean data! Always delivered reliably. I was surprised to see how much the data changes over the course of a few months. The subscription plans are definitely worth it and the service has made my life a million times easier."</p>
            </div>
            <div class="card-footer">
              8th June. 2022
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-end mb-4">
            <div class="card-body">
              <h6 class="card-title" style="font-weight: bold;">Sarah B., Los Angelos, CA <img src="img/testimonial/pexels-photo-372042.jpeg" class="item-t-bottom"></h6>
              <p class="card-text">"We used to use another service. This is so much more reasonable."</p>
            </div>
            <div class="card-footer">
              1st January, 2023
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

  <div class="container p-5">
    <h4 class="text-center text-info" id="about-us">
      AND WHAT ABOUT US?
    </h4>
    <div style="margin: 0 auto; width: 200px; height: 2px;" class="bg-info"></div>
    <br/>
    <p class="lead text-center">
      We have provided national and international data
      expertise solutions since 2014
    </p>
    <div class="row">
      <div class="col-md-7">
        <div class="bg-light p-4">
          <img src="img/logo-box-about-us.png" style="width: 100px;"><br/>
          <p class="mt-2">
            SotoData has been providing national and international data expertise solutions since 2014. We provide the highest and most accurate quality data solutions at fair prices. We are proud of our unique proprietary technology which provides the most up to date data for legal recruiters and marketing professionals.
          </p>
        </div>
      </div>
      <div class="col-md-5 pt-5">
        <img src="img/img-six-box-right.png">
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>