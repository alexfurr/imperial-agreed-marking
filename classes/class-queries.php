<?php

class agreedMarkingQueries
{
   public static function getAssignmentStudents()
   {

      $returnArray = array();
      $myStudents = get_users( 'role=subscriber' );
      // Array of WP_User objects.
      foreach ( $myStudents as $userInfo ) {

         $username = $userInfo->user_login;
         $userID = $userInfo->ID;
         $firstName = get_user_meta($userID, 'first_name', true);
         $lastName = get_user_meta($userID, 'last_name', true);

         $returnArray[$username] = array(
            "userID" => $userID,
            "firstName" => $firstName,
            "lastName" => $lastName,
         );
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
         $savedValue = $marksInfo['savedValue'];

         $returnArray[$itemID][$assessorUsername] = $savedValue;

      }


      return $returnArray;


   }




   // Replace this with a database driven element if they want to scale it up
   public static function getMarkingGrid()
   {

      $formArray = array();

      // Create new item
      $newItem = array();
      $newItem["description"] = "SLIDES (30%)<br/>Slides - CLARITY - visibilty, font size, image size, amount of material";
      $newItem["type"] = "radio";
      $newItem["options"] = array
      (
         1,2,3,4,5,6,7,8,9,10
      );
      $newItem["thisID"] = "slides-clarity"; // Must be unique
      $formArray[] = $newItem; // Add the item

      // Create new item
      $newItem = array();
      $newItem["description"] = "Slides - RELEVANCE - cover material, appropriate for audience";
      $newItem["type"] = "radio";
      $newItem["options"] = array
      (
         1,2,3,4,5,6,7,8,9,10
      );
      $newItem["thisID"] = "slides-relevance"; // Must be unique
      $formArray[] = $newItem; // Add the item

      // Create new item
      $newItem = array();
      $newItem["description"] = "Comments on SLIDES (tick as many as apply)";
      $newItem["type"] = "checkbox";
      $newItem["options"] = array(
         "Outstanding slides, at conference level",
         "Excellent slides with information clearly laid out",
         "Clear slides with informative diagrams",
         "Too much text",
      );
      $newItem["thisID"] = "slides-comments"; // Must be unique
      $formArray[] = $newItem; // Add the item




      return $formArray;

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
         $assessorInfo = imperialQueries::getUserInfo($thisUsername);

         $firstName = $assessorInfo['first_name'];
         $lastName = $assessorInfo['last_name'];
         $returnArray[$thisUsername] = array(
            "firstName" => $firstName,
            "lastName" => $lastName,
         );
      }

      return $returnArray;
   }

}

?>
