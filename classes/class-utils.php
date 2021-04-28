<?php

class agreedMarkingUtils
{
   public static function checkDiscrepancy($itemID, $savedMarks)
   {

      // Get the saved Values
      if(!isset($savedMarks[$itemID]) )
      {
         return true; // must be true as there are no marks to compare
      }

      $savedValues = $savedMarks[$itemID];
      $savedValuesCount = count($savedValues);


      // Only one saved mark so con't be a discrepncy
      if($savedValues<=1)
      {
         return true;
      }

      $allValuesAreTheSame = (count(array_unique($savedValues)) === 1);
      return $allValuesAreTheSame;


   }

   public static function getFinalMarks($assignmentID, $savedMarks)
   {

       // Check if its a step criteria

       $useStepScale = get_post_meta( $assignmentID, 'useStepScale', true );

       // Create array for checking both assessors have marked the same amount of criteria
       $criteria_mark_check = array();
       // Return this as debug in case the marker has somehow missed some marks
       $total_criteria_that_can_be_marked = 0;

      // Get the weightings
      $criteriaGroups = agreedMarkingQueries::getMarkingCriteria($assignmentID);

      $totalAssessorTracker = array();

      foreach ($criteriaGroups as $criteriaGroupMeta)
      {

         $tempAssessorMarksArray = array();


         $name = $criteriaGroupMeta['name'];
         $weighting = $criteriaGroupMeta['weighting'];
         $criteria = $criteriaGroupMeta['criteria'];



         $totalGroupAvailableMarks = 0;

         // Get the total criteria that can be marked

         foreach ($criteria as $criteriaInfo)
         {
            $criteraID = $criteriaInfo['thisID'];

            $type = $criteriaInfo['type'];

            if($type<>"radio" && $type<>"stepScale" ){continue;}

            $total_criteria_that_can_be_marked++;


            $criteriaOptions = $criteriaInfo['options'];
            $optionCount = 0;

            if(is_array($criteriaOptions) )
            {
               $optionCount = count($criteriaOptions);
            }


            if($type=="stepScale")
            {
                $totalGroupAvailableMarks = $totalGroupAvailableMarks+100; // This is always out of 100
            }
            else
            {
                $totalGroupAvailableMarks = $totalGroupAvailableMarks+$optionCount;
            }


            $criteriaID = $criteriaInfo['thisID'];

            // Get the saved values for this criteria
            $thisSavedMarks = array();

            if(isset($savedMarks[$criteriaID] ) )
            {
               $thisSavedMarks = $savedMarks[$criteriaID];
            }

            foreach($thisSavedMarks as $assessorUsername => $thisMark)
            {

               $tempAssessorMarksArray[$assessorUsername][] = $thisMark;
            }

           // echo '<hr/>';
         }

         // Now got through the array getting the total for each assessor
         $criteriaGroupTotalMarkArray = array();

        foreach ($tempAssessorMarksArray as $assessorUsername => $tempCriteriaAssessorMarks)
        {
            $temp_marked_count = count($tempCriteriaAssessorMarks);
            $criteria_mark_check[$assessorUsername][] = $temp_marked_count;
            $tempTotal = 0;
            foreach ($tempCriteriaAssessorMarks as $thisMark)
            {
                $tempTotal = $tempTotal + $thisMark;
            }

            $criteriaGroupTotalMarkArray[$assessorUsername] = $tempTotal;
        }

         // Now appl ythe weighting
         foreach ($criteriaGroupTotalMarkArray as $assessorUsername => $totalMark)
         {
            $tempGroupMarks = ($totalMark / $totalGroupAvailableMarks)*($weighting/100);

            $totalAssessorTracker[$assessorUsername][] = $tempGroupMarks;
         }

      }

      $finalArray = array();




      foreach($totalAssessorTracker as $assessorUsername => $groupMarks)
      {
         $thisTotalMark = 0;
         foreach ($groupMarks as $thisMark)
         {
             //echo $thisMark.'<br/>';
             $thisTotalMark = $thisTotalMark+$thisMark;

         }

         $finalArray[$assessorUsername] = round(($thisTotalMark * 100), 2);
      }

      $totalAverage = 0;

      $assessorCount = count($finalArray);
      foreach ($finalArray as $thisMark)
      {
         $totalAverage = $totalAverage + $thisMark;
      }

      if($assessorCount>=1)
      {
         $finalArray['average'] = round(($totalAverage/$assessorCount), 2);
      }

      // Finally add up the total amount of criteria marked by each student and check they match
      $marked_count_array = array();
      $old_count = '';
      $error = '0';
      foreach ($criteria_mark_check as $assessor => $criteria_marked)
      {

          $total_marked = 0;
          foreach ($criteria_marked as $temp_total_marked)
          {
              $total_marked = $total_marked+$temp_total_marked;
          }
          $marked_count_array[$assessor] = $total_marked;
          if($old_count)
          {
              if($total_marked<>$old_count)
              {
                  $error++;
              }
          }

          $old_count = $total_marked;
      }


      $finalArray['debug']['marked_count'] = $marked_count_array;
      $finalArray['debug']['error_count'] = $error;
      $finalArray['debug']['total_available_criteria'] = $total_criteria_that_can_be_marked;




      return $finalArray;

   }


   // Pass ana rray of values. Takes highest and lowers and checks that they are not different by more than $max_discrepancy%
   public static function getMarkingDiscrepancy($marksArray)
   {
      // Order the array low to high
      // Remove the average
      unset($marksArray['average']);
      unset($marksArray['debug']);
      asort($marksArray);
      $itemsCount = count($marksArray);

      if($itemsCount<=1)
      {
         return 0;
      }
      else
      {
         $array_keys = array_keys($marksArray);

         // get the first item in the array
         $lowestValue = $marksArray[array_shift($array_keys)];

         // get the last item in the array
         $highestValue =  $marksArray[array_pop($array_keys)];

         $discrepancyValue = round($highestValue-$lowestValue), 2);
         return $discrepancyValue;

      }


   }


   // Checks a username against an assessment to see if they can mark it
   public static function checkMarkerAccess($assignmentID, $username)
   {
      $assignmentMarkersArray  = get_post_meta( $assignmentID, 'myMarkers', true );

      if(current_user_can('edit_pages') )
      {
         return true;
      }


      if(!is_array($assignmentMarkersArray) )
      {
         return false;
      }


      if(in_array($username, $assignmentMarkersArray))
      {
         return true;
      }


      return false;

   }

   public static function getCheckboxGroups($assignmentID)
   {
      $checkboxReturnArray = array();
      $criteriaGroups = agreedMarkingQueries::getCriteriaGroups($assignmentID);

      // Get the criteria
      foreach ($criteriaGroups as $groupMeta)
      {
         $groupID= $groupMeta['groupID'];
         $criteria = agreedMarkingQueries::getCriteriaInGroup($groupID);

         foreach ($criteria as $criteriaMeta)
         {
            $criteriaID = $criteriaMeta['criteriaID'];
            $criteriaName = $criteriaMeta['criteriaName'];
            $criteriaType = $criteriaMeta['criteriaType'];

            if($criteriaType=="checkbox")
            {

               $checkboxReturnArray[$criteriaID] = $criteriaName;
            }
         }
      }

      return $checkboxReturnArray;
   }


} // End of class


?>
