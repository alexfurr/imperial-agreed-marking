<?php

$agreedMarkingCPT = new agreedMarkingCPT();
class agreedMarkingCPT
{


	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}


/*	---------------------------
	PRIMARY HOOKS INTO WP
	--------------------------- */
	function addWPActions ()
	{
		//Admin Menu
		add_action( 'init',  array( $this, 'create_CPTs' ) );

		// Remove and add columns in the projects table
		add_filter( 'manage_agreed-marking_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );
		add_action('manage_agreed-marking_posts_custom_column', array($this, 'customColumnContent'), 10, 2);

	}


/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS
	--------------------------- */
	function create_CPTs ()
	{


		//Projects
		$labels = array(
			'name'               =>  'Marking Assignment',
			'singular_name'      =>  'Marking Assignment',
			'menu_name'          =>  'Agreed Marking Assignments',
			'name_admin_bar'     =>  'Marking Assignments',
			'add_new'            =>  'Add New Marking Assignment',
			'add_new_item'       =>  'Add New Marking Assignment',
			'new_item'           =>  'New Marking Assignment',
			'edit_item'          =>  'Edit Marking Assignment',
			'view_item'          => 'View Marking Assignments',
			'all_items'          => 'All Marking Assignments',
			'search_items'       => 'Search Marking Assignments',
			'parent_item_colon'  => '',
			'not_found'          => 'No Marking Assignments found.',
			'not_found_in_trash' => 'No Marking Assignments found in Trash.'
		);

      $args = array(
			'labels'            	=> $labels,
         "menu_icon"          => "dashicons-yes",
			'public'            	=> false,
			'exclude_from_search'	=> true,
			'publicly_queryable' 	=> false,
			'show_ui'            	=> true,
			'show_in_nav_menus'		=> false,
			'show_in_menu'      	=> true,
			'query_var'         	=> true,
			'rewrite'           	=> false,
			'capability_type'   	=> 'post',
			'has_archive'       	=> true,
			'hierarchical'      	=> false,
			'menu_position'     	=> 65,
			'supports'          	=> array( 'title'  )

		);

		register_post_type( 'agreed-marking', $args );
	}


   // Remove Date Columns on projects
	function my_custom_post_columns( $columns  )
	{

	  	// Remove Date
		unset(
		$columns['date']
		);
		// Remove Checkbox
		unset(
		$columns['cb']
		);



		$columns['shortcode'] = 'Shortcode';

		return $columns;
	}



	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{

		switch ($column_name)
		{

         case "shortcode":

            echo '[agreed-marking id='.$post_ID.']';


				//echo '<a href="options.php?page=as-pfeedack-project-groups&projectID='.$post_ID.'">View / Edit Groups</a>';
			break;


		}
	}











} //Close class
?>
