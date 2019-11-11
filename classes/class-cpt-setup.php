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

      add_action( 'admin_menu', array( $this, 'create_AdminPages' ));

      add_action( 'save_post', array($this, 'savePostMeta' ), 10 );

      add_action( 'add_meta_boxes_agreed-marking', array( $this, 'addMetaBoxes' ));

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

     // $columns['shortcode'] = 'Shortcode';
      $columns['criteria'] = 'Marking Criteria';

      $columns['users'] = 'Markers and Students';
      $columns['assessmentDate'] = 'Assessment Date';

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

         case "users":
            echo '<a href="options.php?page=agreed-marking-users&id='.$post_ID.'">Edit Markers and Students</a>';
         break;

         case "criteria":
            echo '<a href="options.php?page=agreed-marking-criteria&id='.$post_ID.'">Edit Criteria</a>';
         break;

         case "assessmentDate":
            $assessmentDate = get_post_meta( $post_ID, 'assessmentDate', true );
            echo $assessmentDate;
         break;

		}
	}


   function create_AdminPages()
   {
      /* Users Page */
      $parentSlug = "no_parent";
      $page_title="Markers and Students";
      $menu_title="";
      $menu_slug="agreed-marking-users";
      $function=  array( $this, 'drawUsersPage' );
      $myCapability = "delete_pages";
      add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

      /* Criteria Page */
      $parentSlug = "no_parent";
      $page_title="Marking Criteria";
      $menu_title="";
      $menu_slug="agreed-marking-criteria";
      $function=  array( $this, 'drawCriteriaPage' );
      $myCapability = "delete_pages";
      add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

   }

   function drawUsersPage	()
   {
      require_once AGREED_MARKING_PATH.'admin/users.php'; # Grade Boundaries
   }

   function drawCriteriaPage()
   {
      require_once AGREED_MARKING_PATH.'admin/criteria.php'; # Grade Boundaries
   }

   // Register the metaboxes on  CPT
   function  addMetaBoxes()
   {

      global $post;

      //Quiz Meta Metabox
      $id 			= 'agreed_meta';
      $title 			= 'Assessment Date';
      $drawCallback 	= array( $this, 'drawMetaBox' );
      $screen 		= 'agreed-marking';
      $context 		= 'side';
      $priority 		= 'default';
      $callbackArgs 	= array();

      add_meta_box(
         $id,
         $title,
         $drawCallback,
         $screen,
         $context,
         $priority,
         $callbackArgs
      );
   }

   function drawMetaBox($post,$callbackArgs)
   {
      $assessmentDate = get_post_meta( $post->ID, 'assessmentDate', true );


      wp_nonce_field( 'save_agreed_metabox_nonce', 'agreed_metabox_nonce' );

      echo '<label for="assessmentDate">Assessment Date:';
      echo '<script>
         jQuery(document).ready(function() {
            jQuery("#assessmentDate").datepicker({
               dateFormat : "yy-mm-dd"
            });

         });
      </script>';
      echo '<input type="text" id="assessmentDate" name="assessmentDate" value="'.$assessmentDate.'">';
      echo '</label>';


   }


   // Save metabox data on edit slide
   function savePostMeta ( $postID )
   {
      global $post_type;
      global $post;

      if($post_type=="agreed-marking")
      {

         // Check if nonce is set.
         if ( ! isset( $_POST['agreed_metabox_nonce'] ) ) {
            return;
         }

         // Verify that the nonce is valid.
         if ( ! wp_verify_nonce( $_POST['agreed_metabox_nonce'], 'save_agreed_metabox_nonce' ) ) {
            return;
         }

         // If this is an autosave, our form has not been submitted, so we don't want to do anything.
         if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
         }

         // Check the user's permissions.
         if ( ! current_user_can( 'edit_post', $postID ) ) {
            return;
         }

         // check if there was a multisite switch before
         if ( is_multisite() && ms_is_switched() ) {
            return $post_id;
         }

         // Save the actual meta
         $assessmentDate = $_POST['assessmentDate'];
         update_post_meta( $postID, 'assessmentDate', $assessmentDate );


      }
   }

} //Close class
?>
