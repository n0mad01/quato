<?php
//$yup = "YUP";
//print_r($this->getErrorMsg());
//print_r($this->errorMsg);
//print_r($this->postdata);
?>
<div class="container">
	<div class="row">
		<div class="twelvecol">
			<form id="Register" accept-charset="utf-8" action="/users/register" method="post">
				<?php if (isset($this->errorMsg['invalid']['email'])) {
						echo '<div class="errormsg">' . $this->errorMsg['invalid']['email'] . '</div>';
				} ?>
				<div class="input">
					<label for="email"><?php echo _('email address'); ?>:</label>
					<input id="email" type="text" maxlength="30" name="postdata[email]" 
					<?php echo ' value="' . $this->postdata['email'] . '"'; ?> />
				</div>
				<?php if (isset($this->errorMsg['invalid']['passwords'])) {
						echo '<div class="errormsg">' . $this->errorMsg['invalid']['passwords'] . '</div>';
				} ?>
				<div class="input">
					<label for="password"><?php echo _('Password'); ?>:</label>
					<input id="password" type="password" name="postdata[password]" />
				</div>
				<div class="input">
					<label for="password2"><?php echo _('Password repeat'); ?>:</label>
					<input id="password2" type="password" name="postdata[password2]" />
				</div>
				<input type="submit" value="<?php echo _('Submit'); ?>"/>
			</form>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="fourcol">
			<p>Three columns</p>
        </div>
        <div class="fourcol">
			<p>Three columns</p>
        </div>
        <div class="fourcol last">
			<p>Three columns</p>
		</div>
	</div>
</div>
