jQuery(window).load(function(){
	jQuery("#content_ifr").contents().find("html").find("#clkmtr_tracking_pixel").remove();
	var myhtml = jQuery.parseHTML( "<div>" + jQuery("#content").text() + "</div>" );
	jQuery(myhtml).find("#clkmtr_tracking_pixel").remove();
	jQuery("#content").text(jQuery(myhtml).html());
});

jQuery(document).ready(function(){
	
	var document_url = document.URL;
	if(document_url.search("#tracking_link_settings")!=-1){
		jQuery("#tracking_link_settings").show();
	}
	if(document_url.search("#manage_404_errors")!=-1){
		jQuery("#manage_404_errors").show();
	}
	if(document_url.search("#clickmeter_track_conversions")!=-1){
		jQuery("#clickmeter_track_conversions").show();
	}
	if(document_url.search("#clickmeter_track_views")!=-1){
		jQuery("#clickmeter_track_views").show();
	}		

	jQuery("#clickmeter_check_all").click(function(){
		var flag = jQuery(this).prop("checked");
		if(flag==false){
			jQuery("input[type=checkbox]").prop("checked", false); 	
		}else{
			jQuery("input[type=checkbox]").prop("checked", true); 	
		}
	});

	jQuery("#cm_apply_bulk_action").click(function(){
		var action_type = jQuery("[name=cm_bulk_actions]").val();
		if(action_type=="null") return;
		if(action_type == "bulk_tl_change_dest_url"){
			jQuery("#cm_bulk_edit_url").show();
		}else if(action_type == "bulk_tl_delete"){
			jQuery("#bulk_action_progress").show();
			var checked_options = [];
			jQuery("input[type=checkbox][name=tracking_link_rows]:checked").each(function(){
				checked_options.push(jQuery(this).val());
			});
			var post_data = {
		        action: action_type,
		        ids: checked_options
		    };

		    var timeout_val = 3600*1000;
			jQuery.ajax({
		        type:"post",
		        url: ajaxurl,
		        timeout: timeout_val,
		        data: post_data,
		        success:function(data) {
		            //alert("Execution terminated with success! ");
		            location.reload();
		        },
		        //error: function( jqXHR, textStatus, errorThrown ){
		          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
		        //}        
			});
		}
	});

	jQuery("#cm_bulk_edit_url_confirm").click(function(){
		jQuery("#cm_bulk_edit_url").hide();
		jQuery("#bulk_action_progress").show();
		var checked_options = [];
		jQuery("input[type=checkbox][name=tracking_link_rows]:checked").each(function(){
			checked_options.push(jQuery(this).val());
		});
		var post_data = {
	        action: "bulk_tl_change_dest_url",
	        ids: checked_options,
	        new_url: jQuery("#cm_dest_url").val()
	    };

	    var timeout_val = 3600*1000;
		jQuery.ajax({
	        type:"post",
	        url: ajaxurl,
	        timeout: timeout_val,
	        data: post_data,
	        success:function(data) {
	            //alert("Execution terminated with success! ");
	            location.reload();
	        },
	        //error: function( jqXHR, textStatus, errorThrown ){
	          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
	        //}        
		});
	});

	jQuery(document).ready( function(jQuery) {   
		jQuery('.openInNewWindow').parent().attr('target','_blank'); 
	});

	jQuery(".expand_section").click(function(){
		jQuery(this).parent().next("div").show();
		jQuery(this).prev().show();
		jQuery(this).hide();
	});
	jQuery(".compress_section").click(function(){
		jQuery(this).parent().next("div").hide();
		jQuery(this).next().show();
		jQuery(this).hide();
	});

	//onload
	var checkedRadioButton = jQuery('input[type=radio][name=pixels_flags]:checked');
	if (checkedRadioButton.val() == 'true') {
		jQuery("#multiple_select_table_pixels").show();
	}
	else if (checkedRadioButton.val() == 'false') {
		jQuery("#tracking_pixel_box").hide();
		jQuery("#include_default_pixel").hide();
	}

	var track404flag = jQuery('input[type=radio][name=track_404_flag]:checked');
	if (track404flag.val() == 'true') {
		jQuery("#custom_404_page").show();
		jQuery("#custom_404_page_label").show();
	}
	else if (track404flag.val() == 'false') {
		jQuery("#custom_404_page").hide();
		jQuery("#custom_404_page_label").hide();
	}

	var conversionId1 = jQuery("#conversion1_id").val();
	if(conversionId1!=null && conversionId1!=undefined && conversionId1!=""){
		jQuery("#conversion1_created").show();
		jQuery("#conversion1_form").hide();
		jQuery("#conversion2_form").show();
	}else{
		jQuery("#conversion1_created").hide();
		jQuery("#conversion1_form").show();
		jQuery("#conversion2_form").hide();
	}
	var conversionId2 = jQuery("#conversion2_id").val();
	if(conversionId2!=null && conversionId2!=undefined && conversionId2!=""){
		jQuery("#conversion2_form").hide();
		jQuery("#conversion2_created").show();
	}else{
		jQuery("#conversion2_created").hide();
	}

	//onchange
	jQuery('input[type=radio][name=pixels_flags]').change(function() {
		if (this.value == 'true') {
			jQuery("#tracking_pixel_box").show();
			jQuery("#include_default_pixel").show();
		}
		else if (this.value == 'false') {
			jQuery("#tracking_pixel_box").hide();
			jQuery("#include_default_pixel").hide();
		}
	});

	jQuery('input[type=radio][name=track_404_flag]').change(function() {
		if (this.value == 'true') {
			jQuery("#custom_404_page").show();
			jQuery("#custom_404_page_label").show();
		}
		else if (this.value == 'false') {
			jQuery("#custom_404_page").hide();
			jQuery("#custom_404_page_label").hide();
		}
	});
});


//PIXEL SETTINGS FUNCTIONS
jQuery(function() {
     jQuery(".multiselect").multiselect();
});

//MULTISELECT FUNCTIONS
jQuery.fn.multiselect = function() {
    jQuery(this).each(function() {
        var checkboxes = jQuery(this).find("input:checkbox");
        checkboxes.each(function() {
            var checkbox = jQuery(this);
            /* Highlight pre-selected checkboxes
            if (checkbox.prop("checked")){
            	checkbox.parent().addClass("multiselect-on");
            	checkbox.attr("name", "included_posts_list");
            }*/
            // Highlight checkboxes that the user selects
            checkbox.click(function() {
                if (checkbox.prop("checked")){
                	checkbox.attr("name", "included_list[]");
                	checkbox.parent().addClass("multiselect-on");
                }
                else{
                	checkbox.attr("name", "excluded_list[]");
                	checkbox.parent().removeClass("multiselect-on");
                } 
            });
        });
    });
};

//POSTS CHECKBOX GROUP FUNCTIONS
function selectAllTPPosts (){
	jQuery("#multiselect_posts").children().addClass("multiselect-on");
	jQuery("#multiselect_posts").children().children().attr("name", "included_list[]");
	jQuery("#multiselect_posts").children().children().prop("checked", "checked");
}
function deselectAllTPPosts (){
	jQuery("#multiselect_posts").children().removeClass("multiselect-on");
	jQuery("#multiselect_posts").children().children().attr("name", "excluded_list[]");
	jQuery("#multiselect_posts").children().children().removeAttr("checked");
}

//PAGES CHECKBOX GROUP FUNCTIONS
function selectAllTPPages (){
	jQuery("#multiselect_pages").children().addClass("multiselect-on");
	jQuery("#multiselect_pages").children().children().attr("name", "included_list[]");
	jQuery("#multiselect_pages").children().children().prop("checked", "checked");
}
function deselectAllTPPages (){
	jQuery("#multiselect_pages").children().removeClass("multiselect-on");
	jQuery("#multiselect_pages").children().children().attr("name", "excluded_list[]");
	jQuery("#multiselect_pages").children().children().removeAttr("checked");
}

function changeSelectedStatus(){
	jQuery("[name='excluded_list[]']").prop("checked", "checked");
	jQuery("[name='excluded_list[]']").prop("checked", "checked");
}


//INIT FUNCTIONS
function startupEnableTP(){
	jQuery("#init_form").submit();
}
function startupDisableTP(){
	jQuery("#startup_create_TP").attr('value', "false");
	jQuery("#init_form").submit();
}
function callAjaxTP_init_creation(){
	var post_data = {
        action: 'TP_init_creation'
    };

    var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
        },
        //error: function( jqXHR, textStatus, errorThrown ){
          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        //}        
	});
}

//CREATE TRACKING PIXEL FUNCTIONS
function callAjaxTP_savechanges(){
    var post_data = {
        action: 'TP_savechanges',
    };

    var timeout_val = 3600*1000;

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    // jQuery.post(ajaxurl, data, function(response) {
    //     //alert('Got this from the server: ' + response);
    // });
	jQuery.ajax({
	        type:"post",
	        url: ajaxurl,
	        timeout: timeout_val,
	        data: post_data,
	        success:function(data) {
	            //alert("Execution terminated with success! ");
	            
	        },
	        //error: function( jqXHR, textStatus, errorThrown ){
	          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
	        //}        
	});
}

//REMOVE APIKEY FUNCTIONS
function callAjaxTP_delete(){
	var post_data = {
        action: 'TP_delete_apikey'
    };

    var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
            
        },
        //error: function( jqXHR, textStatus, errorThrown ){
          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        //}        
	});
}
function confirmDelete(){
	jQuery("#remove_apikey_form").submit();
}

//CREATE TRACKING LINK
function callAjax_create_trackinglink(){
	jQuery("#get_tracking_link").hide();
	jQuery("#creating_tl").show();

	var post_data = {
	    action: 'create_tl',
	    friendly_name: jQuery("#clickmeter_post_title").val(),
	    url: jQuery("#post_url").val(),
	    post_id: jQuery("#post_ID").val()
	};

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
			jQuery("#creating_tl").hide();
			jQuery("#tl_creation_success").show();
			jQuery("#get_tracking_link_button").hide();
            //alert("Execution terminated with success! ");
        },
        //error: function( jqXHR, textStatus, errorThrown ){
          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        //}        
	});
}

//CHECK REDIRECTION LINK EXISTENCE
function callAjax_check_redirection_trackinglink(){
	jQuery(".error").hide();
	jQuery("#save_tracking_link").hide();
	jQuery("#creating_tl").show();
	var dest_url = jQuery("#redirection_link_url").val()
	var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|mailto:|ftp:\/\/|ftps:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(.)*/;
	if(!pattern.test(dest_url)){
		//alert("Invalid URL.");
		jQuery("#error_url").text("*Error: Insert a valid URL.");
		jQuery("#error_url").show();
		jQuery("#creating_tl").hide();
		jQuery("#save_tracking_link").show();
		return;
	}
	if(jQuery("#redirection_link_name").val()=="" || 
		jQuery("#redirection_link_name").val()==null || 
		jQuery("#redirection_link_name").val()==undefined){
		
		//alert("Empty name.");
		jQuery("#error_fname").text("*Error: Friendly name cannot be empty.");
		jQuery("#error_fname").show();
		jQuery("#creating_tl").hide();
		jQuery("#save_tracking_link").show();
		return;
	}

	var clickmeter_default_domainId = jQuery("#domain_list").val();
	if(clickmeter_default_domainId == "1597"){
		var post_data = {
		    action: 'check_redirect_link',
		    link_name: jQuery("#redirection_link_name").val()
		};

		jQuery.post(ajaxurl, post_data, function(response) {
			if(response == "is_tracking_link"){
				//alert('Error: You already have a tracking link with the inserted name.');
				jQuery("#error_fname").text("*Error: You already have a tracking link with the inserted name.");
				jQuery("#error_fname").show();
				jQuery("#creating_tl").hide();
				jQuery("#save_tracking_link").show();	
				return;
			}else if(response == "is_post"){
				//alert('Error: Inserted name have to be different from any post/page title.');
				jQuery("#error_fname").text("*Error: Inserted name have to be different from any post/page title.");
				jQuery("#error_fname").show();
				jQuery("#creating_tl").hide();
				jQuery("#save_tracking_link").show();
				return;
			}
			//alert("check passed!");
			// jQuery("#creating_tl").hide();
			// jQuery("#save_tracking_link").show();
			callAjax_create_custom_trackinglink("1");
		});
	}else{
		callAjax_create_custom_trackinglink("0");
	}
}

//CREATE REDIRECTION TRACKING LINK
function callAjax_create_custom_trackinglink(redirection_flag){
	var post_data = {
	    action: 'create_tl',
	    friendly_name: jQuery("#redirection_link_name").val(),
	    url: jQuery("#redirection_link_url").val(),
	    domain_id: jQuery("#domain_list").val(),
	    camapaign_id: jQuery("#campaign_list").val(),
	    first_conversion_id: jQuery("#first_conversion_list").val(),
	    second_conversion_id: jQuery("#second_conversion_list").val(),
	    post_id: "0000",
	    is_redirection_link: redirection_flag,
	    redirection_type: jQuery("#redirection_type_list").val(),
	    cloak_link: jQuery("[name=link_cloak_flag]:checked").val()
	};

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(response) {
        	var bogoval = jQuery("#cm_bogoval").val();
			var obj = jQuery.parseJSON(response);
			jQuery("#created_tracking_link_code").text(obj.alternative_url);
			jQuery("#created_tracking_link_code").attr("href", obj.alternative_url);
			jQuery("#created_tracking_link_edit").attr("href", "http://mybeta.clickmeter.com/go?val="+bogoval+"&returnUrl=%2Flinks%2Fedit%2F"+ obj.created_link_id);
			jQuery("#created_tracking_link_stats").attr("href", "http://mybeta.clickmeter.com/go?val="+bogoval+"&returnUrl=%2FLinks%3FlinkId%3D"+obj.created_link_id);
			jQuery("#created_tracking_link_QR").attr("href", obj.trackingCode+".qr");

        	jQuery("#creating_tl").hide();
        	jQuery(".error").hide();
			jQuery("#save_tracking_link").show();
			jQuery("#tracking_link_form").hide();
			jQuery("#tl_creation_success").show();

            //alert("Execution terminated with success! ");
        },
        //error: function( jqXHR, textStatus, errorThrown ){
          //  alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        //}        
	});
}

//CREATE CONVERSION FUNCTIONS
function callAjax_create_conversion(){
	var post_data = {
        action: 'TP_create_conversion'
    };

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
            
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	//alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        }        
	});
}
//ASSOCIATE CONVERSION FUNCTION
function callAjax_associate_conversion(conversionToAssociate){
	var post_data = {
        action: 'TP_associate_conversion',
        conversion_id: conversionToAssociate
    };

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
            
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	//alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        }        
	});
}

//DELETE CONVERSION FUNCTIONS
function confirmDeleteFirstConv(){
	jQuery("#first_conv_form").submit();
}

function confirmDeleteSecondConv(){
	jQuery("#second_conv_form").submit();
}

function callAjax_delete_first_conversion(){
	var post_data = {
        action: 'TP_delete_first_conversion',
    };

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
            
        },
        error: function( jqXHR, textStatus, errorThrown ){
           //alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        }        
	});
}
function callAjax_delete_second_conversion(){
	var post_data = {
        action: 'TP_delete_second_conversion',
    };

	var timeout_val = 3600*1000;
	jQuery.ajax({
        type:"post",
        url: ajaxurl,
        timeout: timeout_val,
        data: post_data,
        success:function(data) {
            //alert("Execution terminated with success! ");
            
        },
        error: function( jqXHR, textStatus, errorThrown ){
           //alert('OPS! Something went wrong' + textStatus + ": " + errorThrown);   
        }        
	});
}