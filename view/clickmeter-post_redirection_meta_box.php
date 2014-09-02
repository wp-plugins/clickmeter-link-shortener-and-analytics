<html>
<script type="text/javascript">

window.onload=function(){
	$("input[id=redirection_link_name]").keyup(function() {
		var url_name = $("#redirection_link_name").val();
	    var res = url_name.replace(/[^A-Za-z0-9_]/g, "-");
		$("#resulting_link").text(res);
	});
}

function copyToClipboard() {
	var text = $("#clickmeter_tracking_code").attr("href");
	window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
}

</script>
<body>
	<?php
	$api_key=WPClickmeter::get_option('clickmeter_api_key');
	$tracking_link_id;
	$tracking_link="";
	$post_id = $_GET["post"];
	$post = get_post($post_id);
	$post_title = $post->post_title;
	$permalink = get_permalink($post_id);
	$group_id_TL = WPClickmeter::get_option('clickmeter_TLcampaign_id');

	$blog_name = get_site_url();
	$blog_name = substr($blog_name,7);

	//get information about this post's tracking link if exist
	$link_data = WPClickmeter::get_link($post_id, 1);
	if($link_data!=null){
		$tracking_link_id = $link_data[tracking_link_id];
        $tracking_link_domain = $link_data[domain_id];
        $tracking_link_campaign = $link_data[campaign_id];
        $tracking_link_name = $link_data[link_name];
	}

	if($tracking_link_id!=null){
		$this_post_link = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$tracking_link_id,'GET', null, $api_key);
		if($this_post_link[errors]!=null || $this_post_link[status]==3){
			WPClickmeter::delete_link($post_id, $tracking_link_id);
			$tracking_link_id = null;
		} 
	}
	$tracking_link .= "http://" . $blog_name.'/'.$tracking_link_name;	
	$boGoVal = WPClickmeter::get_option('clickmeter_backOffice_key');

	//Tracking link delete management
	if($_POST["redirection_tracking_link_delete"]=="delete"){
		$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$tracking_link_id, 'DELETE', NULL, $api_key);
		WPClickmeter::delete_link($post_id, $tracking_link_id);
		$tracking_link_id = NULL;
	}

?>
	
<?php if ($post->post_status == "auto-draft") : //CREATE NEW POST BOX?>
	<p>After your post is created, this box will be able to create a custom tracking link.</p>
	
<?php elseif($tracking_link_id!=null) : //UPDATE POST BOX WITH TRACKING LINK?>
	<hr>
	<p style="margin: 4px;">Tracking Link for this post:</p>
	<strong><a id="clickmeter_tracking_code" target="_blank" href="<?php echo $tracking_link ?>"><?php echo $tracking_link ?></a></strong><br>
	<form action="" method="post"></form>
	<form action="" onsubmit="return confirm('Are you sure you want to delete this tracking link?');" method="post">
		<input type="hidden" value="delete" name="redirection_tracking_link_delete">
		<input title="Delete tracking link" type="submit" class='link_button' style="color:#a00" value="Delete"> |
		<a title="Edit tracking link on ClickMeter" target="blank" href="http://mybeta.clickmeter.com/go?val=<?php echo $boGoVal ?>&returnUrl=%2Flinks%2Fedit%2F<?php echo $tracking_link_id ?>">Edit</a> |
		<a title="View tracking link stats on ClickMeter" target="blank" href="http://mybeta.clickmeter.com/go?val=<?php echo $boGoVal ?>&returnUrl=%2FLinks%3FlinkId%3D<?php echo $tracking_link_id ?>">Stats</a> |
		<input title="Copy to clipboard" type="button" class="link_button" value="Copy" onclick="copyToClipboard()"/> |
		<a title="Send an email with this tracking link" target="blank" href="mailto:?subject=<?php echo $post_title; ?>&body=I’d like to share this article with you: <?php echo $tracking_link; ?>">Email</a>
	</form>

<?php else : //UPDATE POST BOX NO TRACKING LINK?>

	<hr>
	<input type="hidden" id="post_url" value="<?php echo $permalink ?>" name="post_url">
	<div id="redirection_tracking_link_button">
		<span style="color:#6B6B6B;font-size: 15px;"><?php echo "http://" . $blog_name . "/"; ?></span>
		<input type=""text style="width:50%" value="" id="redirection_link_name" id="redirection_link_name" maxlength="30"/>
		<input type="button" style="margin-left:20px;" id="save_redirection_link" onclick="callAjax_check_redirection_trackinglink()" class="clickmeter-button-wpstyle" value="Save"/>
		<p><span><strong>Resulting link: </strong><?php echo "http://" . $blog_name . "/"; ?></span><span id="resulting_link"></span></p>
	</div>
	<p id="tl_redirection_creation_success" style="margin: 4px;display:none">Creation successfull. Reload this page or update the post to see created tracking link info.</p>

<?php endif; ?>
</body>
</html>