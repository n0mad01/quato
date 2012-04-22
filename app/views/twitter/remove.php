<?php
//print_r($this->getErrorMsg());
//print_r($this->errorMsg);
//print_r($this->postdata);

?>
<div class="container">
	<div class="row">
		<div class="twelvecol">
			<?php if(isset($this->postdata['msg'])) { echo '<div class="errorMSG">'. $this->postdata['msg'] .'</div>'; } ?>
			<form id="removeTwitterAccounts" name="removeTwitterAccounts" accept-charset="utf-8" action="/twitter/remove" method="post">
				<?php /*if (isset($this->errorMsg['invalid']['email'])) {
						echo '<div class="errormsg">' . $this->errorMsg['invalid']['email'] . '</div>';
				}*/ ?>

				<label for="removeTwitterAccount"><?php echo _('choose the Twitter-account you want to remove'); ?>:</label>
				<select name="postdata[user]" id="removeTwitterAccount">
					<?php
						foreach($this->postdata['twitterusers'] as $user) {
							echo '<option value="'. $user . '">'. $user .'</option>';
						}
					?>
				</select>
				<input type="submit" value="<?php echo _('Submit'); ?>"/>
			</form>
		</div>
	</div>
</div>
