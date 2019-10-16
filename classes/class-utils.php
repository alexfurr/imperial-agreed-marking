<?php

class agreedMarkingUtils
{
   public static function checkDiscrepancy($itemID, $savedMarks)
   {

      // Get the saved Values
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


}


?>
