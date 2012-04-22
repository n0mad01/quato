<div class="container">
	<div class="row">
		<div class="twelvecol">
			<form id="login" accept-charset="utf-8" action="/users/login" method="post">
				<?php if (isset($this->errorMsg['invalid']['notfound'])) {
						echo '<div class="errormsg">' . $this->errorMsg['invalid']['notfound'] . '</div>';
				} ?>
				<div class="input">
					<label for="email"><?php echo _('username or email'); ?>:</label>
					<input id="email" type="text" maxlength="30" name="postdata[email]" 
					<?php echo ' value="' . $this->postdata['email'] . '"'; ?> />
				</div>
				<div class="input">
					<label for="password"><?php echo _('Password'); ?>:</label>
					<input id="password" type="password" name="postdata[password]" />
				</div>
				<div class="checkbox">
					<label for="stayLoggedIn"><?php echo _('stay logged in'); ?>:</label>
					<input id="stayLoggedIn" type="checkbox" name="postdata[stayLoggedIn]" checked="checked" />
				</div>
				<input type="submit" value="<?php echo _('Submit'); ?>"/>
			</form>
		</div>
	</div>
</div>
