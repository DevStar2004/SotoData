<?php

include 'header.php';
include 'stripe-php/init.php';

if(isset($_GET['session']))
{
	$stripe = new \Stripe\StripeClient($stripe_sk);
	
	$res = @$stripe->checkout->sessions->retrieve(
	  $_GET['session'],
	  []
	);

	if($res['payment_status'] == 'paid')
	{

		if(!empty($_SESSION['id']))
		{

			if($res['amount_total'] == 39900)
			{
				$plan = 'MONTHLY';
			}
			else
			{
				$plan = 'YEARLY';
			}

			$q = $pdo->prepare('UPDATE `customers` SET `account_type`=? WHERE `id`=?');
			$q->execute(array($plan, $_SESSION['id']));
			$_SESSION['account_type'] = $plan;
			header('Location: '.$root.'/people');
			exit();
		}

		$q = $pdo->prepare('INSERT INTO `customers` VALUES (?,?,?,?,?,?)');
		$q->execute(array($_SESSION['signup']['name'], $_SESSION['signup']['email'], $_SESSION['signup']['password'], $_SESSION['signup']['plan'], '{"show_profile_image":"true","show_firm_name":"true","show_primary_location":"true","show_practice_area":"true","show_actions":"true"}', NULL));

		$q = $pdo->prepare('SELECT * FROM `customers` ORDER BY `id` DESC LIMIT 1');
		$q->execute(array());

		$customer = $q->fetch(PDO::FETCH_ASSOC);

		$_SESSION = $customer;

		header('Location: '.$root.'/people');
		exit();

	}

}

if(!empty($_SESSION['name']))
{
  header('Location: '.$root.'/people');
  exit();
}
?>

	<div style="padding-left: 40px; padding-right: 40px; padding-top: 70px; padding-bottom: 70px; background: #EEE;">

		<div style="margin: 0 auto; padding: 10px 20px; max-width: 500px;" class="card">
			<h2>Get started</h2>

			<?php

			if(!empty($_POST['name']))
			{

				if($_POST['plan'] == 'FREE')
				{
					$q = $pdo->prepare('INSERT INTO `customers` VALUES (?,?,?,?,?,?)');
					$q->execute(array($_POST['name'], $_POST['email'], $_POST['password'], 'FREE', '{"show_profile_image":"true","show_firm_name":"true","show_primary_location":"true","show_practice_area":"true","show_actions":"true"}', NULL));

					$q = $pdo->prepare('SELECT * FROM `customers` ORDER BY `id` DESC LIMIT 1');
					$q->execute(array());

					$customer = $q->fetch(PDO::FETCH_ASSOC);

					$_SESSION = $customer;

					header('Location: '.$root.'/people');
					
				}
				else
				{

					foreach ($_POST as $key => $value) {
						$_SESSION['signup'][$key] = $value;
					}

					if($_POST['plan'] == 'MONTHLY')
					{
						header('Location: '.$stripe_monthly);
					}

					if($_POST['plan'] == 'YEARLY')
					{
						header('Location: '.$stripe_yearly);
					}

				}

			}
			?>

			<?php
			if(empty($_POST['name']))
			{
				?>
				<form accept="" method="post" id="signup">
					<label>Full name</label>
					<input type="text" class="form-control" name="name" required><br/>
					<label>Email</label>
					<input type="text" class="form-control" name="email" required><br/>
					<label>Password</label>
					<input type="password" class="form-control" name="password" required><br/>
					<label>Repeat password</label>
					<input type="password" class="form-control" name="repeat_password" required><br/>
					<label>Plan</label>
					<select class="form-control" name="plan">
						<option value="FREE">FREE</option>
						<option value="MONTHLY" <?php if(@$_GET['plan'] == 'MONTHLY') { echo ' selected'; } ?>>MONTHLY - $399/month</option>
						<option value="YEARLY" <?php if(@$_GET['plan'] == 'YEARLY') { echo ' selected'; } ?>>YEARLY $3999/yr 16.5% discount</option>
					</select>
					<button class="btn btn-light btn-outline-dark mt-2" style="float: right;" id="submitBtn">NEXT</button>
					<div style="clear: both;"></div>
				</form>
				<?php
			}
			?>

		</div>
	</div>
	
	<?php include 'footer.php'; ?>

	<script type="text/javascript" src="<?php echo $root; ?>/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/owl.carousel.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/owl.carousel2.thumbs.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/main.js?ver=4.8.4"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/sweetalert.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){

			$("form").submit(function(){
				$('#submitBtn').text('Please wait...');
			});

		});
	</script>
</body>
</html>
