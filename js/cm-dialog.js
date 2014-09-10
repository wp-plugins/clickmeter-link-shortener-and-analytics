// custom.js
jQuery(function(jQuery) {
    var info = jQuery("#modal-content");
    info.dialog({                   
        'dialogClass'   : 'wp-dialog',           
        'modal'         : true,
        'autoOpen'      : false, 
        'closeOnEscape' : true,      
        'buttons'       : {
            "Close": function() {
                $(this).dialog('close');
            }
        }
    });
    jQuery("#open-modal").click(function(event) {
        event.preventDefault();
        info.dialog('open');
    });
});    