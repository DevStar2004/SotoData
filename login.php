<?php
if(!empty($_SESSION['name']))
{
  header('Location: '.$root.'/people');
  exit();
}

include 'header.php';

?>

	<div style="padding-left: 40px; padding-right: 40px; padding-top: 70px; padding-bottom: 70px; background: #EEE;">
		<?php
		if(!empty($_POST['email']))
		{
			$q = $pdo->prepare('SELECT * FROM `customers` WHERE `email`=? AND `password`=?');
			$q->execute(array($_POST['email'], $_POST['password']));
			if($q->rowcount()>0)
			{
				$customer = $q->fetch(PDO::FETCH_ASSOC);
				$_SESSION = $customer;
				header('Location: '.$root.'/people');
				exit();
			}
			else
			{
				?>
				<div class="alert alert-warning">
				  <strong>Error!</strong> Incorrect login details provided.
				</div>
				<?php
			}
		}
		?>
		<div style="margin: 0 auto; padding: 10px 20px; max-width: 500px; padding-bottom: 40px !important;" class="card">
			<h2>Login</h2>
			<form accept="" method="post">
				<label>Email</label>
				<input type="text" class="form-control mb-2" name="email">
				<label>Password</label>
				<input type="password" class="form-control mb-2" name="password">
				<button class="btn btn-default mt-2" style="float: right;">Forgot Password</button>
				<button class="btn btn-light btn-outline-dark mt-2">LOGIN</button>
				<div style="clear: both;"></div>
			</form>
		</div>
	</div>
	
	<?php include 'footer.php'; ?>

	<script type="text/javascript" src="<?php echo $root; ?>/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/owl.carousel.min.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/owl.carousel2.thumbs.js"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/main.js?ver=4.8.4"></script>
	<script type="text/javascript" src="<?php echo $root; ?>/js/sweetalert.min.js"></script>
</body>
</html>
