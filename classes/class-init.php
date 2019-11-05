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
      add_action( 'admin_enqueue_scripts', array( $this, 'frontendEnqueues' ) );


		// Setup shortcodes
		add_shortcode('agreed-marking', array('agreedMarkingDraw','drawAgreedMarkingPage'));


	}


	function frontendEnqueues ()
	{

      wp_enqueue_script('jquery');

      // Global  Styles
      wp_enqueue_style( 'imperial-agreed-marking-styles', AGREED_MARKING_URL . '/css/styles.css', array(), 0.1 );
      wp_enqueue_script('imperial-agreed-marking-js', AGREED_MARKING_URL. '/js/scripts.js', array(), 0.1 );

      wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );
	}






}
?>
