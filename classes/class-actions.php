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






   //---
   public static function save_groups_data ( $assignmentID )
   {
      if ( ! isset( $_POST['groups'] ) || ! is_array( $_POST['groups'] ) ) {
           return;
      }
      foreach( $_POST['groups'] as $i => $group ) {
           $group_id = self::save_group( $assignmentID, $group );
      }
   }


   //---
   public static function save_criteria_data ()
   {
      if ( ! isset( $_POST['criteria'] ) || ! is_array( $_POST['criteria'] ) ) {
           return;
      }
      foreach( $_POST['criteria'] as $i => $criteria ) {
           if ( empty( $criteria['criteriaType'] ) ) {
               $criteria['criteriaType'] = 'radio';
           }
           $criteria_id = self::save_criteria( $criteria );

           if ( $criteria_id ) {
               // Loop thru and save options
               $options = isset( $_POST['criteria'][ $i ]['options'] ) ? $_POST['criteria'][ $i ]['options'] : array();
               $sort_order = 1;
               foreach( $options as $j => $op ) {
                   $op['criteriaID']   = $criteria_id;
                   $op['optionOrder']  = $sort_order;
                   $option_id = self::save_option( $op );
                   $sort_order += 1;
               }
           }
      }
   }


   //---
   public static function delete_group_data ( $delete_refs )
   {
      if ( isset( $delete_refs['groupID'] ) ) {
           foreach ( $delete_refs['groupID'] as $group_id ) {
               self::delete_groups( 'groupID', $group_id );
               self::delete_criteria( 'groupID', $group_id );
               //echo '<br>---<br>delete group by - groupID: ' . $group_id;
               //echo '<br>delete criteria by - groupID: ' . $group_id;
           }
      }
      if ( isset( $delete_refs['criteriaID'] ) ) {
           foreach ( $delete_refs['criteriaID'] as $criteria_ids_string ) {
               $criteria_ids = explode( ',', $criteria_ids_string );
               if ( is_array( $criteria_ids ) ) {
                   foreach ( $criteria_ids as $criteria_id ) {
                       self::delete_options( 'criteriaID', $criteria_id );
                       //echo '<br>delete options by - criteriaID: ' . $criteria_id;
                   }
               }
           }
      }
   }


   //---
   public static function delete_criteria_data ( $delete_refs )
   {
      if ( isset( $delete_refs['criteriaID'] ) ) {
           foreach ( $delete_refs['criteriaID'] as $criteria_id ) {
               self::delete_criteria( 'criteriaID', $criteria_id );
               self::delete_options( 'criteriaID', $criteria_id );
               //echo '<br>---<br>delete criteria by - criteriaID: ' . $criteria_id;
               //echo '<br>delete options by - criteriaID: ' . $criteria_id;
           }
      }
      if ( isset( $delete_refs['optionID'] ) ) {
           foreach ( $delete_refs['optionID'] as $option_id ) {
               self::delete_options( 'optionID', $option_id );
               //echo '<br>delete option by - optionID: ' . $option_id;
           }
      }
   }


   //---
   public static function save_group ( $assignmentID, $group )
   {
      global $wpdb;
      global $agreedMarkingCriteriaGroups;
      $group_id = ! empty( $group['groupID'] ) ? intval( $group['groupID'] ) : 0;

      if ( $group_id ) {
           $success = $wpdb->update(
               $agreedMarkingCriteriaGroups,
               array(
                   'assignmentID'  => $assignmentID,
                   'groupName'	    => stripslashes( $group['groupName'] ),
                   'weighting'		=> intval( $group['weighting'] ),
                   'groupOrder'	=> intval( $group['groupOrder'] )
               ),
               array( 'groupID' => $group_id ),
               array( '%d', '%s', '%d', '%d' ),
               array( '%d' )
           );
      } else {
           $success = $wpdb->insert(
               $agreedMarkingCriteriaGroups,
               array(
                   'assignmentID'  => $assignmentID,
                   'groupName'	    => stripslashes( $group['groupName'] ),
                   'weighting'		=> intval( $group['weighting'] ),
                   'groupOrder'	=> intval( $group['groupOrder'] )
               ),
               array( '%d', '%s', '%d', '%d' )
           );
           if ( $success !== false ) {
               $group_id = $wpdb->insert_id;
           }
      }

      return $group_id;
   }


   //---
   public static function save_criteria ( $criteria )
   {
      global $wpdb;
      global $agreedMarkingCriteria;
      $criteria_id = ! empty( $criteria['criteriaID'] ) ? intval( $criteria['criteriaID'] ) : 0;

      if ( $criteria_id ) {
           $success = $wpdb->update(
               $agreedMarkingCriteria,
               array(
                   'groupID'       => intval( $criteria['groupID'] ),
                   'criteriaName'	=> stripslashes( $criteria['criteriaName'] ),
                   'criteriaType'	=> $criteria['criteriaType'],
                   'criteriaOrder'	=> intval( $criteria['criteriaOrder'] )
               ),
               array( 'criteriaID' => $criteria_id ),
               array( '%d', '%s', '%s', '%d' ),
               array( '%d' )
           );
      } else {
           $success = $wpdb->insert(
               $agreedMarkingCriteria,
               array(
                   'groupID'       => intval( $criteria['groupID'] ),
                   'criteriaName'	=> stripslashes( $criteria['criteriaName'] ),
                   'criteriaType'	=> $criteria['criteriaType'],
                   'criteriaOrder'	=> intval( $criteria['criteriaOrder'] )
               ),
               array( '%d', '%s', '%s', '%d' )
           );
           if ( $success !== false ) {
               $criteria_id = $wpdb->insert_id;
           }
      }

      return $criteria_id;
   }


   //---
   public static function save_option ( $option )
   {
      global $wpdb;
      global $agreedMarkingCriteriaOptions;
      $option_id = ! empty( $option['optionID'] ) ? intval( $option['optionID'] ) : 0;

      if ( $option_id ) {
           $success = $wpdb->update(
               $agreedMarkingCriteriaOptions,
               array(
                   'criteriaID'    => intval( $option['criteriaID'] ),
                   'optionValue'	=> stripslashes( $option['optionValue'] ),
                   'optionOrder'	=> intval( $option['optionOrder'] )
               ),
               array( 'optionID' => $option_id ),
               array( '%d', '%s', '%d' ),
               array( '%d' )
           );
      } else {
           $success = $wpdb->insert(
               $agreedMarkingCriteriaOptions,
               array(
                   'criteriaID'    => intval( $option['criteriaID'] ),
                   'optionValue'	=> stripslashes( $option['optionValue'] ),
                   'optionOrder'	=> intval( $option['optionOrder'] )
               ),
               array( '%d', '%s', '%d' )
           );
           if ( $success !== false ) {
               $option_id = $wpdb->insert_id;
           }
      }

      return $option_id;
   }


   //---
   public static function delete_groups ( $fieldkey, $id )
   {
      global $wpdb;
      global $agreedMarkingCriteriaGroups;

      $id = intval( $id );
      $row_count = 0;
      if ( $id ) {
           $row_count = $wpdb->delete(
               $agreedMarkingCriteriaGroups,
               array( $fieldkey => $id ),
               array( '%d' )
           );
      }
      return $row_count;
   }


   //---
   public static function delete_criteria ( $fieldkey, $id )
   {
      global $wpdb;
      global $agreedMarkingCriteria;

      $id = intval( $id );
      $row_count = 0;
      if ( $id ) {
           $row_count = $wpdb->delete(
               $agreedMarkingCriteria,
               array( $fieldkey => $id ),
               array( '%d' )
           );
      }
      return $row_count;
   }


   //---
   public static function delete_options ( $fieldkey, $id )
   {
      global $wpdb;
      global $agreedMarkingCriteriaOptions;

      $id = intval( $id );
      $row_count = 0;
      if ( $id ) {
           $row_count = $wpdb->delete(
               $agreedMarkingCriteriaOptions,
               array( $fieldkey => $id ),
               array( '%d' )
           );
      }
      return $row_count;
   }





}



?>
