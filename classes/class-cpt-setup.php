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


      // Add duplicate options
      add_filter( 'post_row_actions', array($this, 'custom_quick_links'), 10, 2 );

      //Check for Duplciate
      add_action( 'plugins_loaded', array($this, 'checkForAgreedmarkingActions') );



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
      $columns['status'] = 'Status';

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

         case "status":
            $archived = get_post_meta( $post_ID, 'archived', true );
            if($archived==true)
            {
               echo 'Archived';
            }
            else
            {
               echo 'Active';
            }
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

      //Dates Metabox
      $id 			= 'agreed_meta';
      $title 			= 'Assessment Dates';
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


      //Dates Metabox
      $id 			= 'capped_meta';
      $title 			= 'Capped Marks';
      $drawCallback 	= array( $this, 'drawCappedMetaBox' );
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

      //Archive Metabox
      $id 			= 'archive_meta';
      $title 			= 'Archive Options';
      $drawCallback 	= array( $this, 'drawArchiveMetaBox' );
      $screen 		= 'agreed-marking';
      $context 		= 'normal';
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
      $releaseDate = get_post_meta( $post->ID, 'releaseDate', true );


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

      echo '<hr/>';

      echo '<label for="assessmentDate">Marks release date:';
      echo '<script>
         jQuery(document).ready(function() {
            jQuery("#releaseDate").datepicker({
               dateFormat : "yy-mm-dd"
            });

         });
      </script>';
      echo '<input type="text" id="releaseDate" name="releaseDate" value="'.$releaseDate.'">';
      echo '</label>';


   }

   function drawCappedMetaBox($post,$callbackArgs)
   {
      echo 'Capped Mark Percent<br/>';
      $cappedMarks = get_post_meta( $post->ID, 'cappedMarks', true );
      if($cappedMarks==""){$cappedMarks = 40;}
      echo '<input size="3" id="cappedMarks" name="cappedMarks" value="'.$cappedMarks.'" />%';
   }

   function drawArchiveMetaBox($post,$callbackArgs)
   {
      $isArchived = get_post_meta( $post->ID, 'archived', true );

       echo 'Archiving an assignment means that marks cannot be edited by assessors and no futher changes can be made including:
       <ul>
       <li>- Removing markers / students</li>
       <li>- Addig /editing criteria</li>
       <li>- Removing marker submissions</li>';

       echo '<h4>Assignment Status</h4>';
       echo '<label for="archiveAssignment">';
       echo '<select id="archiveAssignment" name="archiveAssignment">';
       echo '<option value=""';
       if($isArchived<>true){echo ' selected';}
       echo '>Not Archived</option>';
       echo '<option value="true"';
       if($isArchived==true){echo ' selected';}
       echo '>Archived</option>';
       echo '</select>';
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

         // Save the actual meta
         $releaseDate = $_POST['releaseDate'];
         update_post_meta( $postID, 'releaseDate', $releaseDate );

         // Save the archive
         $archiveAssignment = $_POST['archiveAssignment'];
         update_post_meta( $postID, 'archived', $archiveAssignment );

         $cappedMarks = $_POST['cappedMarks'];
         update_post_meta( $postID, 'cappedMarks', $cappedMarks );



      }
   }


   // Remove the quick edit from this post type
   function custom_quick_links( $actions = array(), $post = null ) {

      // Abort if the post type is not "ek_question"
      if ( ! is_post_type_archive( 'agreed-marking' ) ) {
         return $actions;
      }
      // Remove the Quick Edit link
      if ( isset( $actions['inline hide-if-no-js'] ) ) {
         unset( $actions['inline hide-if-no-js'] );


      }

      if (current_user_can('edit_posts'))
      {

         $assignmentID = $post->ID;

         //$duplicateString.='<form method="post" action="edit.php?post_type=ek_question&potID='.$postParentID.'&action=ek_question_duplicate">';
        // $duplicateString='<a href="edit.php?post_type=agreed-marking&assignmentID='.$assignmentID.'&myAction=duplicateAssignment">Duplicate</a>';

        $actionURL = 'edit.php?post_type=agreed-marking&assignmentID='.$assignmentID.'&myAction=duplicateAssignment';
         $actions['duplicate'] = '<a href="'.$actionURL.'" title="Duplicate" rel="permalink">Duplicate</a>';

       //  $actions['duplicate'] = $duplicateString;


      }
      // Return the set of links without Quick Edit
      return $actions;
   }

   public function checkForAgreedmarkingActions()
   {



      if(!isset($_GET['assignmentID']) )
      {
         return;
      }

      $assignmentID = $_GET['assignmentID'];

      $post = get_post($assignmentID);
      $typenow = $post->post_type;

      if( 'agreed-marking' != $typenow )
          return;

      if( isset( $_GET['myAction'] ) )
      {
          $action =$_GET['myAction'];

          switch ($action)
          {
             case "duplicateAssignment":


               if(current_user_can('delete_pages') )
               {

                  agreedMarkingActions::duplicateAssignment($assignmentID);
                  wp_redirect( admin_url( '/edit.php?post_type=agreed-marking' ) );
                  exit;
               }
             break;
          }
      }
   }



} //Close class
?>
