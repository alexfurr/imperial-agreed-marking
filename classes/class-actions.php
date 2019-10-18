<?php
class agreedMarkingActions
{
   public static function markStudent($assignmentID)
	{

      $feedback = '';

      // Get the current user ID
      if(!current_user_can('edit_pages') )
      {
         return 'You do not have permission to do that';
      }
		global $wpdb;
      global $agreedMarkingUserMarks;

      // Get the student username

      $assessorUsername = $_SESSION['icl_username'];
      //$assessorUsername = 'aandi';

      $studentUsername = $_GET['username'];

      //echo '$assessorUsername = '.$assessorUsername.'<br/>';
      //echo '$studentUsername = '.$studentUsername.'<br/>';
      //echo '$assignmentID = '.$assignmentID.'<br/>';

      $wpdb->query( $wpdb->prepare( "DELETE FROM $agreedMarkingUserMarks WHERE
      (assignmentID = %d AND username = %s AND assessorUsername = %s)",
      $assignmentID, $studentUsername, $assessorUsername  ) );


      $now = date('Y-m-d H:i:s');

      // Add the stuff

      foreach ($_POST as $KEY => $VALUE)
      {
         $myFields="INSERT into $agreedMarkingUserMarks (assignmentID, username, assessorUsername, itemID, savedValue, dateSubmitted) ";
         $myFields.="VALUES (%d, %s, %s, %s, %s, %s)";


         $RunQry = $wpdb->query( $wpdb->prepare($myFields,
            $assignmentID,
            $studentUsername,
            $assessorUsername,
            $KEY,
            $VALUE,
            $now
         ));


      }




      /*
      $myFields="INSERT into $thisDB (user_id, $fieldName, thisDate, date_added, OS) ";
      $myFields.="VALUES (%d, %s, %s, %s, %s)";

      $RunQry = $wpdb->query( $wpdb->prepare($myFields,
         $currentUserID,
         $thisData,
         $thisDate,
         $now,
         $osType
      ));
      */


		$feedback.='<div class="imperial-feedback imperial-feedback-success">';
		$feedback.= 'Marks submitted';
		$feedback.= '</div>';

    //  $feedback.=$feedbackReportStr;

		return $feedback;
	}

}



?>
