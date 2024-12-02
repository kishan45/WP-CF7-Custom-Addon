<?php
/**
Plugin Name: Contact Form 7 Addon
Plugin URI: https://github.com/kishan45/WP-CF7-Custom-Addon/
Description: Contact Form 7 Addon
Author: Kishan Kothari
Author URI: https://www.linkedin.com/in/kishankothari/
Text Domain: wp-cf7-custom-addon
Domain Path: /languages
Version: 1.0.0
Since: 1.0.0
Requires WordPress Version at least: 5.6
Copyright: 2021 Kishan Kothari
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {	
	exit;
}

add_action( 'admin_notices', 'pre_check_before_installing_wp_cf7_custom_addon' );
include_once(ABSPATH.'wp-admin/includes/plugin.php');
function pre_check_before_installing_wp_cf7_custom_addon() 
{	
	/*
	* Check weather Contact Form 7 is installed or not. If Contact Form 7 is not installed or active then it will give notification to admin panel
	*/
	if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php') ) 
	{
        global $pagenow;
    	if( $pagenow == 'plugins.php' )
    	{
           echo '<div id="error" class="error notice is-dismissible"><p>';
           echo __( 'Contact Form 7 is require to use Contact Form 7 Addon' , 'wp-event-manager-zoom');
           echo '</p></div>';	
    	}
    	return true;
	}
}

/**
 * WP_CF7_Custom_Addon class.
 */
class WP_CF7_Custom_Addon {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main WP_CF7_Custom_Addon Instance.
	 *
	 * Ensures only one instance of WP_CF7_Custom_Addon is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see WP_CF7_Custom_Addon()
	 * @return self Main instance.
	 */
	public static function instance() 
	{
		if ( is_null( self::$_instance ) ) 
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor - get the plugin hooked in and ready
	 */
	public function __construct() 
	{
		//if wp event manager not active return from the plugin
		if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php') )
			return;

		// Define constants
		define( 'WP_CF7_CUSTOM_ADDON_VERSION', '1.0.0' );
		define( 'WP_CF7_CUSTOM_ADDON_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WP_CF7_CUSTOM_ADDON_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WP_CF7_CUSTOM_ADDON_BASENAME', plugin_basename( __FILE__ ) );

		if ( is_admin() ) {
			include_once WP_CF7_CUSTOM_ADDON_PLUGIN_DIR . '/includes/admin/wp-cf7-custom-addon-admin.php';
		}
		
		include_once( WP_CF7_CUSTOM_ADDON_PLUGIN_DIR . '/wp-cf7-custom-addon-functions.php' );
		include_once( WP_CF7_CUSTOM_ADDON_PLUGIN_DIR . '/includes/wp-cf7-custom-addon-validation.php' );
		include_once( WP_CF7_CUSTOM_ADDON_PLUGIN_DIR . '/includes/wp-cf7-custom-addon-extra-features.php' );

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'activation' ) );
		register_deactivation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'deactivation' ) );
		add_action( 'admin_init', array( $this, 'updater' ) );

		// Actions
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 11 );
	}

	/**
	 * Called on plugin activation
	 */
	public function activation() 
	{
		global $wpdb;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wp_cf7_custom_addon_form_data = "CREATE TABLE ". $wpdb->prefix . "wp_cf7_custom_addon_form_data (
				id bigint(20) unsigned NOT NULL auto_increment,
				cf7_id bigint(20) unsigned NOT NULL default '0',
				record_id varchar(255) default NULL,
				field_name varchar(255) default NULL,
				field_value longtext,
				PRIMARY KEY (id),
				INDEX (id),
				INDEX (cf7_id),
				INDEX (record_id),
				INDEX (field_name),
				INDEX (field_value)
			) $collate;";

		$wp_cf7_custom_addon_email_log = "CREATE TABLE ". $wpdb->prefix . "wp_cf7_custom_addon_email_log (
				id bigint(20) unsigned NOT NULL auto_increment,
				cf7_id bigint(20) unsigned NOT NULL default '0',
				email_to varchar(255) default NULL,
				email_subject varchar(255) default NULL,
				email_message longtext,
				email_headers longtext,
				email_attachments varchar(1000) default NULL,
				ip_address varchar(20) default NULL,
				is_sent tinyint(1) DEFAULT NULL,
				error_message longtext,
				sent_date datetime NOT NULL,
				PRIMARY KEY (id),
				INDEX (id),
				INDEX (cf7_id),
				INDEX (email_to),
				INDEX (email_subject),
				INDEX (ip_address),
				INDEX (is_sent),
				INDEX (sent_date),
			) $collate;";

		dbDelta( $wp_cf7_custom_addon_form_data );
		dbDelta( $wp_cf7_custom_addon_email_log );

		$upload_dir    = wp_upload_dir();
	    $cfdb7_dirname = $upload_dir['basedir'].'/wp_cf7_custom_addon_uploads';
	    if ( ! file_exists( $cfdb7_dirname ) ) 
	    {
	        wp_mkdir_p( $cfdb7_dirname );
	        $fp = fopen( $cfdb7_dirname.'/index.php', 'w');
	        fwrite($fp, "<?php \n\t // Silence is golden.");
	        fclose( $fp );
	    }

		update_option( 'wp_cf7_custom_addon_version', WP_CF7_CUSTOM_ADDON_VERSION );

		flush_rewrite_rules();
	}

	/**
	 * Called on plugin deactivation
	 */
	public function deactivation() 
	{
		global $wpdb;
		
		flush_rewrite_rules();
	}

	/**
	 * Handle Updates
	 */
	public function updater() 
	{
		if ( version_compare( WP_CF7_CUSTOM_ADDON_VERSION, get_option( 'wp_cf7_custom_addon_version' ), '>' ) ) {

			update_option( 'wp_cf7_custom_addon_version', WP_CF7_CUSTOM_ADDON_VERSION );

			flush_rewrite_rules();
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() 
	{
		$domain = 'wp-cf7-custom-addon';       

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain( $domain, WP_LANG_DIR . "/wp-cf7-custom-addon/".$domain."-" .$locale. ".mo" );

		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue scripts and css
	 */
	public function frontend_scripts() 
	{
		wp_register_script( 'wp-cf7-custom-addon-front', WP_CF7_CUSTOM_ADDON_PLUGIN_URL . '/assets/js/wp-cf7-custom-addon-front.js', array( 'jquery' ), time(), true );

		wp_localize_script( 'wp-cf7-custom-addon-front', 'wp_cf7_custom_addon', array(
			'ajax_url' 	 => admin_url( 'admin-ajax.php' ),
			'wp_cf7_custom_addon_security'  => wp_create_nonce( '_nonce_wp_cf7_custom_addon_security' ),
		) );

		wp_enqueue_script( 'wp-cf7-custom-addon-front' );
	}

}

$GLOBALS['wp_cf7_custom_addon'] =  WP_CF7_Custom_Addon::instance();