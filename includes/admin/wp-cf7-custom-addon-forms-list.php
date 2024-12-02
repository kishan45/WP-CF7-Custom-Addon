<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WP_CF7_Custom_Addon Forms_List
 *
 * @class    WP_CF7_Custom_Addon_Forms_List
 * @package  WP_CF7_Custom_Addon\Forms_List
 * @version  1.0.0
 */

/**
 * WP_CF7_Custom_Addon_Forms_List class.
 */
class WP_CF7_Custom_Addon_Forms_List extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		parent::__construct(array(
            'singular' => 'cf7_form', //Singular label
            'plural'   => 'cf7_forms', //plural label, also this well be one of the table css class
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

        $view_url = add_query_arg( array(
                        'page'      => 'wp-cf7-custom-addon-db',
                        'cf7_id'    => $item->ID,
                    ), admin_url('admin.php') );

        switch( $column_name ) {
            case 'name' :
                return isset($item->post_title) ? '<a href="'.$view_url.'">'.$item->post_title.'</a>' : '';

            case 'count' :
                $count = get_cf7_records_count($item->ID);
                return '<a href="'.$view_url.'">'.$count.'</a>';

            default:
                return $item->$column_name;
        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        return $columns = array(
            'name'       => __('Name', 'wp-cf7-custom-addon'),
            'count' => __('Count', 'wp-cf7-custom-addon'),
        );
    }

    /**
     * prepare_items function.
     * 
     * @access public
     */
    public function prepare_items() {
        global $wpdb;
        
        $current_page   = $this->get_pagenum();
        $per_page       = 20;
        $orderby        = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'ID';
        $order          = ! empty( $_REQUEST['order'] ) &&  ( $_REQUEST['order'] === 'asc' ) ? 'ASC' : 'DESC';

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        
        /**
         * Process bulk actions
         */
        $this->process_bulk_action();

        $where = array( 'WHERE 1=1' );
        
        $where[] = 'AND post_type="wpcf7_contact_form"';
        $where[] = 'AND post_status="publish"';

        $where = implode( ' ', $where );
        
        /**
         * Get items
         */
        $max = $wpdb->get_var( 
            $wpdb->prepare("SELECT COUNT(ID) FROM %1s 
                $where 
                ORDER BY %1s %1s ", 
                $wpdb->prefix . 'posts', 
                $orderby, 
                $order) 
        );

        $this->items = $wpdb->get_results( 
            $wpdb->prepare("SELECT * FROM %1s 
                $where 
                ORDER BY %1s %1s LIMIT %d, %d ", 
                $wpdb->prefix . 'posts', 
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