<?php include("include/header.php"); ?>

<?php include("include/nav.php"); ?>


<?php if (logged_in()) {
	redirect("admin.php");
} ?>


	<div class="jumbotron">
		<?php display_message(); ?>
		<h1 class="text-center"><?php activate_user(); ?></h1>
	</div>



	<?php include("include/footer.php") ;?>
