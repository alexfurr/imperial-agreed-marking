<?php
class agreedMarkingActions
{
   public static function markStudent($assignmentID)
	{

      $feedback = '';

      $assessorUsername = $_SESSION['icl_username'];
      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $assessorUsername)==false)
      {
         return 'You do not have permission to do that';
      }
		global $wpdb;
      global $agreedMarkingUserMarks;

      // Get the student username

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

         if (strpos($KEY, 'checkbox') !== false) {
             $KEY = substr($KEY, strrpos($KEY, '_') + 1); // Get the criteriaID

             $tempCheckArray = array();
             foreach ($VALUE as $thisOptionID)
             {
               $thisUID = $thisCriteriaID.'_'.$thisOptionID;

               $tempCheckArray[] = $thisOptionID;
               /*

               $myFields="INSERT into $agreedMarkingUserMarks (assignmentID, username, assessorUsername, itemID, savedValue, dateSubmitted) ";
               $myFields.="VALUES (%d, %s, %s, %s, %s, %s)";


               $RunQry = $wpdb->query( $wpdb->prepare($myFields,
               $assignmentID,
               $studentUsername,
               $assessorUsername,
               $thisUID,
               1,
               $now
               ));
               */


            }


            // seralise the array
            $VALUE = serialize($tempCheckArray);

         }

         if (strpos($KEY, 'textarea') !== false) {
            $KEY = substr($KEY, strrpos($KEY, '_') + 1);
            $VALUE = sanitize_textarea_field( $VALUE );
         }

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



      $feedback.= imperialNetworkDraw::imperialFeedback("Marks Submitted");


    //  $feedback.=$feedbackReportStr;

		return $feedback;
	}

}



?>
