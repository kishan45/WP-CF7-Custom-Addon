var WP_CF7_Custom_Addon_Admin = function () {

    return {
        init: function () 
        {
            jQuery( 'body' ).on('click', '#TB_window #TB_ajaxContent .nav-tab-wrapper a', WP_CF7_Custom_Addon_Admin.actions.hideShowTabs);
        },

    	actions:
        {
            hideShowTabs: function (event) 
            {
                jQuery('body').find('#TB_window #TB_ajaxContent .nav-tab-wrapper a').removeClass('nav-tab-active');
                jQuery('body').find('#TB_window #TB_ajaxContent .settings-panel').hide();

                var id = jQuery(event.target).attr('href'); 

                jQuery(event.target).addClass('nav-tab-active');
                jQuery('body').find('#TB_window #TB_ajaxContent ' + id).show();
            },
        }
    }
};

WP_CF7_Custom_Addon_Admin = WP_CF7_Custom_Addon_Admin();
jQuery(document).ready(function ($) {
    WP_CF7_Custom_Addon_Admin.init();
});