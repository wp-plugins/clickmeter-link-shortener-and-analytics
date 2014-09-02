<?php
require_once( plugin_dir_path( __FILE__ ) . 'clickmeter.php');
require_once( plugin_dir_path( __FILE__ ) . 'clickmeter_views.class.php');

add_thickbox(); //enable opening modal popup

//API KEY VALIDATION
$api_key = "";

$args = array(
'posts_per_page' => -1,
'post_type' => 'post',
'post_status' => array('publish', 'private', 'future'),
'orderby' => 'title',
'order' => 'ASC'
);
$posts_array = get_posts( $args );

$args = array(
'posts_per_page' => -1,
'post_type' => 'page',
'post_status' => array('publish', 'private', 'future'),
'orderby' => 'title',
'order' => 'ASC'
);
$pages_array = get_posts( $args );

function test_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

//CODE FOR ACCOUNT!
if ($_SERVER["REQUEST_METHOD"] == "POST" && array_key_exists("API_key",$_POST)) {
	if (empty($_POST["API_key"])) {
		$apikeyErr = "*API-Key is required";
	} else {
		$api_key = test_input($_POST["API_key"]);
		$jsonoutput = WPClickmeter::api_request('http://apiv2.clickmeter.com/account', 'GET', NULL, $api_key);
		WPClickmeter::store_option( 'clickmeter_debug_init', $jsonoutput);		
		if (!preg_match("/^[a-zA-Z0-9]{8}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{12}$/",$api_key)) {
			$apikeyErr = "*Invalid API-Key"; 
		}if(empty($jsonoutput)) {
			$apikeyErr = "*Invalid API-Key"; 
		}
		else{
			WPClickmeter::store_option( 'clickmeter_api_key', $api_key);
			$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/account', 'GET', NULL, $api_key);
			$boGoVal = $json_output[boGoVal];
			if($boGoVal!=NULL) WPClickmeter::store_option( 'clickmeter_backOffice_key', $boGoVal);

			$blog_name = get_site_url();
			$blog_name = substr($blog_name,7);

			//look for TP campaign into WP database
			$group_id_TP = WPClickmeter::get_option('clickmeter_TPcampaign_id');
			//if not present
			if($group_id_TP==null){
				//search it into clickmeter and save it
				$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com:80/groups?status=active&_expand=true', 'GET', NULL, $api_key);
				$groups = $json_output[entities];
				foreach ($groups as $group) {
					if(strcasecmp($group[name], $blog_name.'-views')==0){
						$group_id_TP = $group[id];
						WPClickmeter::store_option( 'clickmeter_TPcampaign_id', $group_id_TP );	
						break;
					} 
				}
				//if not present in clickmeter, create it
				if($group_id_TP == null){
					//Create campaign for wordpress pixels
					$body=array('name'=>$blog_name.'-views');
					$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups','POST', json_encode($body), $api_key);
					$group_id_TP = $json_output[id];
					WPClickmeter::store_option( 'clickmeter_TPcampaign_id', $group_id_TP );
				}
			}

			//look for TL campaign into WP database
			$group_id_TL = WPClickmeter::get_option('clickmeter_TLcampaign_id');
			//if not present
			if($group_id_TL==null){
				//search it into clickmeter and save it
				$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com:80/groups?status=active&_expand=true', 'GET', NULL, $api_key);
				$groups = $json_output[entities];
				foreach ($groups as $group) {
					if(strcasecmp($group[name],$blog_name.'-links')==0){
						$group_id_TL = $group[id];
						WPClickmeter::store_option( 'clickmeter_TLcampaign_id', $group_id_TL );	
						break;	
					} 
				}
				//if not present in clickmeter, create it
				if($group_id_TL == null){
					//Create campaign for wordpress tracking links
					$body=array('name'=>$blog_name.'-links');
					$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups','POST', json_encode($body), $api_key);
					$group_id_TL = $json_output[id];
					WPClickmeter::store_option( 'clickmeter_TLcampaign_id', $group_id_TL );
				}
			}

			//look for 404_reports campaign into WP database
			$group_id_404_reports = WPClickmeter::get_option('clickmeter_404_reports_campaign_id');
			//if not present
			if($group_id_404_reports==null){
				//search it into clickmeter and save it
				$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com:80/groups?status=active&_expand=true', 'GET', NULL, $api_key);
				$groups = $json_output[entities];
				foreach ($groups as $group) {
					if(strcasecmp($group[name],$blog_name.'-404 reports')==0){
						$group_id_404_reports = $group[id];
						WPClickmeter::store_option( 'clickmeter_404_reports_campaign_id', $group_id_404_reports );	
						break;	
					} 
				}
				//if not present in clickmeter, create it
				if($group_id_404_reports == null){
					//Create campaign for wordpress tracking links
					$body=array('name'=>$blog_name.'-404 reports');
					$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups','POST', json_encode($body), $api_key);
					$group_id_404_reports = $json_output[id];
					WPClickmeter::store_option( 'clickmeter_404_reports_campaign_id', $group_id_404_reports );
				}
			}
			WPClickmeter::store_option( 'clickmeter_startup_create_TP', $_POST["startup_create_TP"]);
			
			if($_POST["startup_create_TP"]=="true"){
				WPClickmeter::store_option("clickmeter_workinprogress_flag", "true");
				$inclusion_list = array();
				foreach ($posts_array as $post) {
					$inclusion_list[] = $post->ID;
				}
				foreach ($pages_array as $page) {
					$inclusion_list[] = $page->ID;
				}
				WPClickmeter::store_option( 'clickmeter_inclusion_list', $inclusion_list);
				//update flag active pixels into database
				WPClickmeter::store_option( 'clickmeter_pixel_flag', 1 );
				WPClickmeter::store_option('clickmeter_pixel_new_articles', 1 );
				WPClickmeter::store_option("track_404_flag", 0);
				WPClickmeter::store_option("link_cloak_flag", 0);
				WPClickmeter::store_option("clickmeter_wp_redirection_flag", 0);
				WPClickmeter::store_option("clickmeter_default_redirection_type", "301");
				echo '<script>callAjaxTP_init_creation();</script>';
				echo '<meta http-equiv="refresh" content="0">';
			}else{
				WPClickmeter::store_option("clickmeter_workinprogress_flag", "false");
				WPClickmeter::store_option( 'clickmeter_pixel_flag', 0 );
				WPClickmeter::store_option('clickmeter_pixel_new_articles', 0 );
				WPClickmeter::store_option( 'clickmeter_inclusion_list', array());
				WPClickmeter::store_option( 'clickmeter_exclusion_list', array());
				WPClickmeter::store_option("track_404_flag", 0);
				WPClickmeter::store_option("link_cloak_flag", 0);
				WPClickmeter::store_option("clickmeter_wp_redirection_flag", 0);
				WPClickmeter::store_option("clickmeter_default_redirection_type", "301");
				//workaround to reload the page
				echo '<meta http-equiv="refresh" content="0">';
			}

			// //I don't have conversion ids in the database, search on ClickMeter by blog name.
			// $active_conversions = WPClickmeter::api_request('http://apiv2.clickmeter.com/conversions?status=active&_expand=true', 'GET', NULL, $api_key);
			// $conversion1_name = "";
			// //conversion 1
			// foreach ($active_conversions[entities] as $conversion) {
			// 	if((preg_match("#".$blog_name."#", $conversion[name]) && $conversion2_name!=$conversion[name])){
			// 		WPClickmeter::store_option("clickmeter_conversionId1", $conversion[id]);
			// 		WPClickmeter::store_option("clickmeter_conversionName1", $conversion[name]);
			// 		WPClickmeter::store_option("clickmeter_conversionTarget1", array("post", "page"));
			// 		$conversion1_name = $conversion[name];
			// 	}
			// }

			// //conversion 2
			// foreach ($active_conversions[entities] as $conversion) {
			// 	if((preg_match("#".$blog_name."#", $conversion[name]) && $conversion1_name!=$conversion[name])){
			// 		WPClickmeter::store_option("clickmeter_conversionId2", $conversion[id]);
			// 		WPClickmeter::store_option("clickmeter_conversionName2", $conversion[name]);
			// 		WPClickmeter::store_option("clickmeter_conversionTarget2", array("post", "page"));
			// 	}
			// }
		}
	}
}

if($_POST["API_key_delete"]!=NULL){
	echo '<script>callAjaxTP_delete();</script>';
	echo '<meta http-equiv="refresh" content="0">';
}

$api_key=WPClickmeter::get_option('clickmeter_api_key');

if($api_key!=NULL){
	function hideAPIKey($value) {
		$maskedValue = $value;
		$lenght = strlen($maskedValue);
		for($i=3;$i<$lenght-3;$i++){
			$maskedValue[$i]='*';
		}
		return $maskedValue;
	}
	
	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/account', 'GET', NULL, $api_key);
	$username = $json_output[firstName];
	$lastname = $json_output[lastName];
	$email = $json_output[email];
	$companyName = $json_output[companyName];
	$companyRole = $json_output[companyRole];
	$phone = $json_output[phone];
	$boGoVal = $json_output[boGoVal];

	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/account/plan', 'GET', NULL, $api_key);
	$plan_type = $json_output[name];
	$maximumDatapoints = $json_output[maximumDatapoints];
	$monthlyEvents = $json_output[monthlyEvents];
	$billingPeriodStart = $json_output[billingPeriodStart];
	$billingPeriodStart = substr($billingPeriodStart,0,4).'/'.substr($billingPeriodStart,4,2).'/'.substr($billingPeriodStart,6,2);
	$billingPeriodEnd = $json_output[billingPeriodEnd];
	$billingPeriodEnd = substr($billingPeriodEnd,0,4).'/'.substr($billingPeriodEnd,4,2).'/'.substr($billingPeriodEnd,6,2);
	$usedMonthlyEvents = $json_output[usedMonthlyEvents];

	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/count', 'GET', NULL, $api_key);
	$used_datapoints = $json_output[count];

	//GET TRACKING PIXELS LIST
	$group_id_TP = WPClickmeter::get_option('clickmeter_TPcampaign_id');
	$group_id_TL = WPClickmeter::get_option('clickmeter_TLcampaign_id');
	$group_id_404_reports = WPClickmeter::get_option('clickmeter_404_reports_campaign_id');

	//TRACKING PIXELS
	if($_POST["pixels_flags"]=="true"){
		WPClickmeter::store_option("clickmeter_workinprogress_flag", "true");

		WPClickmeter::store_option( 'clickmeter_pixel_flag', 1 );

		if($_POST["excluded_list"]!=NULL)
			WPClickmeter::store_option( 'clickmeter_exclusion_list', $_POST["excluded_list"]);
		if($_POST["excluded_list"]==NULL and $_POST["included_list"]!=NULL)
			WPClickmeter::store_option( 'clickmeter_exclusion_list', array());

		if($_POST["included_list"]!=NULL)
			WPClickmeter::store_option( 'clickmeter_inclusion_list', $_POST["included_list"]);
		if($_POST["included_list"]==NULL and $_POST["excluded_list"]!=NULL)
			WPClickmeter::store_option( 'clickmeter_inclusion_list', array());

		if($_POST["new_article_default"]=="true"){
			WPClickmeter::store_option('clickmeter_pixel_new_articles', 1 );
		}
		if($_POST["new_article_default"]=="false"){
			WPClickmeter::store_option('clickmeter_pixel_new_articles', 0 );
		}

		echo '<script>callAjaxTP_savechanges();</script>';
	}

	if($_POST["pixels_flags"]=="false"){

		WPClickmeter::store_option("clickmeter_workinprogress_flag", "true");

		WPClickmeter::store_option( 'clickmeter_pixel_flag', 0 );
		WPClickmeter::store_option('clickmeter_pixel_new_articles', 0 );

		WPClickmeter::store_option( 'clickmeter_inclusion_list', array());
		WPClickmeter::store_option( 'clickmeter_exclusion_list', array());

		echo '<script>callAjaxTP_savechanges();</script>';
	}

	//CONVERSIONS
	$conversion1_id = WPClickmeter::get_option('clickmeter_conversionId1');
	$conversion1_name = WPClickmeter::get_option("clickmeter_conversionName1");
	$conversion2_id = WPClickmeter::get_option('clickmeter_conversionId2');
	$conversion2_name = WPClickmeter::get_option("clickmeter_conversionName2");

	//Create conversion
	if($_POST["conversion_type"]!=null){
		$conversion_type= $_POST["conversion_type"];
		if($conversion_type == "null"){
			$conversionErr = " *Please choose a conversion type";
		}
		elseif($_POST["conversion_target_list"]==NULL || empty($_POST["conversion_target_list"])){
			$conversionErr = " *Please choose a conversion target";
		}else{
			WPClickmeter::store_option("clickmeter_workinprogress_flag", "true");
			WPClickmeter::store_option("clickmeter_lastconversion_target", $_POST["conversion_target_list"]);
			if(preg_match("/existing_conversion/", $conversion_type)){
				//ASSOCIATE EXISTING CONVERSION
				preg_match("/[0-9]*$/",$conversion_type, $match);
				$conversion_id = "";
				if(!empty($match)) $conversion_id = $match[0];
				echo '<script>callAjax_associate_conversion('.$conversion_id.');</script>';	
			}else{
				//CREATE CONVERSION CODE
				WPClickmeter::store_option("clickmeter_lastconversion_type", $conversion_type);
				echo '<script>callAjax_create_conversion();</script>';	
			}
		}
	}
	
	//Delete conversion
	if($_POST["conversion_delete"]!=NULL){
		WPClickmeter::store_option("clickmeter_workinprogress_flag", "true");
		if($_POST["conversion_delete"] == "1"){
			echo '<script>callAjax_delete_first_conversion();</script>';
		}
		if($_POST["conversion_delete"] == "2"){
			echo '<script>callAjax_delete_second_conversion();</script>';
		}
	}
	

	//Check for existing conversions in ClickMeter
	//Case 1: I have a conversion ids in the database and I search it on ClickMeter
	if($conversion1_id!=null){
		$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/conversions/'.$conversion1_id, 'GET', NULL, $api_key);
		if(!array_key_exists("deleted",$json_output)){
			WPClickmeter::store_option("clickmeter_conversionName1", $json_output[name]);
			$conversion1_name = $json_output[name];
		}
	}
	if($conversion2_id!=null){
		$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/conversions/'.$conversion2_id, 'GET', NULL, $api_key);
		if(!array_key_exists("deleted",$json_output)){
			WPClickmeter::store_option("clickmeter_conversionName2", $json_output[name]);
			$conversion2_name = $json_output[name];
		}
	}
	
	//Case 2: I don't have conversion ids in the database, search on ClickMeter by blog name.
	$blog_name = get_site_url();
	$blog_name = substr($blog_name,7);
	$active_conversions = WPClickmeter::api_request('http://apiv2.clickmeter.com/conversions?status=active&_expand=true', 'GET', NULL, $api_key);
	// if($conversion1_id==null){
	// 	foreach ($active_conversions[entities] as $conversion) {
	// 		if((preg_match("#".$blog_name."#", $conversion[name]) && $conversion2_name!=$conversion[name])){
	// 			WPClickmeter::store_option("clickmeter_conversionId1", $conversion[id]);
	// 			WPClickmeter::store_option("clickmeter_conversionName1", $conversion[name]);	
	// 		}
	// 	}
	// }

	
	// if($conversion2_id==null){
	// 	foreach ($active_conversions[entities] as $conversion) {
	// 		if((preg_match("#".$blog_name."#", $conversion[name]) && $conversion1_name!=$conversion[name])){
	// 			WPClickmeter::store_option("clickmeter_conversionId2", $conversion[id]);
	// 			WPClickmeter::store_option("clickmeter_conversionName2", $conversion[name]);	
	// 		}
	// 	}
	// }

	$conversion1_id = WPClickmeter::get_option('clickmeter_conversionId1');
	$conversion1_name = WPClickmeter::get_option("clickmeter_conversionName1");
	$conversion1_target = WPClickmeter::get_option("clickmeter_conversionTarget1");
	$conversion2_id = WPClickmeter::get_option('clickmeter_conversionId2');
	$conversion2_name = WPClickmeter::get_option("clickmeter_conversionName2");
	$conversion2_target = WPClickmeter::get_option("clickmeter_conversionTarget2");

	$pixel_value=WPClickmeter::get_option('clickmeter_pixel_flag');
	$pixel_default_value = WPClickmeter::get_option('clickmeter_pixel_new_articles');	
	$exclusion_list = WPClickmeter::get_option("clickmeter_exclusion_list");
	$inclusion_list = WPClickmeter::get_option("clickmeter_inclusion_list");
	

	//TRACKING LINK SETTINGS
	
	//DOMAINS
	$wordpress_domain_id = 1597;
	$default_domainId = WPClickmeter::get_option("clickmeter_default_domainId");
	if($_POST['domain_list']!=null){
		$default_domainId = $_POST['domain_list'];
		WPClickmeter::store_option("clickmeter_default_domainId", $default_domainId);
		if($default_domainId == $wordpress_domain_id){
			WPClickmeter::store_option("clickmeter_default_domainName", $blog_name);
		}else{
			$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com:80/domains/'.$default_domainId, 'GET', NULL, $api_key);
			WPClickmeter::store_option("clickmeter_default_domainName", $json_output[name]);	
		}
	}
	
	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/domains?_expand=true', 'GET', NULL, $api_key);
	$clickmeter_domains = $json_output[entities];
	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/domains?type=go&_expand=true', 'GET', NULL, $api_key);
	foreach ($json_output[entities] as $goDomain) {
		$clickmeter_domains[] = $goDomain;
	}
	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/domains?type=personal&_expand=true', 'GET', NULL, $api_key);
	foreach ($json_output[entities] as $personalDomain) {
		$clickmeter_domains[] = $personalDomain;
	}
	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/domains?type=dedicated&_expand=true', 'GET', NULL, $api_key);
	foreach ($json_output[entities] as $dedicated_domain) {
		$clickmeter_domains[] = $dedicated_domain;
	}

	//if default is null, take first returned domain as default domain
	if($default_domainId == null){
		WPClickmeter::store_option("clickmeter_default_domainId", $wordpress_domain_id);
		WPClickmeter::store_option("clickmeter_default_domainName", $blog_name);
	} 

	//CAMPAIGNS
	$default_campaignId = WPClickmeter::get_option("clickmeter_default_campaignId_links");
	if($default_campaignId==null) {
		$default_campaignId = WPClickmeter::get_option('clickmeter_TLcampaign_id');
		WPClickmeter::store_option("clickmeter_default_campaignId_links", $default_campaignId);
	}
	if($_POST['campaign_list']!=null){
		$default_campaignId = $_POST['campaign_list'];
		WPClickmeter::store_option("clickmeter_default_campaignId_links", $default_campaignId);
	}

	$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups?status=active&_expand=true', 'GET', NULL, $api_key);
	$groups = $json_output[entities];


	//CONVERSIONS
	if($_POST['first_conversion_list']!=null){
		$default_firstConv_links = $_POST['first_conversion_list'];
		WPClickmeter::store_option("clickmeter_default_firstConv_links", $default_firstConv_links);	
	}
	$default_firstConv_links = WPClickmeter::get_option("clickmeter_default_firstConv_links");


	if($_POST['second_conversion_list']!=null){
		$default_secondConv_links = $_POST['second_conversion_list'];
		WPClickmeter::store_option("clickmeter_default_secondConv_links", $default_secondConv_links);
	}
	$default_secondConv_links = WPClickmeter::get_option("clickmeter_default_secondConv_links");

	//REDIRECTION TYPE
	if($_POST['redirection_type_list']!=null){
		$default_redirection_type = $_POST['redirection_type_list'];
		WPClickmeter::store_option("clickmeter_default_redirection_type", $default_redirection_type);
	}
	$default_redirection_type = WPClickmeter::get_option("clickmeter_default_redirection_type");



	//LINK CLOAKING
	if($_POST['link_cloak_flag']=="true"){
		WPClickmeter::store_option("link_cloak_flag", 1);
	}
	if($_POST['link_cloak_flag']=="false"){
		WPClickmeter::store_option("link_cloak_flag", 0);
	}
	$link_cloak_flag = WPClickmeter::get_option("link_cloak_flag");


	//BLOG DOMAIN REDIRECTION LINKS
	// if($_POST['wp_redirection_flag']=="true"){
	// 	WPClickmeter::store_option("clickmeter_wp_redirection_flag", 1);
	// }
	// if($_POST['wp_redirection_flag']=="false"){
	// 	WPClickmeter::store_option("clickmeter_wp_redirection_flag", 0);
	// }
	// $wp_redirection_flag = WPClickmeter::get_option("clickmeter_wp_redirection_flag");

	//404 TRACKING
	if($_POST['track_404_flag']=="true"){
		WPClickmeter::store_option("clickmeter_track_404_flag", 1);
		//create 404 tracking link
		$clickmeter_404_tl = WPClickmeter::get_option("clickmeter_404_tl");
		$url_404_name = "wordpress-404";
		$url_404 = $_POST['custom_404_page'];
		WPClickmeter::store_option("clickmeter_track_404_url", $url_404);
		$url_404_name = WPClickmeter::generateRandomString() ."-". $url_404_name;
		if($clickmeter_404_tl==null){
			$body=array('type'=> 0,
				'title'=> "WordPress 404",
				'groupId'=> $group_id_404_reports,
				'name'=> $url_404_name,
				'typeTL'=>array('domainId'=> WPClickmeter::get_option("clickmeter_default_domainId"),'url'=> $url_404)
			);
			$created_link = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints','POST', json_encode($body), $api_key);
			$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$created_link[id], 'GET', NULL, $api_key);
			WPClickmeter::store_option("clickmeter_404_tlID", $json_output[id]);
			WPClickmeter::store_option("clickmeter_404_tl", $json_output[trackingCode]);
		}else{
			$tl_404_id = WPClickmeter::get_option("clickmeter_404_tlID");
			$json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$tl_404_id,'GET', null, $api_key);
			if(strcasecmp($json_output[typeTL][url],$url_404)==0){
				WPClickmeter::store_option("clickmeter_404_tlID", $json_output[id]);
				WPClickmeter::store_option("clickmeter_404_tl", $json_output[trackingCode]);
			}else{
				$body=array('type'=> 0,
					'title'=> $json_output[title],
					'groupId'=> $json_output[groupId],
					'name'=> $json_output[name],
					'typeTL'=>array('domainId'=> $json_output[typeTL][domainId],'url'=> $url_404)
				);
				$updated_link = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$tl_404_id,'POST', json_encode($body), $api_key);
				WPClickmeter::store_option("clickmeter_404_tlID", $updated_link[id]);
				WPClickmeter::store_option("clickmeter_404_tl", $json_output[trackingCode]);
			}
		}
	}
	if($_POST['track_404_flag']=="false"){
		WPClickmeter::store_option("clickmeter_track_404_flag", 0);
	}
	$track_404_flag = WPClickmeter::get_option("clickmeter_track_404_flag");
	$url_404 = WPClickmeter::get_option("clickmeter_track_404_url");

	global $wp;

	$workInProgress = WPClickmeter::get_option('clickmeter_workinprogress_flag');

//phpinfo();
}
?>