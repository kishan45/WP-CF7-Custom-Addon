<?php
/**
 * WP_CF7_Custom_Addon Admin
 *
 * @class    WP_CF7_Custom_Addon_Admin
 * @package  WP_CF7_Custom_Addon\Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WP_CF7_Custom_Addon_Admin class.
 */
class WP_CF7_Custom_Addon_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		include( 'wp-cf7-custom-addon-forms-list.php' );
		include( 'wp-cf7-custom-addon-form-records-list.php' );

		include( 'wp-cf7-custom-addon-email-log.php' );

		add_action('admin_menu', array($this, 'admin_menu'), 12);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'wpcf7_editor_panels', [$this, 'wp_cf7_custom_addon_cvm_panel'] );
		add_action( 'wpcf7_after_save', [$this, 'wp_cf7_custom_addon_save_validation'] );

		add_filter( 'wp_cf7_custom_addon_admin_list_columns', [$this, 'wp_cf7_custom_addon_admin_list_columns'] );
	}

	public function admin_menu()
    {
        add_menu_page( __('WP CF7 Custom Addon', 'wp-cf7-custom-addon'), __('WP CF7 Custom Addon', 'wp-cf7-custom-addon'), 'manage_options', 'wp-cf7-custom-addon-db', array($this, 'wp_cf7_custom_addon_form_records_list'), WP_CF7_CUSTOM_ADDON_PLUGIN_URL.'/assets/images/wp-cf7-custom-addon-icon.png', 30);

        add_submenu_page('wp-cf7-custom-addon-db', __('Email Log', 'wp-cf7-custom-addon'), __('Email Log', 'wp-cf7-custom-addon'), 'manage_options', 'wp-cf7-custom-addon-email-log', array($this, 'wp_cf7_custom_addon_email_log'));
    }

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @return void
     */
    public function admin_enqueue_scripts() 
    {
    	wp_register_script( 'wp-cf7-custom-addon-admin', WP_CF7_CUSTOM_ADDON_PLUGIN_URL . '/assets/js/wp-cf7-custom-addon-admin.js', array( 'jquery' ), time(), true );
        wp_enqueue_script( 'wp-cf7-custom-addon-admin' );
    }

    public function wp_cf7_custom_addon_form_records_list()
    {
    	$cf7_id = isset($_REQUEST['cf7_id']) ? sanitize_text_field($_REQUEST['cf7_id']) : '';

    	if(!empty($cf7_id))
    	{
    		$wp_cf7_custom_addon_form_records_list = new WP_CF7_Custom_Addon_Form_Records_List();
	        $wp_cf7_custom_addon_form_records_list->prepare_items();
	    	?>
	    	<div class="wrap">
	            <h2><?php esc_html_e( 'Contact Form Records List', 'wp-cf7-custom-addon' ); ?></h2>

	            <form id="wp_cf7_custom_addon_form_records_list" method="post">
	            <?php
	                $wp_cf7_custom_addon_form_records_list->display();
	            ?>
	        	</form>
	        </div>
	        <?php
    	}
    	else
    	{
    		$cf7_forms_list = new WP_CF7_Custom_Addon_Forms_List();
	        $cf7_forms_list->prepare_items();
	    	?>
	    	<div class="wrap">
	            <h2><?php esc_html_e( 'Contact Forms List', 'wp-cf7-custom-addon' ); ?></h2>

	            <?php
	                $cf7_forms_list->display();
	            ?>
	        </div>
	        <?php
    	}
    }

    public function wp_cf7_custom_addon_email_log()
    {
    	$wp_cf7_custom_addon_email_log = new WP_CF7_Custom_Addon_Email_Log();
        $wp_cf7_custom_addon_email_log->prepare_items();
    	?>
    	<div class="wrap">
            <h2><?php esc_html_e( 'Email Log', 'wp-cf7-custom-addon' ); ?></h2>

            <form id="wp_cf7_custom_addon_email_log" method="post">
            <?php
                $wp_cf7_custom_addon_email_log->display();
            ?>
        	</form>
        </div>
        <?php
    }

	public function wp_cf7_custom_addon_cvm_panel( $panels ) 
	{
		$panels['wp-cf7-custom-addon-custom-validation'] = array(
			'title'    => __( 'Custom Validation', 'wp-cf7-custom-addon' ),
			'callback' => array( $this, 'wp_cf7_custom_addon_cvm_panel_callback' ),
		);
		$panels['wp-cf7-custom-addon-extra-features'] = array(
			'title'    => __( 'Extra Features', 'wp-cf7-custom-addon' ),
			'callback' => array( $this, 'wp_cf7_custom_addon_extra_features_panel_callback' ),
		);
		return $panels;
	}

	public function wp_cf7_custom_addon_cvm_panel_callback($post) 
	{
		wp_nonce_field( 'wp_cf7_custom_addon_save_data_security', 'wp_cf7_custom_addon_save_data_nonce' );
		?>
		<h2><?php esc_html_e( 'Custom Validation', 'wp-cf7-custom-addon' ); ?></h2>

		<fieldset>
			<?php
			$cf7_fields = array();
			$cf7_id     = $post->id();
			if( $cf7_id != null)
			{
				$ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
				$cf7_fields = $ContactForm->scan_form_tags();
			}
			else
			{
				$cf7_fields = $post->scan_form_tags();
			}

			$arr_values = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );
			?>

			<table class="form-table">
				<thead>
					<tr>
						<th scope="row" width="30%"><?php esc_html_e( 'Your field', 'wp-cf7-custom-addon' ); ?></th>
						<td width="35%"><?php esc_html_e( 'Field validation message', 'wp-cf7-custom-addon' ); ?></td>
						<td width="35%"><?php esc_html_e( '/^[A-Za-z. ]+$/', 'wp-cf7-custom-addon' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php if(!empty($cf7_fields)) : ?>

						<?php foreach($cf7_fields as $cf7_field) : ?>

							<?php
							$validation_pattern = isset($arr_values[$cf7_field->name]['validation-pattern']) ? sanitize_text_field($arr_values[$cf7_field->name]['validation-pattern']) : '';

							$validation_message = isset($arr_values[$cf7_field->name]['validation-message']) ? sanitize_text_field($arr_values[$cf7_field->name]['validation-message']) : '';
							?>

							<?php if( in_array($cf7_field->basetype, ['submit', 'acceptance']) ) : 
								continue; ?>

							<?php elseif( $cf7_field->basetype === 'email' ) : ?>
								<tr>
									<th scope="row">
										<label for="field-<?php echo esc_attr($cf7_field->name).'-validation'; ?>"><?php echo esc_html($cf7_field->name).' (Wrong Email)'; ?></label>
									</th>
									<td>
										<input type="text" id="field-<?php echo esc_attr($cf7_field->name).'-validation-message'; ?>" name="wp-cf7-custom-addon-validation[<?php echo esc_attr($cf7_field->name).'][validation-message]'; ?>" class="regular-text" size="70" value="<?php echo esc_attr($validation_message); ?>">
									</td>
									<td>
										<input type="text" id="field-<?php echo esc_attr($cf7_field->name).'-validation-pattern'; ?>" name="wp-cf7-custom-addon-validation[<?php echo esc_attr($cf7_field->name).'][validation-pattern]'; ?>" class="regular-text" size="70" value="<?php echo esc_attr($validation_pattern); ?>">
									</td>
								</tr>
							
							<?php else : ?>
								<tr>
									<th scope="row">
										<label for="field-<?php echo esc_attr($cf7_field->name).'-validation'; ?>"><?php echo esc_html($cf7_field->name).''; ?></label>
									</th>
									<td>
										<input type="text" id="field-<?php echo esc_attr($cf7_field->name).'-validation-message'; ?>" name="wp-cf7-custom-addon-validation[<?php echo esc_attr($cf7_field->name).'][validation-message]'; ?>" class="regular-text" size="70" value="<?php echo esc_attr($validation_message); ?>">
									</td>
									<td>
										<?php if( in_array($cf7_field->basetype, ['text', 'email', 'tel', 'number', 'range']) ) : ?>
											<input type="text" id="field-<?php echo esc_attr($cf7_field->name).'-validation-pattern'; ?>" name="wp-cf7-custom-addon-validation[<?php echo esc_attr($cf7_field->name).'][validation-pattern]'; ?>" class="regular-text" size="70" value="<?php echo esc_attr($validation_pattern); ?>">
										<?php endif; ?>
									</td>
								</tr>

							<?php endif; ?>

						<?php endforeach; ?>

					<?php endif; ?>
				</tbody>
			</table>

		</fieldset>
		<?php
	}

	public function wp_cf7_custom_addon_extra_features_panel_callback($post)
	{
		wp_nonce_field( 'wp_cf7_custom_addon_save_data_security', 'wp_cf7_custom_addon_save_data_nonce' );

		$cf7_id     = $post->id();
		$thankyou_page_url = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_thankyou_page_url', true );
		$skip_mail = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_skip_mail', true );
		$save_email_log = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_email_log', true );
		$save_form_data = get_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_form_data', true );

		if(empty($cf7_id) && empty($post->id))
		{
			$save_form_data = 1;
		}
		?>
		<h2><?php esc_html_e( 'Extra Features', 'wp-cf7-custom-addon' ); ?></h2>

		<fieldset>
			<table class="form-table">
				<tbody>
					<tr>
						<th width="30%"><?php esc_html_e( 'Thank you page URL', 'wp-cf7-custom-addon' ); ?></th>
						<td><input type="text" class="regular-text" name="wp-cf7-custom-addon-thankyou-page-url" value="<?php echo esc_attr($thankyou_page_url); ?>" /> </br>
							<small><?php esc_html_e('If you redirect thank you page then add than you page link.', 'wp-cf7-custom-addon' ); ?></small></td>
					</tr>
					<tr>
						<th width="30%"><?php esc_html_e( 'Skip Send Email', 'wp-cf7-custom-addon' ); ?></th>
						<td><input type="checkbox" name="wp-cf7-custom-addon-skip-mail" value="1" <?php echo checked($skip_mail, 1); ?> /> </br>
							<small><?php esc_html_e('If you are not send mail then checked.', 'wp-cf7-custom-addon' ); ?></small></td>
					</tr>
					<tr>
						<th width="30%"><?php esc_html_e( 'Save Email Log', 'wp-cf7-custom-addon' ); ?></th>
						<td><input type="checkbox" name="wp-cf7-custom-addon-save-email-log" value="1" <?php echo checked($save_email_log, 1); ?> /></br>
							<small><?php esc_html_e('If you are save email log in database then checked.', 'wp-cf7-custom-addon' ); ?></small></td>
						</td>
					</tr>
					<tr>
						<th width="30%"><?php esc_html_e( 'Save in Database', 'wp-cf7-custom-addon' ); ?></th>
						<td><input type="checkbox" name="wp-cf7-custom-addon-save-form_data" value="1" <?php echo checked($save_form_data, 1); ?> /></br>
							<small><?php esc_html_e('If you are save records in database then checked.', 'wp-cf7-custom-addon' ); ?></small></td>
						</td>
					</tr>
					<tr>
						<th width="30%"><?php esc_html_e( 'Integration with 3party API', 'wp-cf7-custom-addon' ); ?></th>
						<td>
							<code>
								add_action('wp_cf7_custom_addon_thirdparty_api_<?php echo $cf7_id; ?>', 'wp_cf7_custom_addon_thirdparty_api_callback_<?php echo $cf7_id; ?>', 20, 2);
								</br>
								</br>
								function wp_cf7_custom_addon_thirdparty_api_callback_<?php echo $cf7_id; ?>($cf7, $form_data) </br>
								{
									</br>&nbsp;&nbsp;&nbsp; //your code </br>
								}
							</code>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}

	public function wp_cf7_custom_addon_save_validation($cf7)
	{
		if( empty($_POST) )
			return;

		if( isset($_POST['page']) && $_POST['page'] !== 'wpcf7' )
			return;

		if ( isset($_POST['wp_cf7_custom_addon_save_data_nonce']) && !empty($_POST['wp_cf7_custom_addon_save_data_nonce']) && !wp_verify_nonce( sanitize_text_field($_POST['wp_cf7_custom_addon_save_data_nonce']), 'wp_cf7_custom_addon_save_data_security' ) )
			return;

		$cf7_id = $cf7->id();

		if(!empty($_POST['wp-cf7-custom-addon-validation']))
		{
			$arr_values = recursive_sanitize_text_field($_POST['wp-cf7-custom-addon-validation']);

			update_post_meta( $cf7_id, '_wp_cf7_custom_addon_custom_validation', $arr_values );
		}

		$thankyou_page_url = isset($_POST['wp-cf7-custom-addon-thankyou-page-url']) ? sanitize_text_field($_POST['wp-cf7-custom-addon-thankyou-page-url']) : '';
		update_post_meta( $cf7_id, '_wp_cf7_custom_addon_thankyou_page_url', $thankyou_page_url );

		$skip_mail = isset($_POST['wp-cf7-custom-addon-skip-mail']) ? sanitize_text_field($_POST['wp-cf7-custom-addon-skip-mail']) : '';
		update_post_meta( $cf7_id, '_wp_cf7_custom_addon_skip_mail', $skip_mail );

		$save_email_log = isset($_POST['wp-cf7-custom-addon-save-email-log']) ? sanitize_text_field($_POST['wp-cf7-custom-addon-save-email-log']) : '';
		update_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_email_log', $save_email_log );

		$save_form_data = isset($_POST['wp-cf7-custom-addon-save-form_data']) ? sanitize_text_field($_POST['wp-cf7-custom-addon-save-form_data']) : '';
		update_post_meta( $cf7_id, '_wp_cf7_custom_addon_save_form_data', $save_form_data );
	}

	public function wp_cf7_custom_addon_admin_list_columns($columns)
	{
		if(isset($columns['g-recaptcha-response']))
		{
			unset($columns['g-recaptcha-response']);
		}

		if(isset($columns['recaptcha']))
		{
			unset($columns['recaptcha']);
		}

		return $columns;
	}

}

new WP_CF7_Custom_Addon_Admin();