<?php

/*
Plugin Name: Property Manager
Plugin URI: http://trangdunlap.com
Description: Get properties from zillow
Version: 1.0
License: GPL
Author: Matt Dunlap
Author URI: http://trangdunlap.com
*/

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define(PLUGINPATH, WP_PLUGIN_DIR."/".plugin_basename('properties'));
define(PLUGINURL, WP_PLUGIN_URL."/".plugin_basename('properties'));
require_once("zillow.class.php");

class ManageProperties
{

	function __construct()
	{
        global $wpdb, $table_prefix;
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_prefix}wp_properties` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `address` VARCHAR(255) NULL ,
                `city` VARCHAR(255) NULL ,
                `state` VARCHAR(255) NULL ,
                `zip` VARCHAR(10) NULL ,
                `price` DECIMAL NULL ,
                `description` TEXT, 
                `status` VARCHAR(45),
                PRIMARY Key (`id`));";
        $wpdb->query($sql);

        $sql = "CREATE  TABLE `".$table_prefix."property_images` (
          `id` INT NOT NULL AUTO_INCREMENT ,
          `property_id` INT NOT NULL ,
          `image_url` VARCHAR(255) NOT NULL ,
          `main_image` TINYINT NOT NULL,
          PRIMARY KEY (`id`) );";
        $wpdb->query($sql);
        

		//hook up with Wordpress and make sweet love...
		add_action('admin_enqueue_scripts', array(&$this,'load_scripts' ));
		add_action('admin_menu', array(&$this,'adminMenu'));
		add_action('wp_ajax_insert_property_image', array(&$this, 'insert_property_image'));
        add_action('wp_ajax_update_main_property_image', array(&$this, 'update_main_property_image'));
		add_action('wp_ajax_update_description', array(&$this, 'update_description'));
        add_shortcode( 'properties', array(&$this, 'displayPropertyThumbs' ));
        add_shortcode( 'property', array(&$this, 'displayProperty' ));
		
	}

	function load_scripts(){
		wp_register_style( 'prefix-style', plugins_url('bootstrap/css/bootstrap.css', __FILE__) );
        
		if(function_exists( 'wp_enqueue_media' )){
		    wp_enqueue_media();
		}else{
		    wp_enqueue_style('thickbox');
		    wp_enqueue_script('media-upload');
		    wp_enqueue_script('thickbox');
		}
	}


	function my_plugin_admin_styles() {
       
       wp_enqueue_style( 'prefix-style' );
       wp_enqueue_script( 'boostrap_script' );
       wp_enqueue_script( 'holder_script' );
       wp_enqueue_script( 'functions_script' );
    }

    function displayProperty(){

        global $wpdb, $table_prefix;
        $id = $_GET['id'];

        $sql = "SELECT p.address, p.description, pi.image_url FROM {$table_prefix}properties p
            INNER JOIN {$table_prefix}property_images pi
            ON pi.property_id = p.id AND pi.main_image = 1
            LIMIT 1";

        $property = $wpdb->get_row($sql);

        include(PLUGINPATH . "/views/property.php");

    }

    /**
     * $options values
     * -------------------
     * total - number of properties to show
     * 
     * @param Array $options 
     * @return String
     */
    function displayPropertyThumbs($options){
        global $wpdb, $table_prefix;
        $total = isset($options['total'])?$options['total']:1;

        $sql = "SELECT p.address, p.description, pi.image_url FROM {$table_prefix}properties p
            INNER JOIN {$table_prefix}property_images pi
            ON pi.property_id = p.id AND pi.main_image = 1
            ORDER BY rand() limit {$total}";

        $results = $wpdb->get_results($sql);
        $output = '';
        $count = 0;
        $total = sizeof($results);
        foreach($results as $result){
            $count++;
            $startDiv = ($count == $total)?'<div class="one_fourth last">':'<div class="one_fourth">';
            $output .= $startDiv . 
                    '<a href="/property?id=21"><span class="imageframe imageframe-glow"><img alt="" src="'.$result->image_url.'"></span></a>
                    <p></p>
                    <h2>'.$result->address.'</h2>
                    <p>'.$result->description.'
                    </p></div>';
        }

        return $output;
        
    }
    

	function adminMenu()
	{
		global $wpdb, $table_prefix;
		
        wp_register_script( 'boostrap_script', plugins_url('bootstrap/js/bootstrap.js', __FILE__) );
        wp_register_script( 'holder_script', plugins_url('bootstrap/js/holder.js', __FILE__) );
        wp_register_script( 'functions_script', plugins_url('bootstrap/js/functions.js', __FILE__) );

		$managePage = add_menu_page('Manage Properties', 'Manage Properties', 0, 'manage_properties', array(&$this,'manageProperties'));
		$editPage = add_submenu_page('manage_properties', 'Edit', '', 0, 'edit_property', array(&$this, 'edit') );
		$addPage = add_submenu_page('manage_properties', 'Add', 'Add Property', 0, 'add_property', array(&$this, 'add') );
		//add_submenu_page('manage_properties', 'Ajax', '', 0, 'insert_property', array(&$this, 'insert_property') );
		add_action( 'admin_print_styles-' . $managePage, array(&$this, 'my_plugin_admin_styles'));
		add_action( 'admin_print_styles-' . $editPage, array(&$this, 'my_plugin_admin_styles'));
		add_action( 'admin_print_styles-' . $addPage, array(&$this, 'my_plugin_admin_styles'));

	}

    function update_description(){
        global $wpdb, $table_prefix;
        $id = $_POST['id'];
        $description = $_POST['description'];

        $sql = "UPDATE {$table_prefix}properties set description = '".mysql_real_escape_string($description)."' WHERE id = $id";
        $affected = $wpdb->query($sql);

        wp_send_json(array($affected));
    }

    /**
     * Inserts images into the database
     * 
     */
	function insert_property_image(){
		global $wpdb, $table_prefix;

        $files = $_POST['images'];
        $id = $_POST['id'];
        $response = array();
        foreach($files as $file){
            $wpdb->query("INSERT IGNORE INTO {$table_prefix}property_images (image_url, property_id) VALUES ('".$file."', '".$id."');");
            if($wpdb->insert_id){
                $response[] = $file;
            }
        }
		wp_send_json($response);
		
	}

    /**
     * Sets the main image
     */
    function update_main_property_image(){
        global $wpdb, $table_prefix;

        $image = $_POST['image'];
        $id = $_POST['id'];
        $response = array();

        // delete the main image
        $wpdb->query("DELETE FROM {$table_prefix}property_images WHERE property_id = {$id} AND main_image = 1;");

        //insert the main image
        $wpdb->query("INSERT IGNORE INTO {$table_prefix}property_images (image_url, property_id, main_image) VALUES ('".$image."', '".$id."', 1);");
        if($wpdb->insert_id){
            $response[] = $file;
        }
        
        wp_send_json($response);
    }

    /**
    * adds a new property
    * TODO: call zillow class and save the information in the database
    *   Then present to the user
    */
	function add(){
		?>
		<style>
       	input[type='text'] {
		  height: 30px; !important
		}
		</style>
       	<?

       	if(isset($_POST['submit'])){
       		global $wpdb, $table_prefix;
       		$data = array();
       		$data['address'] = $_POST['address'];
       		$data['city'] = $_POST['city'];
       		$data['state'] = $_POST['state'];
       		$data['zip'] = $_POST['zipcode'];
       		$wpdb->insert( $table_prefix."properties", $data);

       		$property_id = $wpdb->insert_id;
       		?>
       		<META http-equiv="refresh" content="0;URL=?page=edit_property&property_id=<?php echo $property_id?>">
       		<?
       	} else {
			include(PLUGINPATH . "/views/add.php");
		}
	}

	function edit(){
        global $wpdb, $table_prefix;

        if(isset($_POST['submit'])){
            
            $data = array();
            $data['description'] = $_POST['description'];
            $data['status'] = $_POST['status'];
            $data['price'] = $_POST['price'];
            $where['id'] = $property_id = $_POST['id'];
            $wpdb->update( $table_prefix."properties", $data, $where);

            ?>
            <META http-equiv="refresh" content="0;URL=?page=edit_property&property_id=<?php echo $property_id?>">
            <?
            return;
        } 

        ?>
        <style>
        input[type='text'] {
          height: 30px; !important
        }
        </style>
        <?

		$id = mysql_real_escape_string($_GET['property_id']);
		$sql = "SELECT * FROM ".$table_prefix."properties WHERE id = {$id}";
		$property = $wpdb->get_row($sql);
		$propertyObj = new Property();
		$propertyObj->address = $prop->address;
		$propertyObj->city = $prop->city;
		$propertyObj->state = $prop->state;

        $thumbnails = $wpdb->get_results("SELECT * FROM {$table_prefix}property_images WHERE property_id = {$id}");
        $main_image = null;
        foreach($thumbnails as $thumbnail){
            if($thumbnail->main_image == 1){
                $main_image = $thumbnail->image_url;
                break;
            }
        }
        
		$propertyObj->deepSearchResults();
		
		include(PLUGINPATH . "/views/edit.php");
	}

	function manageProperties()
	{
	    global $wpdb, $table_prefix;
	    $sql = "SELECT * FROM ".$table_prefix."properties";
	    $properties = $wpdb->get_results("SELECT * FROM ".$table_prefix."properties" , ARRAY_A);
		include(PLUGINPATH . "/views/index.php");
	}


}

$mp = new ManageProperties;


class Property_List extends WP_List_Table {
    

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Property',     //singular name of the listed records
            'plural'    => 'Properties',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'address':
            	$edit = "<div><a href='?page=edit_property&property_id=".$item['id']."'>Edit</a></div>";
            	return ucfirst($item[$column_name]).$edit;
            case 'state':
            	return strtoupper($item[$column_name]);
            case 'city':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['address'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'address'   => 'Address',
            'city'      => 'City',
            'state'     => 'State'
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'address'     => array('address',false),     //true means it's already sorted
            'city'    => array('city',false),
            'state'  => array('state',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }
    
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items($data) {
        global $wpdb; //This is used only if making any database queries

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
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
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
        //$data = $this->example_data;
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
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
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}