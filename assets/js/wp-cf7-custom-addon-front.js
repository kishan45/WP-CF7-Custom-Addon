var WP_CF7_Custom_Addon_Front = function () {

    return {
        init: function () 
        {            
            if (jQuery('.wpcf7').length > 0)
            {
                var wpcf7Elm = document.querySelector( '.wpcf7' );
                wpcf7Elm.addEventListener( 'wpcf7mailsent', function( event ) {
                  WP_CF7_Custom_Addon_Front.actions.WP_CF7_Custom_Addon_Mail_Sent(event);
                }, false );

                WP_CF7_Custom_Addon_Front.actions.WP_CF7_Custom_Addon_Multi_Submit_Prevent();
            }
        },

    	actions:
        {
            WP_CF7_Custom_Addon_Mail_Sent: function(event) 
            {
                var fields = event.detail.inputs;
                jQuery.ajax({
                    url: wp_cf7_custom_addon.ajax_url,
                    type: 'POST',
                    dataType: 'HTML',
                    data: {
                        action: 'wp_cf7_custom_addon_mail_sent',
                        cf7_id: event.detail.contactFormId,
                        security: wp_cf7_custom_addon.wp_cf7_custom_addon_security,
                    },
                    success: function (responce)
                    {
                        if(responce != '')
                        {
                            window.location.replace(responce);
                        }
                    }
                });
            },

            WP_CF7_Custom_Addon_Multi_Submit_Prevent: function() 
            {
                var disableSubmit = false;
                jQuery('.wpcf7-form input[type="submit"]').click(function() 
                {
                    var disabled = jQuery(this).attr('data-disabled');
                    if (disabled && disabled == "disabled") 
                    {
                        return false;
                    } 
                    else 
                    {
                        jQuery(this).attr('data-disabled',"disabled");
                        return true;
                    }
                });

                jQuery('.wpcf7').bind("wpcf7submit",function()
                {
                    jQuery(this).find('.wpcf7-form input[type="submit"]').attr('data-disabled',"enabled");
                });
            },
        }
    }
};

WP_CF7_Custom_Addon_Front = WP_CF7_Custom_Addon_Front();
jQuery(document).ready(function ($) {
    WP_CF7_Custom_Addon_Front.init();
});