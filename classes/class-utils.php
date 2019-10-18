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

      echo '<pre>';
      //print_r($savedMarks);
      echo '</pre>';

   }


}


?>
