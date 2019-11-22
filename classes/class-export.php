<?php

if(isset($_GET['myAction']) )
{
   $action = $_GET['myAction'];

   switch ($action)
   {
      case "downloadMarks":
         // Handle CSV Export
         add_action( 'admin_init', array('agreedMarkingDataExport', 'downloadMarks') );
      break;

   }

}

class agreedMarkingDataExport
{

	public function downloadMarks()
	{
		// Check for current user privileges

		// Nonce Check
		if(!current_user_can('delete_pages') )
      {
			die( 'Security check error' );
		}

      $assignmentID = $_GET['id'];
      $assignmentName = get_the_title($assignmentID);

      $fileName = $assignmentName.'_marks.csv';

      $CSV_array = agreedMarkingAdminDraw::drawUserTable($assignmentID, "student", true);

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		ob_end_clean();		 // Remove unwanted blank spaces / line breaks
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");

		$fh = @fopen( 'php://output', 'w' );

		foreach ($CSV_array as $fields) {
			fputcsv($fh, $fields);
		}

		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		die();
	}

}

?>
