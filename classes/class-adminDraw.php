<?php

class agreedMarkingAdminDraw
{

   public static function drawUserTable($assignmentID, $userType, $createCSV = false)
   {
      $html='';
      $CSVarray = array();
      $csvHeaderRow = array();
      $userCount = 0;

      switch($userType)
      {

         case "marker":
            $userArray = get_post_meta( $assignmentID, 'myMarkers', true );
            $tableID="markerTable";
            $headerArray = array("Name", "Username", "");


         break;

         case "student";
            $userArray = get_post_meta( $assignmentID, 'myStudents', true );
            $tableID="studentTable";
            $headerArray = array("Name", "Username", "Marked Count", "Markers", "Score", "");

            $masterMarkingStatus = agreedMarkingQueries::getAllAssignmentMarks($assignmentID);

         break;
      }

      if(is_array($userArray) )
      {
         $userCount = count($userArray);
      }
      if($userCount==0)
      {
         return '<br/><br/>No '.$userType.'s found';
      }


      $html.='<table class="imperial-table" id="'.$tableID.'">';
      $html.='<thead><tr>';
      foreach ($headerArray as $colTitle)
      {
         $html.='<th>'.$colTitle.'</th>';
      }

      $csvHeaderRow = array(
        "Name", "Username", "Marked Count", "Marker 1", "Marker 1 Score", "Marker 2", "Marker 2 Score", "Average",
      );


      $CSVarray[] = $csvHeaderRow;
      $html.='</tr></thead>';

      foreach ($userArray as $thisUsername)
      {

         $csvRow = array();
         $userMeta = imperialQueries::getUserInfo($thisUsername);

         $usernameCheck = $userMeta['username'];
         $firstName = $userMeta['first_name'];
         $lastName = $userMeta['last_name'];
         $fullName = $lastName.', '.$firstName;

         $errorClass = '';
         if($usernameCheck=="")
         {
            $fullName = 'User not found!';
            $errorClass = 'rowAlert';
         }

         $csvRow[] = $fullName;
         $csvRow[] = $thisUsername;


         if($userType=="student")
         {
            $thisMarkingCount = 0;
            if(array_key_exists($thisUsername, $masterMarkingStatus) )
            {
               $markersArray = $masterMarkingStatus[$thisUsername];
               $thisMarkingCount = count($markersArray);

            }

            $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $thisUsername);

            // Get the scores
            $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);

            $finalMark ='-';
            if(isset($finalMarks['average']) )
            {
               $finalMark = $finalMarks['average'].'%';
            }

         }

         $html.='<tr class="'.$errorClass.'">';
         $html.='<td>'.$fullName.'</td>';
         $html.='<td>'.$thisUsername.'</td>';

         if($userType=="student")
         {
            $html.='<td>'.$thisMarkingCount.'</td>';
            $csvRow[] = $thisMarkingCount;

            $html.='<td class="smallText">';
            foreach ($finalMarks as $KEY => $VALUE)
            {
               if($KEY<>"average")
               {
                  $html.=$KEY.' : '.$VALUE.'% <a href="?page=agreed-marking-users&id='.$assignmentID.'&markerUsername='.$KEY.'&student='.$thisUsername.'&view=deleteMarkCheck">(Remove Mark)</a><br/>';
                  $csvRow[] = $KEY;
                  $csvRow[] = $VALUE.'%';



               }

            }

           // Fill in any missing marker cols
           while ($thisMarkingCount<2)
           {
             $csvRow[] = "-";
             $csvRow[] = "-";
             $thisMarkingCount++;
           }



            $html.='</td>';
            $html.='<td>'.$finalMark.'</td>';
            $csvRow[] = $finalMark;
         }

         $html.='<td><a class="button-secondary" href="?page=agreed-marking-users&id='.$assignmentID.'&myAction=removeUser&username='.$thisUsername.'&userType='.$userType.'">Remove</a></td>';
         $html.='</tr>';

         $CSVarray[] = $csvRow;


      }
      $html.= '</tbody>';

      $html.='</table>';
      $html.="
      <script>
      jQuery(document).ready( function () {
         jQuery('#".$tableID."').DataTable({


         'pageLength': 50
         }



         );
      } );

      </script>
      ";


      if($createCSV==false)
      {
         return $html;
      }
      else
      {

         return $CSVarray;
      }


   }




}


?>
