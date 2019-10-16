<?php
$agreedMarking_db = new agreedMarking_db();
class agreedMarking_db {

   // Was 0.6

   var $DBversion = 0.1;


	//~~~~~
	function __construct ()
	{

		global $wpdb;
		// Define the table as a global
		global $agreedMarkingUserMarks;
		$agreedMarkingUserMarks = $wpdb->prefix . 'agreedMarkingUserMarks';

		add_action( 'plugins_loaded', array($this, 'myplugin_update_db_check' ) );

	}

	// Function to check latest version and then update DB if needed
	function myplugin_update_db_check()
	{


		// Get the Current DB and check against this verion
		$currentDBversion = get_option('agreedMarkingDB_version');
		$thisDBversion = $this->DBversion;

		if($thisDBversion>$currentDBversion)
		{

			$this->createTables();
			update_option('agreedMarkingDB_version', $thisDBversion);
		}
		//$this->createTables(); // Testing

	}

	//~~~~~
	function createTables()
	{



		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// Get plugin version and set option to current version
		global $wpdb;
		global $agreedMarkingUserMarks;


		$charSet = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $agreedMarkingUserMarks (
		ID int NOT NULL AUTO_INCREMENT,
		assignmentID mediumint(9) NOT NULL,
		dateSubmitted datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      username varchar(255),
      assessorUsername varchar(255),
		itemID varchar(255),
		savedValue mediumint(9),
		PRIMARY KEY (ID),
      KEY studentMarks (username, assignmentID),
      KEY assessorMarks (assessorUsername, assignmentID)
		) ".$charSet;

      print_r( dbDelta( $sql ) );


   }

}
?>
