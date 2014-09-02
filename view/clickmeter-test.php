<?php add_thickbox(); ?>
<div id="my-content-id" style="display:none;">
     <p>This is my hidden content! It will appear in ThickBox when the link is clicked.</p>
</div>
<br>
<a href="#TB_inline?width=300&height=250&inlineId=my-content-id" title="<center>My Thickbox</center>" class="thickbox">View my inline content!</a>-->
<br>

<div id="save_changes_popup" style="display:none;">
     <p>
     	The selected options are going to be saved. It will take some seconds.
     </p>
</div>
<!--<a href="#TB_inline?width=300&height=250&inlineId=save_changes_popup" title="<center>Save changes</center>" class="thickbox" id="test">TEST AJAX</a>-->
<a id="test">TEST AJAX</a>
<br>

<script>

jQuery('#test').click(function(){
    
    var post_data = ({
        action: 'test_action',
        whatever: "1234"
    });

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        /** Ajax Call */
        jQuery.ajax({
            type:"post",
            url: ajaxurl,
            timeout: 50000,
            data: post_data,
            dataType: "json",
            success:function(data) {
                alert('Got this from the server: ' + data);
            },
            error: function(textStatus, errorThrown ){
                alert('OPS! Something went wrong');   
            }        
        });
});
</script>

<?php 

    $api_key=WPClickmeter::get_option('clickmeter_api_key');
    $group_id_TP = WPClickmeter::get_option('clickmeter_TPcampaign_id');
    $group_id_TL = get_option('clickmeter_TLcampaign_id');
    $timezone = WPClickmeter::get_option("clickmeter_user_timezone");

    $timezone = '+'.$timezone.':00';
    $timezone = preg_replace('/[^0-9]/', '', $timezone) * 36;
    $timezone_name = timezone_name_from_abbr(null, $timezone, true);
    date_default_timezone_set($timezone_name);
#   echo $timezone_name .' '. date('D d M Y H:i');
    echo "<table><tr><td>Timezone: </td><td><div style='padding-right:20px'>".$timezone_name ." ". date('D d M Y H:i') ."</div></td></tr></table>";


    $args = array(
    'posts_per_page' => -1,
    'post_type' => array('post', 'page'),
    'post_status' => array('publish', 'private', 'future'),
    'orderby' => 'title',
    'order' => 'ASC'
    );
    $posts_array = get_posts( $args );

    $remove = array();
    foreach ($posts_array as $post) {
        $post_id = $post->ID;
        $pixel_data = WPClickmeter::get_pixel($post_id);
        //Look for already existent pixels in clickmeter for actual campaign with the same title of the post
        if($pixel_data!=null){
            //if exist -> take it
            $doc = new DOMDocument();
            if(!empty($post->post_content)){
                $doc->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'));
                $doc->encoding = 'UTF-8';
                $divTags = $doc->getElementsByTagName('div');
                //echo $pixel_name . "<br>";
                foreach ($divTags as $div) {
                    if(preg_match("/clkmtr_tracking_pixel/",$div->attributes->getNamedItem('id')->nodeValue)){
                        $remove[] = $div;
                    }
                }
            }
        }
    }
    print_r($remove);

    // $t=time();
    // echo $t;

    // $pixels_list = array();
    // $offset = 0;
    // $json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups/'.$group_id_TP.'/datapoints?offset=0&limit=100&type=TP&status=active&_expand=true', 'GET', NULL, $api_key);
    // while(!empty($json_output[entities])){
    //     foreach ($json_output[entities] as $pixel) {
    //         $pixels_list[] = $pixel;    
    //     }
    //     $offset += 100;
    //     $json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/groups/'.$group_id_TP.'/datapoints?offset='.$offset.'&limit=100&type=TP&status=active&_expand=true', 'GET', NULL, $api_key);
    // }

    // $args = array(
    //     'posts_per_page' => -1,
    //     'post_type' => array('post', 'page'),
    //     'post_status' => array('publish', 'private', 'future'),
    //     'orderby' => 'title',
    //     'order' => 'ASC'
    //     );
    // $posts_array = get_posts( $args );

    // //print_r($posts_array);
    // print_r($pixels_list);

    // foreach ($posts_array as $post) {
    //     $post_id = $post->ID;
    //     $post_title = $post->post_title;
    //     if(!empty($pixels_list)){
    //         //Look for already existent pixels in clickmeter for actual campaign with the same title of the post
    //         foreach ($pixels_list as $pixel) {
    //             //if exist -> take it
    //             if(strcasecmp($pixel[title],$post_title)==0){
    //                 //echo "esiste un pixel con quel titolo su clickmeter <br>";
    //                 $pixel_id = $pixel[id];
    //                 $pixel_name = $pixel[name];
    //                 $json_output = WPClickmeter::api_request('http://apiv2.clickmeter.com/datapoints/'.$pixel_id, 'GET', NULL, $api_key);
    //                 $trackingCode = $json_output[trackingCode];

    //                 WPClickmeter::store_pixel($post_id, $pixel_id, $pixel_name, $trackingCode);
    //             }
    //         }
    //     }
    // }

?>
<form method="post" action="#">
    <input type="hidden" name="fake_post_creation" value="true"/>
    <input type="submit" style="font-size: 12px; padding: 3px 3px;width:220px;" class="clickmeter-button" value="Create fake posts">
</form>
<br><br><br><br><br>
<form method="post" action="#">
    <input type="hidden" name="fake_post_delete" value="true"/>
    <input type="submit" style="font-size: 12px; padding: 3px 3px;width:220px;" class="clickmeter-button" value="Delete fake posts">
</form>
<br><br><br><br><br>
<form method="post" action="#">
    <input type="hidden" name="error_message_test" value="true"/>
    <input type="submit" style="font-size: 12px; padding: 3px 3px;width:220px;" class="clickmeter-button" value="Error message test">
</form>

<a id="showspin"><div class="spinner"></div>SHOW SPINNER</a>