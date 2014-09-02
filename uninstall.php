<?php 

//if uninstall not called from WordPress exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
	
  	$args = array(
	'posts_per_page' => -1,
	'post_type' => array('post', 'page'),
	'post_status' => array('publish', 'private', 'future'),
	'orderby' => 'title',
	'order' => 'ASC'
	);
	$posts_array = get_posts( $args );

	//delete all tracking pixel from pages
	foreach ($posts_array as $post) {
		$doc = new DOMDocument();
		if(!empty($post->post_content)){
			$remove = array();
			$doc->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'));
			$doc->encoding = 'UTF-8';
			$divTags = $doc->getElementsByTagName('div');
			//echo $pixel_name . "<br>";
			foreach ($divTags as $div) {
                if(preg_match("/clkmtr_tracking_pixel/",$div->attributes->getNamedItem('id')->nodeValue)){
                    $remove[] = $div;
                }
			}
			foreach ($remove as $item){
				$item->parentNode->removeChild($item); 
			}
			$new_content = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si","!</body></html>$!si"),"",$doc->saveHTML());
			$modified_post = array(
			'ID'           => $post->ID,
			'post_content' => $new_content
			);
			wp_update_post( $modified_post );
			//echo "pixel rimosso true, dentro ".$post->post_title."<br>";
		}
	}
 
	global $wpdb;
	$options_table = $wpdb->prefix . 'clickmeter_options';
	//$wpdb->query("TRUNCATE TABLE $options_table");
	$wpdb->query( "DROP TABLE IF EXISTS $options_table" );
	$pixel_table_name = $wpdb->prefix . 'clickmeter_tracking_pixels';
	$wpdb->query( "DROP TABLE IF EXISTS $pixel_table_name" );
	$link_table_name = $wpdb->prefix . 'clickmeter_tracking_links';
	$wpdb->query( "DROP TABLE IF EXISTS $link_table_name" );
?>