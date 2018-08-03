<?php include("include/header.php"); ?>

<?php include("include/nav.php") ;?>


	<div class="jumbotron">
		<?php display_message(); ?>
		<h1 class="text-center">
			<?php if(logged_in()){
				echo "logged in";
			}else {
				redirect("index.php");
			} ?>
		</h1>
	</div>





	<?php include("include/footer.php"); ?>
