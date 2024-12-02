<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WP_CF7_Custom_Addon Form_Records_List
 *
 * @class    WP_CF7_Custom_Addon_Form_Records_List
 * @package  WP_CF7_Custom_Addon\Form_Records_List
 * @version  1.0.0
 */

/**
 * WP_CF7_Custom_Addon_Form_Records_List class.
 */
class WP_CF7_Custom_Addon_Form_Records_List extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		parent::__construct(array(
            'singular' => 'cf7_record', //Singular label
            'plural'   => 'cf7_records', //plural label, also this well be one of the table css class
            'ajax'     => false //We won't support Ajax for this table
        ));
	}

	/**
     * column_default function.
     * 
     * @access public
     * @param mixed $post
     * @param mixed $column_name
     */
    public function column_default( $item, $column_name ) {
        global $wpdb;

        $view_url = '';

        switch( $column_name ) {
            case 'name' :
                return isset($item->post_title) ? '<a href="'.$view_url.'">'.$item->post_title.'</a>' : '';

            default:
                $record = get_cf7_record($column_name, $item->record_id);

                if(isset($record->field_value) && !empty($record->field_value))
                    if (filter_var($record->field_value, FILTER_VALIDATE_URL)) 
                        return '<a target="_blank" href="'.$record->field_value.'" ><span class="dashicons dashicons-media-document"></span></a>';
                    else
                        return $record->field_value;
                else
                    return '';
        }
    }

    /**
     * column_cb function.
     * 
     * @access public
     * @param mixed $item
     */
    public function column_cb( $item ){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'record_id',
            $item->record_id
        );
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $cf7_id = isset($_REQUEST['cf7_id']) ? sanitize_text_field($_REQUEST['cf7_id']) : '';

        $columns = get_cf7_fields($cf7_id);

        if(empty($columns))
        {
            $columns = [];
        }

        $columns = apply_filters('wp_cf7_custom_addon_admin_list_columns', $columns);

        return $columns;
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /** 
     * Process bulk actions
     */
    public function process_bulk_action() {
        global $wpdb;
        
        if ( ! isset( $_POST['record_id'] ) ) {
            return;
        }
        
        $items = array_map( 'sanitize_text_field', $_POST['record_id'] );

        if ( $items ) {
            switch ( $this->current_action() ) {
                case 'delete' :
                    $ids = implode( "','", $items );
                    $wpdb->query( $wpdb->prepare("DELETE FROM %1s WHERE record_id IN('".$ids."')", $wpdb->prefix . 'wp_cf7_custom_addon_form_data') );
                    echo '<div class="updated"><p>' . esc_html(sizeof($items)) . ' record deleted' . '</p></div>';
                break;
            }
        }
    }

    /**
     * prepare_items function.
     * 
     * @access public
     */
    public function prepare_items() 
    {
        global $wpdb;
        
        $current_page   = $this->get_pagenum();
        $per_page       = 20;
        $orderby        = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id';
        $order          = ! empty( $_REQUEST['order'] ) &&  ( $_REQUEST['order'] === 'asc' ) ? 'ASC' : 'DESC';
        $cf7_id         = isset($_REQUEST['cf7_id']) ? sanitize_text_field($_REQUEST['cf7_id']) : '';

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        
        /**
         * Process bulk actions
         */
        $this->process_bulk_action();

        $where = array( 'WHERE 1=1' );
        
        $where[] = 'AND cf7_id="'.$cf7_id .'"';

        $where = implode( ' ', $where );

        $group_by = 'GROUP BY `record_id` ';
        
        /**
         * Get items
         */
        $wpdb->get_var( 
            $wpdb->prepare("SELECT COUNT(id) FROM %1s 
                $where 
                %1s 
                ORDER BY %1s %1s ", 
                $wpdb->prefix . 'wp_cf7_custom_addon_form_data', 
                $group_by, 
                $orderby, 
                $order) 
        );

        $max = $wpdb->num_rows;

        $this->items = $wpdb->get_results( 
            $wpdb->prepare("SELECT record_id FROM %1s 
                $where 
                %1s 
                ORDER BY %1s %1s LIMIT %d, %d ", 
                $wpdb->prefix . 'wp_cf7_custom_addon_form_data', 
                $group_by, 
                $orderby, 
                $order,
                ( $current_page - 1 ) * $per_page, 
                $per_page )
        );

        /**
         * Pagination
         */
        $this->set_pagination_args( array(
            'total_items' => $max, 
            'per_page'    => $per_page,
            'total_pages' => ceil( $max / $per_page )
        ) );

        
        /*echo '<pre>';
        print_r($wpdb->last_query);
        print_r($wpdb->last_result);
        echo '</pre>' . __FILE__ . ' ( Line Number ' . __LINE__ . ')';*/
        
    }	

}