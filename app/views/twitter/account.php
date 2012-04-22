<div class="container account">
	<div class="row">
		<div id="account" class="twelvecol">
			<ul class="tabs">
				<li class="tab tab_one">One</li>
				<li class="tab tab_two">Two</li>
				<li class="tab tab_three">Three</li>
			</ul>
			<div class="clear"></div>
			<div id="contentTab">
			    <div id="tabContent">
					Content
			    </div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="ninecol">
			ninecol
			<pre>
			<div id="content">
			</div>
			</pre>
		</div>
		<div class="threecol last">
			<!--div id="accountBox">
			<?php
				if(isset($this->postdata['screen_name'])) {
					echo '<div class="imgBox"><img src="'. $this->postdata['profile_image_url'] . '" alt="'. $this->postdata['screen_name'] .'_image" /></div>';
					echo '<p>'. $this->postdata['screen_name'] .'</p>';

					echo '<div id="accountInfo"></div>';

					echo '<div class="clear"></div>';

				} else {
					echo '<div class="error">'. _('No such user found!') .'</div>'; 
				}
			?>
			</div-->
		</div>
	</div><!-- row -->
</div>

<!--script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script-->
<script type="text/javascript">
	/* GET ACCOUNT/USERDATA */
	jQuery.ajax({
		url : "http://api.twitter.com/1/users/show.json?screen_name=<?php echo $this->postdata['screen_name']; ?>",
		dataType : "jsonp",
		timeout:15000,
		success : function(data) {
			//jQuery('#content').html(dump(data));
			var followers = jQuery('<p>', {text: "<?php echo _('follower').': '; ?>"+ data.followers_count });
			var following = jQuery('<p>', {text: "<?php echo _('following').': '; ?>"+ data.friends_count });
			jQuery('#accountInfo').append(followers, following);
		},
		error : function(e) {
			jQuery('#accountInfo').html(dump(e));
			//alert("Failure!");
		},
	});

	//jQuery.ajax({
		//url : "http://twitter.com/statuses/user_timeline/<?php echo $this->postdata['screen_name']; ?>.json?callback=?",
		////url : "http://api.twitter.com/1/users/show.xml?screen_name=xScited_Adrian',
		////url : "http://twitter.com/users/show/180706419.json?callback=?",
		////url : "http://search.twitter.com/search.json?q=%23science&callback=?", 
		////url : "http://twitter.com/statuses/user_timeline/165085883.json?callback=?",
		//dataType : "json",
		//timeout:15000,
		//success : function(data) {
			//jQuery('#').html(dump(data));
			////for (i=0; i<data.length; i++) {
				////jQuery("#accountInfo").append("<p>" + data[i].text) +"</p>";
				////jQuery("#accountInfo").append("<p>" + data[i].created_at +"</p>");
			////}
		//},
		//error : function() {
			//alert("Failure!");
		//},
	//});



</script>
				<?php /*if (isset($this->errorMsg['invalid']['email'])) {
						echo '<div class="errormsg">' . $this->errorMsg['invalid']['email'] . '</div>';
				}*/ ?>
