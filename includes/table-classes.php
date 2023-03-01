<?php
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class Asset_List_Table extends WP_List_Table {
    public $items;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct() {
        parent::__construct( array(
            'singular'=> 'wp_list_eno_asset', //Singular label
            'plural' => 'wp_list_eno_assets', //plural label, also this well be one of the table css class
            'ajax'   => false //We won't support Ajax for this table
        ) );
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
//        if ( $which == "top" ){
//            //The code that goes before the table is here
//            echo '<h1>ENO Asset List</h1>';
//            echo 'Here you can view the list of assets ENO possesses, and if they are checked out or not.';
//        }
//        if ( $which == "bottom" ){
//            //The code that goes after the table is there
//            echo"Hi, I'm after the table";
//        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $columns = array(
            'col_asset_tag'=>__('Tag'),
            'col_asset_brand'=>__('Brand'),
            'col_asset_model'=>__('Model'),
            'col_asset_serial'=>__('Serial Number'),
            'col_asset_checked_out'=>__('Checked Out'),
            'col_asset_checked_out_user'=>__('Checked Out By'),
            'col_asset_checked_out_date'=>__('Checked Out At'),
            'col_asset_checked_out_from'=>__('Verifier'),
            'col_delete_item'=>__('Delete Item')
        );
    }

    // Adding action buttons to column
    function column_col_asset_tag($item)
    {
        $actions = array(
            'delete'    => sprintf('<a href="?page=%s&action=%s&tag=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['Tag']),
        );

        return sprintf('%1$s %2$s', $item['col_asset_tag'], $this->row_actions($actions));
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return $sortable = array(
            'col_asset_tag'=>'idTag',
            'col_asset_brand'=>'brand',
            'col_asset_model'=>'model',
            'col_asset_checked_out'=>'checkedOut',
            'col_asset_checked_out_user'=>'checkedOutUser',
            'col_asset_checked_out_date'=>'checkedOutDate',
            'col_asset_checked_out_from'=>'checkedOutFrom',
        );
    }

    function get_users_data($search = "", $args = "")
    {
        global $wpdb;

        if (!empty($search)) {
            return $wpdb->get_results(
                "SELECT idTag, brand, model, serialNumber, checkedOut, checkedOutUser, checkedOutDate, checkedOutFrom FROM wp_eno_assets WHERE idTag LIKE '%{$search}%' OR brand LIKE '%{$search}%' OR serialNumber LIKE '%{$search}%' OR checkedOut LIKE '%{$search}%' OR checkedOutUser LIKE '%{$search}%' OR checkedOutDate LIKE '%{$search}%' OR checkedOutFrom LIKE '%{$search}%' {$args}"
            );
        }else{
            return $wpdb->get_results(
                "SELECT * from wp_eno_assets {$args}"
            );
        }
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        $query = "";

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ASC';
        $order = !empty($_GET["order"]) ? $_GET["order"] : FALSE;
        if(!empty($orderby) & !empty($order)){ $query.='ORDER BY '.$orderby.' '.$order; }

        // delete
        if (isset($_GET['action']) && $_GET['page'] == "eno-asset-manager" && $_GET['action'] == "delete") {
            $empID = intval($_GET['tag']);

            $wpdb->delete('wp_eno_assets', array('idTag' => $empID));
        }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        if (isset($_POST['page']) && isset($_POST['s'])) {
            $totalitems = $wpdb->query("SELECT idTag, brand, model, serialNumber, checkedOut, checkedOutUser, checkedOutDate, checkedOutFrom FROM wp_eno_assets WHERE idTag LIKE '%{$_POST['s']}%' OR brand Like '%{$_POST['s']}%' OR serialNumber Like '%{$_POST['s']}%' OR checkedOut Like '%{$_POST['s']}%' OR checkedOutUser Like '%{$_POST['s']}%' OR checkedOutDate Like '%{$_POST['s']}%' OR checkedOutFrom Like '%{$_POST['s']}%'");
        } else {
            $totalitems = $wpdb->query("SELECT * FROM wp_eno_assets");
        }
        //How many to display per page?
        $perpage = 10;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : FALSE;
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)) {
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }
        // /* -- Register the pagination -- */
        $this->set_pagination_args( array(
        "total_items" => $totalitems,
         "total_pages" => $totalpages,
         "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $_wp_column_headers[$screen->id]=$columns;

        /* -- Fetch the items -- */
        if (isset($_POST['page']) && isset($_POST['s'])) {
            $this->items = $this->get_users_data($_POST['s'], $query);
        } else {
            $this->items = $this->get_users_data("", $query);
        }
    }

    /**
     * Display the rows of records in the table
     */
    function display_rows() {

        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();

        //Loop for each record
        if(!empty($records)){
            foreach($records as $rec){

                //Open the line
                echo '<tr id="record_'.$rec->idTag.'">';
                foreach ( $columns as $column_name => $column_display_name ) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    $num_length = strlen((string)$rec->idTag);
                    if($num_length == 6) {
                        $updatedId = ''.$rec->idTag;
                    } else {
                        switch ($num_length) {
                            case 1:
                                $updatedId = '00000'.$rec->idTag;
                                break;
                            case 2:
                                $updatedId = '0000'.$rec->idTag;
                                break;
                            case 3:
                                $updatedId = '000'.$rec->idTag;
                                break;
                            case 4:
                                $updatedId = '00'.$rec->idTag;
                                break;
                            case 5:
                                $updatedId = '0'.$rec->idTag;
                                break;
                            default:
                                $updatedId = '000000';
                                break;
                        }
                    }

                    $updatedCheckOut = "No";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOut = "Yes";
                    }

                    $updatedCheckOutUser = "N/A";
                    $updatedCheckOutDate = "N/A";
                    $updatedCheckOutFrom = "N/A";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOutUser = $rec->checkedOutUser;
                        $updatedCheckOutDate = $rec->checkedOutDate;
                        $updatedCheckOutFrom = $rec->checkedOutFrom;
                    }

                    //Display the cell
                    switch ( $column_name ) {
                        case "col_asset_tag": echo '<td '.$attributes.'>'.$updatedId.'</td>'; break;
                        case "col_asset_brand": echo '<td '.$attributes.'>'.$rec->brand.'</td>'; break;
                        case "col_asset_model": echo '<td '.$attributes.'>'.$rec->model.'</td>'; break;
                        case "col_asset_serial": echo '<td '.$attributes.'>'.$rec->serialNumber.'</td>'; break;
                        case "col_asset_checked_out": echo '<td '.$attributes.'>'.$updatedCheckOut.'</td>'; break;
                        case "col_asset_checked_out_user": echo '<td '.$attributes.'>'.$updatedCheckOutUser.'</td>'; break;
                        case "col_asset_checked_out_date": echo '<td '.$attributes.'>'.$updatedCheckOutDate.'</td>'; break;
                        case "col_asset_checked_out_from": echo '<td '.$attributes.'>'.$updatedCheckOutFrom.'</td>'; break;
                        case "col_delete_item": echo '<td '.$attributes.'>'.'<a href="?page='.$_REQUEST['page'].'&action=delete&tag='.$rec->idTag.'">Delete</a>'.'</td>'; break;
                    }
                }

                //Close the line
                echo'</tr>';
            }
        }
    }
}

class Check_List_Table extends WP_List_Table {
    public $items;
    public $ids = array();
    public function setIds($ids) {
        $this->ids = $ids;
    }
    public function getIds() {
        return $this->ids;
    }

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct() {
        parent::__construct( array(
            'singular'=> 'wp_list_eno_assets_check', //Singular label
            'plural' => 'wp_list_eno_assets_checks', //plural label, also this well be one of the table css class
            'ajax'   => false //We won't support Ajax for this table
        ) );

        $x_array = array();

        if(isset($_COOKIE['eno_checkout_amount'])) {
            if($_COOKIE['eno_checkout_amount'] != 0) {
                for ($x = 0; $x < $_COOKIE['eno_checkout_amount']; $x++) {
                    array_push($x_array, $_COOKIE['eno_checkout_id_'.$x]);
                }
            }
        }

        if(isset($_POST['idTag'])) {
            array_push($x_array, $_POST["idTag"]);
        }

        $this->setIds($x_array);

        if(array_key_exists('button1', $_POST)) {
            $this->setIds(array());
        }
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
//        if ( $which == "top" ){
//            //The code that goes before the table is here
//            echo '<h1>ENO Asset List</h1>';
//            echo 'Here you can view the list of assets ENO possesses, and if they are checked out or not.';
//        }
//        if ( $which == "bottom" ){
//            //The code that goes after the table is there
//            echo"Hi, I'm after the table";
//        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $columns = array(
            'col_asset_tag'=>__('Tag'),
            'col_asset_brand'=>__('Brand'),
            'col_asset_model'=>__('Model'),
            'col_asset_checked_out'=>__('Checked Out'),
            'col_asset_checked_out_user'=>__('Checked Out By'),
            'col_asset_remove'=>__('Remove Asset')
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return $sortable = array(
            'col_asset_tag'=>'idTag',
            'col_asset_brand'=>'brand',
            'col_asset_model'=>'model',
            'col_asset_checked_out'=>'checkedOut',
            'col_asset_checked_out_user'=>'checkedOutUser',
        );
    }

    function get_users_data($search = "", $args = "", $tids)
    {
        global $wpdb;

        if (!empty($search)) {
            return $wpdb->get_results(
                "SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE idTag IN (". implode(',', $tids) . ") {$args}"
            );
        }else{
            return $wpdb->get_results(
                "SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE idTag IN (". implode(',', $tids) . ") {$args}"
            );
        }
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        $query = "";

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ASC';
        $order = !empty($_GET["order"]) ? $_GET["order"] : FALSE;
        if(!empty($orderby) & !empty($order)){ $query.='ORDER BY '.$orderby.' '.$order; }

        // delete
        if (isset($_GET['action']) && $_GET['page'] == "eno-dashboard-asset-checkout" && $_GET['action'] == "remove") {
            $idTag = intval($_GET['tag']);

            $this->setIds(array_diff($this->getIds(), [$idTag]));
        }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query("SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE idTag IN (". implode(',', $this->getIds()) . ")");
        //How many to display per page?
        $perpage = 20;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : FALSE;
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)) {
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }
        // /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;

        /* -- Fetch the items -- */
        if (isset($_POST['page']) && isset($_POST['s'])) {
            $this->items = $this->get_users_data($_POST['s'], $query, $this->getIds());
        } else {
            $this->items = $this->get_users_data("", $query, $this->getIds());
        }
    }

    /**
     * Display the rows of records in the table
     */
    function display_rows() {

        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();

        //Loop for each record
        if(!empty($records)){
            foreach($records as $rec){

                //Open the line
                echo '<tr id="record_'.$rec->idTag.'">';
                foreach ( $columns as $column_name => $column_display_name ) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    $num_length = strlen((string)$rec->idTag);
                    if($num_length == 6) {
                        $updatedId = ''.$rec->idTag;
                    } else {
                        switch ($num_length) {
                            case 1:
                                $updatedId = '00000'.$rec->idTag;
                                break;
                            case 2:
                                $updatedId = '0000'.$rec->idTag;
                                break;
                            case 3:
                                $updatedId = '000'.$rec->idTag;
                                break;
                            case 4:
                                $updatedId = '00'.$rec->idTag;
                                break;
                            case 5:
                                $updatedId = '0'.$rec->idTag;
                                break;
                            default:
                                $updatedId = '000000';
                                break;
                        }
                    }

                    $updatedCheckOut = "No";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOut = "Yes";
                    }

                    $updatedCheckOutUser = "N/A";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOutUser = $rec->checkedOutUser;
                    }

                    //Display the cell
                    switch ( $column_name ) {
                        case "col_asset_tag": echo '<td '.$attributes.'>'.$updatedId.'</td>'; break;
                        case "col_asset_brand": echo '<td '.$attributes.'>'.$rec->brand.'</td>'; break;
                        case "col_asset_model": echo '<td '.$attributes.'>'.$rec->model.'</td>'; break;
                        case "col_asset_checked_out": echo '<td '.$attributes.'>'.$updatedCheckOut.'</td>'; break;
                        case "col_asset_checked_out_user": echo '<td '.$attributes.'>'.$updatedCheckOutUser.'</td>'; break;
                        case "col_asset_remove": echo '<td '.$attributes.'>'.'<a href="?page='.$_REQUEST['page'].'&action=remove&tag='.$rec->idTag.'">Remove</a>'.'</td>'; break;
                    }
                }

                //Close the line
                echo'</tr>';
            }
        }
    }
}

class Check_In_List_Table extends WP_List_Table {
    public $items;
    public $this_user;
    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct() {
        parent::__construct( array(
            'singular'=> 'wp_list_eno_assets_check_in', //Singular label
            'plural' => 'wp_list_eno_assets_check_ins', //plural label, also this well be one of the table css class
            'ajax'   => false //We won't support Ajax for this table
        ) );

        $this->this_user = wp_get_current_user();
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
//        if ( $which == "top" ){
//            //The code that goes before the table is here
//            echo '<h1>ENO Asset List</h1>';
//            echo 'Here you can view the list of assets ENO possesses, and if they are checked out or not.';
//        }
//        if ( $which == "bottom" ){
//            //The code that goes after the table is there
//            echo"Hi, I'm after the table";
//        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $columns = array(
            'col_asset_tag'=>__('Tag'),
            'col_asset_brand'=>__('Brand'),
            'col_asset_model'=>__('Model'),
            'col_asset_checked_out'=>__('Checked Out'),
            'col_asset_checked_out_user'=>__('Checked Out By'),
            'col_asset_remove'=>__('Check In Asset')
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return $sortable = array(
            'col_asset_tag'=>'idTag',
            'col_asset_brand'=>'brand',
            'col_asset_model'=>'model',
            'col_asset_checked_out'=>'checkedOut',
            'col_asset_checked_out_user'=>'checkedOutUser',
        );
    }

    function get_users_data($search = "", $args = "", $tids)
    {
        global $wpdb;

        if (!empty($search)) {
            return $wpdb->get_results(
                "SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE checkedOutUser LIKE '%{$this->this_user->display_name}%' {$args}"
            );
        }else{
            return $wpdb->get_results(
                "SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE checkedOutUser LIKE '%{$this->this_user->display_name}%' {$args}"
            );
        }
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        $query = "";

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ASC';
        $order = !empty($_GET["order"]) ? $_GET["order"] : FALSE;
        if(!empty($orderby) & !empty($order)){ $query.='ORDER BY '.$orderby.' '.$order; }

        // delete
        if (isset($_GET['action']) && $_GET['page'] == "eno-dashboard-asset-checkin" && $_GET['action'] == "checkin") {
            $idTag = intval($_GET['tag']);

            $wpdb->update('wp_eno_assets', array('checkedOut' => FALSE, 'checkedOutUser' => null, 'checkedOutDate' => null, 'checkedOutFrom' => null), array('idTag' => $idTag));
        }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query("SELECT idTag, brand, model, checkedOut, checkedOutUser FROM wp_eno_assets WHERE checkedOutUser LIKE '%{$this->this_user->display_name}%'");
        //How many to display per page?
        $perpage = 20;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : FALSE;
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)) {
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }
        // /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;

        /* -- Fetch the items -- */
        if (isset($_POST['page']) && isset($_POST['s'])) {
            $this->items = $this->get_users_data($_POST['s'], $query, $this->getIds());
        } else {
            $this->items = $this->get_users_data("", $query, $this->getIds());
        }
    }

    /**
     * Display the rows of records in the table
     */
    function display_rows() {

        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();

        //Loop for each record
        if(!empty($records)){
            foreach($records as $rec){

                //Open the line
                echo '<tr id="record_'.$rec->idTag.'">';
                foreach ( $columns as $column_name => $column_display_name ) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    $num_length = strlen((string)$rec->idTag);
                    if($num_length == 6) {
                        $updatedId = ''.$rec->idTag;
                    } else {
                        switch ($num_length) {
                            case 1:
                                $updatedId = '00000'.$rec->idTag;
                                break;
                            case 2:
                                $updatedId = '0000'.$rec->idTag;
                                break;
                            case 3:
                                $updatedId = '000'.$rec->idTag;
                                break;
                            case 4:
                                $updatedId = '00'.$rec->idTag;
                                break;
                            case 5:
                                $updatedId = '0'.$rec->idTag;
                                break;
                            default:
                                $updatedId = '000000';
                                break;
                        }
                    }

                    $updatedCheckOut = "No";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOut = "Yes";
                    }

                    $updatedCheckOutUser = "N/A";

                    if($rec->checkedOut == 1) {
                        $updatedCheckOutUser = $rec->checkedOutUser;
                    }

                    //Display the cell
                    switch ( $column_name ) {
                        case "col_asset_tag": echo '<td '.$attributes.'>'.$updatedId.'</td>'; break;
                        case "col_asset_brand": echo '<td '.$attributes.'>'.$rec->brand.'</td>'; break;
                        case "col_asset_model": echo '<td '.$attributes.'>'.$rec->model.'</td>'; break;
                        case "col_asset_checked_out": echo '<td '.$attributes.'>'.$updatedCheckOut.'</td>'; break;
                        case "col_asset_checked_out_user": echo '<td '.$attributes.'>'.$updatedCheckOutUser.'</td>'; break;
                        case "col_asset_remove": echo '<td '.$attributes.'>'.'<a href="?page='.$_REQUEST['page'].'&action=checkin&tag='.$rec->idTag.'">Check In</a>'.'</td>'; break;
                    }
                }

                //Close the line
                echo'</tr>';
            }
        }
    }
}