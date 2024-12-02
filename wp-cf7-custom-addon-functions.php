<?php
/**
 * Recursive sanitation for an array
 * @param $array
 * @return mixed
 */
function recursive_sanitize_text_field($array) 
{
	foreach ( $array as $key => &$value ) 
	{
		if ( is_array( $value ) ) 
		{
			$value = recursive_sanitize_text_field($value);
		}
		else 
		{
			$value = sanitize_text_field( $value );
		}
	}

	return $array;
}

/**
 * get cf7 form records count
 * @param $cf7_id
 * @return mixed
 */
function get_cf7_records_count($cf7_id = '') 
{
	global $wpdb;

	$records = $wpdb->get_col( $wpdb->prepare( 
                        "SELECT COUNT(*) AS `totals` FROM %1s 
                        WHERE cf7_id = %s 
                        GROUP BY `record_id` 
                        ORDER BY `record_id`", 
                        $wpdb->prefix . 'wp_cf7_custom_addon_form_data',
                        $cf7_id
                ) );

	if( isset($records) && !empty($records) )
    {
    	return count($records);
    }
    else
    {
    	return false;
    }
}

/**
 * get cf7 form fields
 * @param $cf7_id
 * @return mixed
 */
function get_cf7_fields($cf7_id = '') 
{
	global $wpdb;

	$fields = $wpdb->get_col( $wpdb->prepare( 
                        "SELECT `field_name` FROM %1s 
                        WHERE cf7_id = %s 
                        GROUP BY `field_name` 
                        ORDER BY `id`", 
                        $wpdb->prefix . 'wp_cf7_custom_addon_form_data',
                        $cf7_id
                ) );

	if( isset($fields) && !empty($fields) )
    {
    	$columns = [];
        $columns['cb'] = '<input type="checkbox" />';
    	foreach ($fields as $field) 
    	{
    		$lable = str_replace('-', ' ', $field);
    		$lable = str_replace('_', ' ', $lable);
    		$columns[$field] = ucwords($lable);
    	}

    	return $columns;
    }
    else
    {
    	return false;
    }
}

/**
 * get cf7 form single record
 * @param $field_name, $record_id
 * @return mixed
 */
function get_cf7_record($field_name = '', $record_id = '') 
{
    global $wpdb;

    $record = $wpdb->get_row( $wpdb->prepare( 
                        "SELECT * FROM %1s 
                        WHERE field_name = %s AND record_id = %s 
                        ORDER BY `id`", 
                        $wpdb->prefix . 'wp_cf7_custom_addon_form_data',
                        $field_name,
                        $record_id
                ) );

    if( isset($record) && !empty($record) )
    {
        return $record;
    }
    else
    {
        return false;
    }
}

/**
 * get cf7 email log
 * @param $id
 * @return mixed
 */
function get_cf7_email_log($id = '') 
{
    global $wpdb;

    $where = 'WHERE 1=1';
    if ( isset($id) && !empty($id) ) {

        $where .= ' AND id IN ('. $id .')';
    }

    if ( isset($id) && !empty($id) ) 
    {
        $logs = $wpdb->get_row( $wpdb->prepare( 
                        "SELECT * FROM %1s 
                        $where
                        ORDER BY `id`", 
                        $wpdb->prefix . 'wp_cf7_custom_addon_email_log'
                ) );
    }
    else
    {
        $logs = $wpdb->get_results( $wpdb->prepare( 
                        "SELECT * FROM %1s 
                        $where
                        ORDER BY `id`", 
                        $wpdb->prefix . 'wp_cf7_custom_addon_email_log'
                ) );
    }

    if( isset($logs) && !empty($logs) )
    {
        return $logs;
    }
    else
    {
        return false;
    }
}

/**
 * get cf7 user IP
 * @param 
 * @return mixed
 */
function wp_cf7_custom_addon_get_user_ip_address()
{
    if( isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) )
    {
        //ip from share internet
        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    }
    elseif( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) )
    {
        //ip pass from proxy
        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    else
    {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
    }
    return $ip;
}