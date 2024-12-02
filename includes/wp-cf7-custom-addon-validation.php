<?php
/**
 * WP_CF7_Custom_Addon Validation
 *
 * @class    WP_CF7_Custom_Addon_Validation
 * @package  WP_CF7_Custom_Addon\Validation
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WP_CF7_Custom_Addon_Validation class.
 */
class WP_CF7_Custom_Addon_Validation {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_filter( 'wpcf7_validate_text*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_email*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_url*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_tel*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_number*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_range*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_date*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_textarea*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_select*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_checkbox*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_radio*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_acceptance*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_quiz*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_file*', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_country_code', [$this, 'wp_cf7_custom_addon_required_message'], 9, 2 );

		add_filter( 'wpcf7_validate', [$this, 'wp_cf7_custom_addon_validate_message'], 20, 2 );
	}

	public function wp_cf7_custom_addon_required_message( $result, $tag ) 
	{
		$cf7_id = isset( $_POST['_wpcf7'] ) ? sanitize_text_field($_POST['_wpcf7']) : '';

		$form = WPCF7_Submission::get_instance();
		$data = $form->get_posted_data();

		if( isset( $data[$tag->name] ) && is_array($data[$tag->name]) && empty($data[$tag->name][0]) )
		{
			$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );

			$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : '';
			$message = !empty($message) ? $message : 'The '. $tag->name .' field is required.';

			$result->invalidate( $tag->name, $message );
		}
		else if( isset( $data[$tag->name] ) && empty($data[$tag->name]) )
		{
			$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );

			$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : '';
			$message = !empty($message) ? $message : 'The '. $tag->name .' field is required.';

			$result->invalidate( $tag->name, $message );
		}
		elseif ( isset($data[$tag->name]) && 'email' == $tag->basetype && !wpcf7_is_email($data[$tag->name]) ) 
		{
			$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );

			$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : '';
			$message = !empty($message) ? $message : 'The '. $tag->name .' field is required.';

			$result->invalidate( $tag->name, $message );
		}

		if( isset($_FILES) && !empty($_FILES) )
		{
			$ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
			$form_fields = $ContactForm->scan_form_tags();

			$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );

			foreach ($form_fields as $form_field) 
			{
				if( $form_field->type === 'file*' && isset($_FILES[$form_field->name]['name']) && empty($_FILES[$form_field->name]['name']) )
				{
					$message = isset($arr_values[$form_field->name]['validation-message']) ? $arr_values[$form_field->name]['validation-message'] : '';
					$message = !empty($message) ? $message : 'The '. $form_field->name .' field is required.';

					$result->invalidate( $form_field->name, $message );
				}
			}
		}

		return $result;
	}

	public function wp_cf7_custom_addon_validate_message( $result, $tags ) 
	{
		$cf7_id = isset( $_POST['_wpcf7'] ) ? sanitize_text_field($_POST['_wpcf7']) : '';
		$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
		$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
		$arr_values = recursive_sanitize_text_field( $arr_values );

		foreach ($tags as $tag) 
		{
			if( isset($arr_values[$tag->name]['validation-pattern']) && !empty($arr_values[$tag->name]['validation-pattern']) )
			{
				$value = isset( $_POST[$tag->name] )
				? trim( wp_unslash( strtr( (string) sanitize_text_field($_POST[$tag->name]), "\n", " " ) ) )
				: '';

				$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : '';
				$message = !empty($message) ? $message : 'The '. $tag->name .' field is required.';
				$pattern = isset($arr_values[$tag->name]['validation-pattern']) ? $arr_values[$tag->name]['validation-pattern'] : '';

				if( !empty($pattern) && !preg_match($pattern, $value) )
				{
					$result->invalidate( $tag, $message );
				}
			}
		}

		return $result;
	}

}

return new WP_CF7_Custom_Addon_Validation();