<?php
$agreedMarking_db = new agreedMarking_db();
class agreedMarking_db {

   // Was 0.6

   var $DBversion = 0.4;


	//~~~~~
	function __construct ()
	{

		global $wpdb;
		// Define the table as a global
		global $agreedMarkingUserMarks;
      global $agreedMarkingCriteriaGroups;
      global $agreedMarkingCriteria;
      global $agreedMarkingCriteriaOptions;

      $agreedMarkingUserMarks = $wpdb->prefix . 'agreedMarkingUserMarks';
      $agreedMarkingCriteriaGroups = $wpdb->prefix . 'agreedMarkingCriteriaGroups';
      $agreedMarkingCriteria = $wpdb->prefix . 'agreedMarkingCriteria';
      $agreedMarkingCriteriaOptions = $wpdb->prefix . 'agreedMarkingCriteriaOptions';

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
      global $agreedMarkingCriteriaGroups;
      global $agreedMarkingCriteria;
      global $agreedMarkingCriteriaOptions;

		$charSet = $wpdb->get_charset_collate();


		$sql = "CREATE TABLE $agreedMarkingUserMarks (
		ID int NOT NULL AUTO_INCREMENT,
		assignmentID mediumint(9) NOT NULL,
		dateSubmitted datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      username varchar(255),
      assessorUsername varchar(255),
		itemID int,
		savedValue longtext,
		PRIMARY KEY (ID),
      KEY studentMarks (username, assignmentID),
      KEY assessorMarks (assessorUsername, assignmentID)
		) ".$charSet;
      dbDelta( $sql );

      // Criteria Groups
      $sql = "CREATE TABLE $agreedMarkingCriteriaGroups (
      groupID int NOT NULL AUTO_INCREMENT,
      assignmentID mediumint(9) NOT NULL,
      groupName varchar(255),
      weighting int,
      groupOrder int,
      PRIMARY KEY (groupID),
      KEY assignmentID (assignmentID)
      ) ".$charSet;

      dbDelta( $sql );


      // Criteria
      $sql = "CREATE TABLE $agreedMarkingCriteria (
      criteriaID int NOT NULL AUTO_INCREMENT,
      groupID mediumint(9) NOT NULL,
      criteriaName varchar(255),
      criteriaType varchar(50),
      criteriaOrder int,
      PRIMARY KEY (criteriaID),
      KEY groupID (groupID)
      ) ".$charSet;
      dbDelta( $sql );


      // Criteria options
      $sql = "CREATE TABLE $agreedMarkingCriteriaOptions (
      optionID int NOT NULL AUTO_INCREMENT,
      criteriaID int NOT NULL,
      optionValue text,
      optionOrder int,
      PRIMARY KEY (optionID),
      KEY criteriaID (criteriaID)
      ) ".$charSet;
      dbDelta( $sql );


   }

}
?>
