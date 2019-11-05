<?php

class agreedMarkingQueries
{
   public static function getAssignmentStudents($assignmentID)
   {

      $returnArray = array();
      $myStudents = get_post_meta( $assignmentID, 'myStudents', true );

      // Array of WP_User objects.
      foreach ( $myStudents as $thisUsername ) {

         $userMeta = imperialQueries::getUserInfo($thisUsername);

         $usernameCheck = $userMeta['username'];
         $firstName = $userMeta['first_name'];
         $lastName = $userMeta['last_name'];

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




   // Replace this with a database driven element if they want to scale it up
   public static function getCriteria()
   {

     $formArray = array();

     $criteriaGroup = array();  // Create new cirteria group
     $criteriaGroup['name'] = "SLIDES";
     $criteriaGroup['weighting'] = 30;


      // Create new item
     $newItem = array();
     $newItem["description"] = "Slides - CLARITY - visibilty, font size, image size, amount of material</p>";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "slides-clarity"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item

     // Create new item
     $newItem = array();
     $newItem["description"] = "<p>Slides - RELEVANCE - cover material, appropriate for audience</p>";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "slides-relevance"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item


     // Create new item
     $newItem = array();
     $newItem["description"] = "Slides - CONTENT ACCURACY";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "slides-accuracy"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item



     // Create new item
     $newItem = array();
     $newItem["description"] = "Comments on SLIDES (tick as many as apply)";
     $newItem["type"] = "checkbox";
     $newItem["options"] = array(
        "Outstanding slides, at conference level",
        "Excellent slides with information clearly laid out",
        "Clear slides with informative diagrams",
        "Too much text",
        "Font too small",
        "Could have used more diagrams",
        "Images/diagrams too small",
        "Images/diagrams not relevant to content",
        "Covered too much material on slides",
        "Images used not visible/at low resolution",
        "Accurate content, presented at very high level",
        "Content not presented in logical order",
        "Some errors in the content",
        "Significant errors in the content",
        "Some irrelevant material covered",


     );
     $newItem["thisID"] = "slides-comments"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item

     $formArray[] = $criteriaGroup; // Add to the main form pbject item


     // Create new criteria group
     $criteriaGroup = array();  // Create new cirteria group
     $criteriaGroup['name'] = "VERBAL PRESENTATION";
     $criteriaGroup['weighting'] = 30;



      // Create new item
      $newItem = array();
      $newItem["description"] = "<p>DELIVERY - audibility, pace, fluency, length</p>";
      $newItem["type"] = "radio";
      $newItem["options"] = array
      (
         1,2,3,4,5,6,7,8,9,10
      );
      $newItem["thisID"] = "verbal-delivery"; // Must be unique
      $criteriaGroup['criteria'][] = $newItem; // Add the item

      // Create new item
     $newItem = array();
     $newItem["description"] = "ENGAGEMENT WITH AUDIENCE - energy, eye contact, not reading";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "verbal-engagement"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item

       // Create new item
       $newItem = array();
       $newItem["description"] = "Appropriate use of visual material during talk";
       $newItem["type"] = "radio";
       $newItem["options"] = array
       (
          1,2,3,4,5,6,7,8,9,10
       );
       $newItem["thisID"] = "verbal-visual-material"; // Must be unique
       $criteriaGroup['criteria'][] = $newItem; // Add the item

       // Create new item
     $newItem = array();
     $newItem["description"] = "Comments on VERBAL PRESENTATION<br/>(tick as many as apply)";
     $newItem["type"] = "checkbox";
     $newItem["options"] = array(
        "Excellent delivery, good pace",
        "Authoritative delivery",
        "Very confident delivery",
        "Very good audience engagement",
        "Not enough eye contact with audience",
        "Did not engage audience",
        "Spoke too quickly",
        "Spoke too quietly",
        "Monotonous delivery",
        "Did not point to relevant parts on slide",
        "Did not make use of slides",
        "Too much reading from notes/slides",
        "Did not keep to time",
        "Seemed nervous",
        "Did not seem very confident",
        "Was not very fluent in the delivery",


     );
     $newItem["thisID"] = "presentation-comments"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item

     $formArray[] = $criteriaGroup; // Add to the main form pbject item


     // Create new criteria group
     $criteriaGroup = array();  // Create new cirteria group
     $criteriaGroup['name'] = "Quality of presentation overall";
     $criteriaGroup['weighting'] = 10;



     // Create new item
     $newItem = array();
     $newItem["description"] = "<p>Mark on appropriate background and conclusions, logical presentation of information</p>";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "quality-presentation"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item


     $formArray[] = $criteriaGroup; // Add to the main form pbject item


     // Create new criteria group
     $criteriaGroup = array();  // Create new cirteria group
     $criteriaGroup['name'] = "DISCUSSION";
     $criteriaGroup['weighting'] = 30;



     // Create new item
     $newItem = array();
     $newItem["description"] = "<p>Single mark out of 10 based on handling of questions and clarity of thought in the discussion.
     NB will count 30% of total marks.</p>";
     $newItem["type"] = "radio";
     $newItem["options"] = array
     (
        1,2,3,4,5,6,7,8,9,10
     );
     $newItem["thisID"] = "discussion"; // Must be unique
     $criteriaGroup['criteria'][] = $newItem; // Add the item

      // Create new item
      $newItem = array();
      $newItem["description"] = "Comments on DISCUSSION<br/>(tick as many as apply)";
      $newItem["type"] = "checkbox";
      $newItem["thisID"] = "discussion-comments"; // Must be unique

      $newItem["options"] = array(
         "Excellent answers to questions",
         "Handled questions well",
         "Very confident in answering questions",
         "Demonstrated a lot of outside reading",
         "Answered questions well but needed to think some answers through",
         "Needed help with answering of questions",
         "Some errors of understanding of concepts",
         "Significant errors of understanding of concepts",
         "Hesitated too much when answering questions",
         "Was not very confident when answering questions",
         "Confused answers",


      );

      $criteriaGroup['criteria'][] = $newItem; // Add the item
      $formArray[] = $criteriaGroup; // Add to the main form pbject item


      $criteriaGroup = array();  // Create new cirteria group
      $criteriaGroup['name'] = "General Comments";
      $criteriaGroup['weighting'] = 0;


      // Create new item
      $newItem = array();
      $newItem["description"] = "Please add any general feedback below";
      $newItem["type"] = "textarea";
      $newItem["options"] = array();
      $newItem["thisID"] = "general-comments"; // Must be unique
      $criteriaGroup['criteria'][] = $newItem; // Add the item



      $formArray[] = $criteriaGroup; // Add to the main form pbject item






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

}

?>
