<?php
/**
 * WP_CF7_Custom_Addon Extra_Features
 *
 * @class    WP_CF7_Custom_Addon_Extra_Features
 * @package  WP_CF7_Custom_Addon\Extra_Features
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WP_CF7_Custom_Addon_Extra_Features class.
 */
class WP_CF7_Custom_Addon_Extra_Features {

	private $cf7_id = '';
	private $last_inserted_id = '';
	private $cf7_attachments = [];

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_filter('wpcf7_skip_mail', [$this, 'wp_cf7_custom_addon_skip_mail'], 10, 2);

		add_action('wpcf7_before_send_mail', [$this, 'wp_cf7_custom_addon_before_send_mail'], 20);

		//add_filter('pre_wp_mail', [$this, 'wp_cf7_custom_addon_save_email_log'], 10, 2);
		add_filter('wp_mail', [$this, 'wp_cf7_custom_addon_save_email_log'], 10);

		add_action( 'wp_mail_failed', array( $this, 'wp_cf7_custom_addon_update_mail_status' ) );

		add_action( 'wp_ajax_wp_cf7_custom_addon_mail_sent', array( $this, 'wp_cf7_custom_addon_mail_sent' ) );
		add_action( 'wp_ajax_nopriv_wp_cf7_custom_addon_mail_sent', array( $this, 'wp_cf7_custom_addon_mail_sent' ) );
	}

	public function wp_cf7_custom_addon_skip_mail($skip_mail, $cf7)
	{
		$cf7_id = $cf7->id();

		$is_skip_mail = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_skip_mail', true );

		if($is_skip_mail)
		{
			$skip_mail = true;
		}
		
		return $skip_mail;
	}

	public function wp_cf7_custom_addon_before_send_mail($cf7)
	{
		$cf7_id = $cf7->id();

		/*
		* Create action for 3party API integration.
		*/
		do_action( 'wp_cf7_custom_addon_thirdparty_api_'.$cf7_id, $cf7, $_POST);


		$save_form_data = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_form_data', true );
		if($save_form_data)
		{
			$this->wp_cf7_custom_addon_save_form_data($cf7);
		}
	}

	public function wp_cf7_custom_addon_save_form_data($cf7)
	{
		global $wpdb;

		$cf7_id 		= $cf7->id();
		$table_name    	= $wpdb->prefix.'wp_cf7_custom_addon_form_data';
    	$upload_dir    	= wp_upload_dir();
    	$wp_cf7_custom_addon_dirname = $upload_dir['basedir'].'/wp_cf7_custom_addon_uploads';
    	$wp_cf7_custom_addon_dirurl 	= $upload_dir['baseurl'].'/wp_cf7_custom_addon_uploads';
    	$time_now      	= time();

    	$form = WPCF7_Submission::get_instance();

    	if ( $form ) 
    	{
    		$data 		= $form->get_posted_data();
	        $arrFiles   = $form->uploaded_files();

	        foreach ($arrFiles as $file_key => $files) 
	        {
	        	unset($data[$file_key]);

	        	if(is_array($files))
	        	{
	        		foreach ($files as $file) 
	        		{
	        			copy($file, $wp_cf7_custom_addon_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($file));

	        			$data[$file_key][] = $wp_cf7_custom_addon_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($file);
	        			$this->cf7_attachments[$file_key][] = $wp_cf7_custom_addon_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($file);
	        		}
	        	}
	        	else
	        	{
	        		copy($file, $wp_cf7_custom_addon_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($files));

	        		$data[$file_key][] = $wp_cf7_custom_addon_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($files);
	        		$this->cf7_attachments[$file_key][] = $wp_cf7_custom_addon_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($files);
	        	}
	        }

	        $params = [];

	        foreach ($data as $key => $value) 
	        {
        		if( is_array($value) && !empty($value) )
                {
                	$value = implode(',', $value);
                }

                $params[$key] = $value;
	        }

	        if(!empty($params))
	        {
	        	$params = apply_filters('wp_cf7_custom_addon_add_custom_data', $params);

	        	$record_id = uniqid();

	        	foreach ($params as $key => $value) 
	        	{
	        		$logs = [];
	        		$logs['cf7_id'] = $cf7_id;
	        		$logs['record_id'] = $record_id;
	        		$logs['field_name'] = $key;
	        		$logs['field_value'] = is_array($value) ? wp_json_encode($value) : $value;

	        		$wpdb->insert($table_name, $logs);
	        	}

	        	$logs = [];
        		$logs['cf7_id'] = $cf7_id;
        		$logs['record_id'] = $record_id;
        		$logs['field_name'] = 'created_date';
        		$logs['field_value'] = current_time('Y-m-d H:i:s');

        		$wpdb->insert($table_name, $logs);

        		do_action( 'wp_cf7_custom_addon_save_custom_data', $cf7_id, $record_id);
	        }
    	}
	}

	//public function wp_cf7_custom_addon_save_email_log($return, $atts)
	public function wp_cf7_custom_addon_save_email_log($atts)
	{
		global $wpdb;

		$cf7_id = isset( $_POST['_wpcf7'] ) ? sanitize_text_field($_POST['_wpcf7']) : '';
		$this->cf7_id = $cf7_id;

		$table_name    = $wpdb->prefix.'wp_cf7_custom_addon_email_log';

		$save_email_log = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_email_log', true );
		if($save_email_log)
		{
			$logs = [];
			$logs['cf7_id'] = $cf7_id;
			$logs['email_to'] = $atts['to'];
			$logs['email_subject'] = $atts['subject'];
			$logs['email_message'] = $atts['message'];
			$logs['email_headers'] = $atts['headers'];
			$logs['email_attachments'] = is_array($this->cf7_attachments) && !empty($this->cf7_attachments) ? wp_json_encode($this->cf7_attachments) : '';
			$logs['ip_address'] = wp_cf7_custom_addon_get_user_ip_address();
			$logs['is_sent'] = 1;
			$logs['sent_date'] = current_time('Y-m-d H:i:s');

			$wpdb->insert($table_name, $logs);

			$this->last_inserted_id = $wpdb->insert_id;

			$this->cf7_attachments = [];
		}

		return $atts;
	}

	public function wp_cf7_custom_addon_update_mail_status($wp_errors)
	{
		global $wpdb;

		$save_email_log = get_post_meta( $this->cf7_id, '_wp_cf7_custom_addon_save_email_log', true );
		if( $save_email_log && !empty($this->last_inserted_id) && !empty($this->cf7_id) )
		{
			$errors = $wp_errors->get_error_messages();

			$table_name    = $wpdb->prefix.'wp_cf7_custom_addon_email_log';

			$logs = [];
			$logs['is_sent'] = 0;
			$logs['error_message'] = is_array($errors) ? implode(', ', $errors) : $errors;

			$wpdb->update($table_name, $logs, ['id' => $this->last_inserted_id, 'cf7_id' => $this->cf7_id]);

			$this->last_inserted_id = '';
		}
	}

	public function wp_cf7_custom_addon_mail_sent()
	{
		check_ajax_referer( '_nonce_wp_cf7_custom_addon_security', 'security' );

		$cf7_id = isset($_REQUEST['cf7_id']) ? sanitize_text_field($_REQUEST['cf7_id']) : '';

		$thankyou_page_url = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_thankyou_page_url', true );

		print($thankyou_page_url);
		wp_die();
	}

}

return new WP_CF7_Custom_Addon_Extra_Features();