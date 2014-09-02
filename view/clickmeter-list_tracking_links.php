<html>
<script type="text/javascript">

function copyToClipboard(text) {
	window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
}

</script>
<body>
<?php
	$api_key=WPClickmeter::get_option('clickmeter_api_key');
	$group_id_TL = WPClickmeter::get_option('clickmeter_TLcampaign_id');
	$boGoVal = WPClickmeter::get_option('clickmeter_backOffice_key');

	$blog_name = get_site_url();
	$blog_name = substr($blog_name,7);

	//Tracking link delete management
	if(isset($_POST["tracking_link_delete"])){
		$tracking_link_id_delete = $_POST["tracking_link_delete"];
		$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$tracking_link_id_delete, 'DELETE', NULL, $api_key);
		WPClickmeter::delete_link(null, $tracking_link_id_delete);
	}

	$stored_links = WPClickmeter::get_all_links();
	$total_links = sizeof($stored_links);
	$total_pages = intval($total_links/10)+1;
	$current_page = 1;
	if(isset($_GET["paged"])) $current_page = $_GET["paged"];
	$current_page_links = array();
	if($current_page == 1){
		$start_index=0;	
		$end_index=10;
	}else{
		$start_index = 0 + ($current_page-1) * 10;
		$end_index = 10 + ($current_page-1) * 10;
	}
	
	for($start_index;$start_index<$end_index;$start_index++){
		if($stored_links[$start_index]!=null) $current_page_links[] = $stored_links[$start_index];
	}
	add_thickbox();
?>
	<hr>
	<br>
	<a target="_blank" href="<?php echo esc_url(add_query_arg(array('page' => 'clickmeter-link-shortener-and-analytics/view/clickmeter-new_tracking_link.php'), admin_url('admin.php'))); ?>">Create Tracking Link</a> | 
	<a target="_blank" href="http://mybeta.clickmeter.com/go?val=<?php echo $boGoVal; ?>&returnUrl=%2FLinks%23campaignId%3D<?php echo $group_id_TL; ?>%26rows%3D10%2614days">View Tracking Links on ClickMeter</a><br><br>
	<div class="clickmeter_tablenav">
		<table width="100%">
		<tr>
			<td width="25%">
				<select style="width:70%;" name="cm_bulk_actions">
					<option value="null">Bulk Actions</option>
					<option value="bulk_tl_delete">Delete</option>
					<option value="bulk_tl_change_dest_url">Update Destination URL</option>
				</select>
				<input id="cm_apply_bulk_action" class="clickmeter-button-wpstyle" type="button" value="Apply"/>
			</td>
			<td width="60%">
				<div id="bulk_action_progress" style="padding: 5px 0px 5px 0px; display:none">
					<table>
						<tr><td><div class="spinner_cm"></div></td><td><span>Please wait some seconds.</span></td></tr>
					</table>
				</div>
				<div id="cm_bulk_edit_url" style="display:none">
					<span style="color:#6B6B6B;font-size: 15px;">New Destination URL: </span>
					<input type="text" style="width:55%" value="http://" id="cm_dest_url" id="cm_dest_url" maxlength="50"/><span style="display:none" class="error" id="error_url"></span>
					<input type="button" id="cm_bulk_edit_url_confirm" value="Update" class="clickmeter-button"/>
				</div>
			</td>
			<td width="20%">
				<div class="clickmeter_tablenav_pages">
					<?php if($current_page!=1): ?>
					<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page-1; ?>">«</a>
					<?php endif; ?>
					<!--<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page-1; ?>"><?php echo $current_page-1; ?></a>-->
					<span class="cm_displaying_num"><?php echo $current_page ." of ". $total_pages; ?></span>
					<!--<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page+1; ?>"><?php echo $current_page+1; ?></a>-->
					<?php if($current_page!=$total_pages): ?>
					<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page+1; ?>">»</a>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		</table>
	</div>
	<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th class="manage-column column-cb check-column"><input type="checkbox" id="clickmeter_check_all"/></th>
				<th class="manage-column" width="35%">Tracking Link</th>
				<th class="manage-column" width="25%">Destination URL</th>
				<th class="manage-column" width="25%">Campaign Name</th>
				<th class="manage-column" width="15%">Creation Date</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($current_page_links as $link_data) {
				$tracking_link_id = $link_data[tracking_link_id];
				$tracking_link = $link_data[tracking_code];
        		$tracking_link_campaign = $link_data[campaign_name];
        		$dest_url = $link_data[destination_url];
        		$tracking_link_domain = $link_data[domain_id];
        		$redirection_flag = $link_data[is_redirection_link];
        		$alternative_url = $tracking_link;
        		if($redirection_flag==1){
        			$tracking_link = "http://" . $blog_name.'/'.$link_data[link_name];
        		} 
				echo '
				<tr>
					<td><input id="cb-select-'.$tracking_link_id.'" type="checkbox" value="'.$tracking_link_id.'" name="tracking_link_rows"/></td>
					<td>	
						<strong><a target="_blank" href="'.$tracking_link.'">'.$tracking_link.'</a></strong><br>
						<form action="" onsubmit="return confirm("Are you sure you want to delete this tracking link?");" method="post">
							<input type="hidden" value="'.$tracking_link_id.'" name="tracking_link_delete">
							<input title="Delete tracking link" type="submit" class="link_button" style="color:#a00" value="Delete"> |
							<a style="text-decoration:underline" title="Edit tracking link on ClickMeter" target="blank" href="http://mybeta.clickmeter.com/go?val='.$boGoVal.'&returnUrl=%2Flinks%2Fedit%2F'.$tracking_link_id.'">Edit</a> |
							<a style="text-decoration:underline" title="View tracking link stats on ClickMeter " target="blank" href="http://mybeta.clickmeter.com/go?val='.$boGoVal.'&returnUrl=%2FLinks%3FlinkId%3D'.$tracking_link_id.'">Stats</a> |
							<a style="text-decoration:underline" title="Get QR code" target="_blank" href="'.$alternative_url.'.qr">QR</a> |
							<input title="Copy to clipboard" type="button" class="link_button" value="Copy" onclick="copyToClipboard(\''.$tracking_link.'\')"/>						
						</form>
					</td>
					<td><a href="'.$dest_url.'" target="_blank">'.$dest_url.'</a></td>
					<td>'.$tracking_link_campaign.'</td>
					<td>'.$link_data[time].'</td>
				</tr>';
			}?>
		</tbody>
	</table>
	<div class="clickmeter_tablenav_pages">
		<?php if($current_page!=1): ?>
		<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page-1; ?>">«</a>
		<?php endif; ?>
		<!--<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page-1; ?>"><?php echo $current_page-1; ?></a>-->
		<span class="cm_displaying_num"><?php echo $current_page ." of ". $total_pages; ?></span>
		<!--<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page+1; ?>"><?php echo $current_page+1; ?></a>-->
		<?php if($current_page!=$total_pages): ?>
		<a class="cm_page_numbers" href="?page=clickmeter-link-shortener-and-analytics/view/clickmeter-list_tracking_links.php&amp;paged=<?php echo $current_page+1; ?>">»</a>
		<?php endif; ?>
	</div>

	
</body>
</html>