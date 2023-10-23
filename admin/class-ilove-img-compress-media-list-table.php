<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Custom table class for managing and displaying compressed media information in the WordPress media library.
 *
 * This class extends the WordPress WP_List_Table class to create a custom table for managing and displaying
 * information related to the compression of media files within the WordPress media library.
 *
 * @since 1.0.0
 */
class Ilove_Img_Compress_Media_List_Table extends WP_List_Table {

    /**
	 * Variable to track the total number of items.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int    total_items    The total number of items.
	 */
    public $total_items;

    /**
     * Constructor method for the Image_List_Table class.
     *
     * Initializes the image list table, setting up parent defaults and properties.
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $status, $page;

        // Set parent defaults
        parent::__construct(
            array(
				'singular' => 'image',     // singular name of the listed records
				'plural'   => 'images',    // plural name of the listed records
				'ajax'     => false,       // does this table support ajax?
            )
        );
    }

    /**
     * Default callback method for rendering table columns in the Image_List_Table class.
     *
     * Renders the content for each column based on the specified column name.
     *
     * @since 1.0.0
     *
     * @param array  $item         The current row's data as an associative array.
     * @param string $column_name  The name of the current column being rendered.
     *
     * @return string              The HTML content to display in the specified column.
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'file':
                return "<a href='" . admin_url( 'post.php?post=' . $item['ID'] . '&action=edit' ) . "'>" . wp_get_attachment_image( $item['ID'] ) . $item['post_title'] . '</a>';
            case 'post_author':
                return get_the_author_meta( 'nickname', $item[ $column_name ] );
            case 'post_date':
                return $item[ $column_name ];
            case 'status':
                return Ilove_Img_Compress_Resources::get_status_of_column( $item['ID'] );
            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Callback method for rendering the checkbox column in the Image_List_Table class.
     *
     * Renders a checkbox input for selecting items in the table.
     *
     * @since 1.0.0
     *
     * @param array $item The current row's data as an associative array.
     *
     * @return string The HTML content for the checkbox column.
     */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  // Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                // The value of the checkbox should be the record's id
        );
    }

    /**
     * Define the columns for the table in the Image_List_Table class.
     *
     * @since 1.0.0
     *
     * @return array An associative array of column names and their corresponding labels.
     */
    public function get_columns() {
        $columns = array(
            'cb'          => '<input type="checkbox" />', // Render a checkbox instead of text
            'file'        => 'File',
            'post_author' => 'Author',
            'post_date'   => 'Date',
            'status'      => 'Status',
        );
        return $columns;
    }

    /**
     * Define sortable columns for the table in the Image_List_Table class.
     *
     * @since 1.0.0
     *
     * @return array An associative array of sortable column names and their sorting options.
     */
    protected function get_sortable_columns() {
        $sortable_columns = array(
            'file'        => array( 'file', false ),     // true means it's already sorted
            'post_author' => array( 'post_author', false ),
            'post_date'   => array( 'post_date', false ),
        );
        return $sortable_columns;
    }

    /**
     * Define bulk actions for the table in the Image_List_Table class.
     *
     * @since 1.0.0
     *
     * @return array An associative array of bulk action names and their labels.
     */
    protected function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    /**
     * Process bulk actions in the Image_List_Table class.
     *
     * Detects and handles bulk actions triggered by the user.
     *
     * @since 1.0.0
     */
    public function process_bulk_action() {

        // Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            wp_die( 'Items deleted (or they would be if we had items to delete)!' );
        }
    }

    /**
     * Prepares the items and pagination for the Image_List_Table class.
     *
     * Initializes and prepares data for displaying in the table, including pagination.
     *
     * @since 1.0.0
     */
    public function prepare_items() {
        global $wpdb; // This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();

        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $order = 'ORDER BY post_date DESC';
        if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
            $order = 'ORDER BY ' . sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) . ' ' . sanitize_text_field( wp_unslash( $_GET['order'] ) );
            if ( 'file' === $_GET['orderby'] ) {
                $order = 'ORDER BY post_title ' . sanitize_text_field( wp_unslash( $_GET['order'] ) );
            }
        }

        $data = $wpdb->get_results( // phpcs:ignore
            "
            SELECT {$wpdb->prefix}posts.* 
            FROM {$wpdb->prefix}posts
            WHERE {$wpdb->prefix}posts.post_type = 'attachment' AND 
                {$wpdb->prefix}posts.post_mime_type IN ('image/jpg', 'image/jpeg', 'image/png', 'image/gif') AND 
                {$wpdb->prefix}posts.ID NOT IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE {$wpdb->prefix}postmeta.meta_key = 'iloveimg_status_compress' AND {$wpdb->prefix}postmeta.meta_value = 2)
                " . $order, // phpcs:ignore
            ARRAY_A
        );
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items       = count( $data );
        $per_page          = $total_items;
        $this->total_items = $total_items;

        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(
            array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page
				'total_pages' => ( $per_page ) ? ceil( $total_items / $per_page ) : 0,   // WE have to calculate the total number of pages
            )
        );
    }
}
