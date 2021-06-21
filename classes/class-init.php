<?php
$agreedMarking = new agreedMarking();
class agreedMarking
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
		//Add Front End Jquery and CSS
      add_action( 'wp_head', array( $this, 'frontendEnqueues' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueues' ) );
      add_action( 'wp_enqueue_scripts', array( $this, 'frontendEnqueues' ) );


		// Setup shortcodes
		add_shortcode('agreed-marking', array('agreedMarkingDraw','drawAgreedMarkingPage'));


	}


   function adminEnqueues()
   {
      global $wp_scripts;
		$queryui = $wp_scripts->query('jquery-ui-core');

      wp_enqueue_script('jquery');

      //wp_enqueue_script( 'jquery-ui-mouse' );
      //wp_enqueue_script( 'jquery-ui-draggable' );
      //wp_enqueue_script( 'jquery-ui-droppable' );
      wp_enqueue_script( 'jquery-ui-sortable' );

		// load the jquery ui theme for datepicker
		//$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";
		//wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

      //wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
      //wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );

      wp_enqueue_style( 'imperial-agreed-marking-styles', AGREED_MARKING_URL . '/css/styles.css', array(), 0.1 );



   }


	function frontendEnqueues ()
	{

      wp_enqueue_script('jquery');

      // Global  Styles
      wp_enqueue_style( 'imperial-agreed-marking-styles', AGREED_MARKING_URL . '/css/styles.css', array(), 0.3 );
      wp_enqueue_script('imperial-agreed-marking-js', AGREED_MARKING_URL. '/js/scripts.js', array(), 0.1 );

      //wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
		//wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );


        wp_enqueue_script('imperial-offline-js', AGREED_MARKING_URL. '/js/offline.js', array('jquery'), 0.1 );
        wp_enqueue_script('imperial-agreed-marking-js', AGREED_MARKING_URL. '/js/scripts.js', array('jquery'), 0.1 );

	}


}
?>
