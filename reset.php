	<?php include("include/header.php"); ?>
	<?php if (logged_in()) {
		redirect("admin.php");
	} ?>

	<div class="row">

	</div>
    	<div class="row">
			<div class="col-md-6 col-md-offset-3">
			<?php password_reset(); ?>
				<div class="panel panel-login">
					<div class="panel-heading">
						<div class="row">

							<div class="col-xs-12">
								<h3>Reset Password</h3>
							</div>
						</div>
						<hr>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-12">
								<?php display_message(); ?>
								<form id="register-form" method="post" role="form" >

									<div class="form-group">
										<input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password" required>
									</div>
									<div class="form-group">
										<input type="password" name="confirm_password" id="confirm-password" tabindex="2" class="form-control" placeholder="Confirm Password" required>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-sm-6 col-sm-offset-3">
												<input type="submit" name="reset-password-submit" id="reset-password-submit" tabindex="4" class="form-control btn btn-register" value="Reset Password">
											</div>
										</div>
									</div>
										<input type="hidden" class="hide" name="token_reset" id="token_reset" value="<?php echo token_generator();?>" >
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php include("include/footer.php"); ?>
