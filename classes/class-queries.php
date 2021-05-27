<?php

class agreedMarkingQueries
{


   public static function getCriteriaGroups($assignmentID)
   {

      global $wpdb;
      global $agreedMarkingCriteriaGroups;

      $query = $wpdb->prepare( "SELECT * FROM $agreedMarkingCriteriaGroups WHERE assignmentID= %d order by groupOrder ASC",
      $assignmentID);
      $groupsArray = $wpdb->get_results($query, ARRAY_A);
      return $groupsArray;

   }


   public static function getCriteriaInGroup($groupID)
   {

      global $wpdb;
      global $agreedMarkingCriteria;

      $query = $wpdb->prepare( "SELECT * FROM $agreedMarkingCriteria WHERE groupID= %d order by criteriaOrder ASC",
      $groupID);

      $criteriaArray = $wpdb->get_results($query, ARRAY_A);
      return $criteriaArray;

   }

   public static function getCriteriaOptions($criteriaID)
   {

      global $wpdb;
      global $agreedMarkingCriteriaOptions;

      $query = $wpdb->prepare( "SELECT * FROM $agreedMarkingCriteriaOptions WHERE criteriaID= %d order by optionOrder ASC",
      $criteriaID);

      $optionsArray = $wpdb->get_results($query, ARRAY_A);
      return $optionsArray;

   }

   //---
   public static function getMarkingCriteriaForAdmin ( $assignmentID )
   {
      $assignment_data = array();
      if ( $assignmentID ) {
           $assignment_data = agreedMarkingQueries::getCriteriaGroups( $assignmentID );
      }
      if ( is_array( $assignment_data ) ) {
           foreach ( $assignment_data as $i => $group ) {
               $criteria = agreedMarkingQueries::getCriteriaInGroup( $group['groupID'] );
               if ( is_array( $criteria ) ) {
                   foreach ( $criteria as $j => $c ) {
                       $options = agreedMarkingQueries::getCriteriaOptions( $c['criteriaID'] );
                       $criteria[ $j ]['options'] = empty( $options ) ? array() : $options;
                   }
               }
               $assignment_data[ $i ]['criteria'] = empty( $criteria ) ? array() : $criteria;
           }
      }


      return is_array( $assignment_data ) ? $assignment_data : array();
   }

   // Replace this with a database driven element if they want to scale it up
   public static function getMarkingCriteria($assignmentID)
   {


      $criteriaReturnArray = array();
      $criteriaGroups = agreedMarkingQueries::getCriteriaGroups($assignmentID);

      // Get the criteria
      foreach ($criteriaGroups as $groupMeta)
      {
         $groupID= $groupMeta['groupID'];
         $name= $groupMeta['groupName'];
         $weighting= $groupMeta['weighting'];
         $tempArray = array();

         $tempArray['name'] =$name;
         $tempArray['weighting'] =$weighting;

         $criteria = agreedMarkingQueries::getCriteriaInGroup($groupID);

         $tempCriteriaArray = array();
         $currentCriteria = 0;
         foreach ($criteria as $criteriaMeta)
         {

            $tempCriteriaListArray = array();
            $criteriaID = $criteriaMeta['criteriaID'];
            $criteriaName = $criteriaMeta['criteriaName'];
            $criteriaType = $criteriaMeta['criteriaType'];

            $tempCriteriaArray[$currentCriteria]['description'] = $criteriaName;
            $tempCriteriaArray[$currentCriteria]['type'] = $criteriaType;
            $tempCriteriaArray[$currentCriteria]['thisID'] = $criteriaID;

            // Get the criteria options
            $criteriaOptions = agreedMarkingQueries::getCriteriaOptions($criteriaID);

            // Create blank array for the options
            $tempCriteriaArray[$currentCriteria]['options'] = array();

            if($criteriaType=="stepScale")
            {
                $options = agreedMarkingQueries::getStepScale();
                foreach ($options as $optionValue)
                {
                   $tempCriteriaArray[$currentCriteria]['options'][$optionValue] = $optionValue;

                }
            }
            else
            {
                foreach ($criteriaOptions as $optionMeta)
                {
                   $optionID = $optionMeta['optionID'];
                   $optionValue = $optionMeta['optionValue'];
                   $tempCriteriaArray[$currentCriteria]['options'][$optionID] = $optionValue;

                }

            }

            $currentCriteria++;


         }
         $tempArray['criteria'] = $tempCriteriaArray;

         $criteriaReturnArray[] = $tempArray;

      }

      return $criteriaReturnArray;


   }


   public static function getAssignmentStudents($assignmentID)
   {

      $returnArray = array();
      $myStudents = get_post_meta( $assignmentID, 'myStudents', true );

      // Array of WP_User objects.
      foreach ( $myStudents as $thisUsername ) {

         $userMeta = \icl_network\user_queries::get_user_info( $thisUsername );
         
         $usernameCheck = $userMeta->username;
         $firstName = $userMeta->first_name;
         $lastName = $userMeta->last_name;

         if($usernameCheck<>"")
         {
            $returnArray[$thisUsername] = array(
               "firstName" => $firstName,
               "lastName" => $lastName,
            );
         }
      }

      return $returnArray;
   }

   public static function getUserMarks($assignmentID, $username, $assessorUsername="")
   {

      global $wpdb;
      global $agreedMarkingUserMarks;


      $query = $wpdb->prepare( "SELECT * FROM $agreedMarkingUserMarks WHERE assignmentID= %d AND username = %s",
      $assignmentID, $username);
      $marksArray = $wpdb->get_results($query, ARRAY_A);

      $returnArray= array();

      foreach ($marksArray as $marksInfo)
      {
         $assessorUsername = $marksInfo['assessorUsername'];
         $itemID = $marksInfo['itemID'];
         $savedValue = stripslashes($marksInfo['savedValue']);

         $returnArray[$itemID][$assessorUsername] = $savedValue;

      }


      return $returnArray;


   }






   public static function getAssessorsForStudent($assignmentID, $username)
   {
      $userMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);

      // Create a unique array of all assessors who have contributed to this grading
      $tempArray = array();
      foreach ($userMarks as $markMetaArray)
      {
         foreach ($markMetaArray as $assessorUsername => $thisMark)
         {
            $tempArray[] = $assessorUsername;
         }
      }

      $usernamesArray = array_unique($tempArray);

      // Now add the assessor info as well
      $returnArray = array();
      foreach ($usernamesArray as $thisUsername)
      {
         $assessorInfo = \icl_network\user_queries::get_user_info( $thisUsername );
         
         $firstName = $assessorInfo->first_name;
         $lastName = $assessorInfo->last_name;
         $returnArray[$thisUsername] = array(
            "firstName" => $firstName,
            "lastName" => $lastName,
         );
      }

      return $returnArray;
   }


   public static function getAllAssignmentMarks($assignmentID)
   {
      global $wpdb;
      global $agreedMarkingUserMarks;

      $query = $wpdb->prepare( "SELECT * FROM $agreedMarkingUserMarks WHERE assignmentID= %d", $assignmentID);
      $marksArray = $wpdb->get_results($query, ARRAY_A);

      $returnArray= array();

      foreach ($marksArray as $marksMeta)
      {
         $username = $marksMeta['username'];
         $assessorUsername = $marksMeta['assessorUsername'];
         $returnArray[$username][] = $assessorUsername;

      }

      // Get just the assessors
      foreach ($returnArray as $username => $assessors)
      {
         $assessors = array_unique($assessors);
         $returnArray[$username] = $assessors;
      }


      return $returnArray;

   }

   public static function getStepScale()
   {
       $array = array(0,5,10,15,20,25,30,35,38,42,45,48,52,55,58,62,65,68,72,76,80,85,90,95,100);
       return $array;

   }

}

?>
