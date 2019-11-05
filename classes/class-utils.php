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

   public static function getFinalMarks($savedMarks)
   {


      // Get the weightings

      $criteriaGroups = agreedMarkingQueries::getCriteria();

      $totalAssessorTracker = array();

      foreach ($criteriaGroups as $critertaGroupMeta)
      {

         $tempAssessorMarksArray = array();

         $name = $critertaGroupMeta['name'];
         $weighting = $critertaGroupMeta['weighting'];
         $criteria = $critertaGroupMeta['criteria'];

         $totalGroupAvailableMarks = 0;

         foreach ($criteria as $criteriaInfo)
         {
            $criteraID = $criteriaInfo['thisID'];
            if($criteriaInfo['type']<>"radio"){continue;}

            $criteriaOptions = $criteriaInfo['options'];
            $optionCount = count($criteriaOptions);
            $totalGroupAvailableMarks = $totalGroupAvailableMarks+$optionCount;
            $criteriaID = $criteriaInfo['thisID'];


            // Get the saved values for this criteria
            $thisSavedMarks = array();

            if(isset($savedMarks[$criteriaID] ) )
            {
               $thisSavedMarks = $savedMarks[$criteriaID];
            }

            foreach($thisSavedMarks as $assessorUsername => $thisMark)
            {
               //echo $assessorUsername.' gave '.$thisMark.' / '.$optionCount.'<br/>';

               $tempAssessorMarksArray[$assessorUsername][] = $thisMark;
            }

           // echo '<hr/>';
         }

         // Now got through the array getting the total for each assessor
         $criteriaGroupTotalMarkArray = array();
         foreach ($tempAssessorMarksArray as $assessorUsername => $tempCriteriaAssessorMarks)
         {
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

         $finalArray[$assessorUsername] = ($thisTotalMark * 100);
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

      return $finalArray;

   }


   // Pass ana rray of values. Takes highest and lowers and checks that they are not different by more than 7%
   public static function getMarkingDiscrepancy($marksArray)
   {
      // Order the array low to high
      // Remove the average
      unset($marksArray['average']);
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

         $discrepancyValue = $highestValue-$lowestValue;
         return $discrepancyValue;

      }


   }


}


?>
