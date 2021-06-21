<?php

class agreedMarkingAdminDraw
{

   public static function drawAdminNotice($message, $type="success")
   {
      $html = '<div class="notice notice-'.$type.' is-dismissible">';
      $html.='<p><strong>'.$message.'</strong></p>';
      $html.='<button type="button" class="notice-dismiss">';
      $html.='<span class="screen-reader-text">Dismiss this notice.</span>';
      $html.='</button>';
      $html.='</div>';

      return $html;

   }



   public static function drawUserTable($assignmentID, $userType, $createCSV = false)
   {
      $siteURL = get_site_url();

      $html='';
      $error_str = '';
      $CSVarray = array();
      $csvHeaderRow = array();
      $userCount = 0;

      $archived = get_post_meta( $assignmentID, 'archived', true );


      switch($userType)
      {

         case "marker":
            $userArray = get_post_meta( $assignmentID, 'myMarkers', true );
            $tableID="markerTable";
            $headerArray = array("Name", "Username", "");


         break;

         case "student";
            $userArray = get_post_meta( $assignmentID, 'myStudents', true );

            $cappedStudentArray = get_post_meta( $assignmentID, 'cappedStudentArray', true );
            if(!is_array($cappedStudentArray) )
            {
               $cappedStudentArray = array();
            }

            $tableID="studentTable";
            $headerArray = array("Name", "Username", "Marked Count", "Markers", "Score",  "Preview", "Capped", "");

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

         $thisUsername = strtolower($thisUsername);
         $csvRow = array();
         $userMeta = \icl_network\user_queries::get_user_info( $thisUsername );

         $usernameCheck = $userMeta->username;
         $firstName = $userMeta->first_name;
         $lastName = $userMeta->last_name;
         $fullName = $lastName.', '.$firstName;
         $userType = $userMeta->user_type;

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

            // Are there errors?
            if(isset($finalMarks['debug']['error_count']) )
            {

                $error_count = $finalMarks['debug']['error_count'];

                if($error_count>0)
                {
                    $this_mark_count = $finalMarks['debug']['marked_count'];

                    $error_str.= 'Student : '.$thisUsername.' :<br/>';
                    foreach ($this_mark_count as $KEY => $VALUE)
                    {
                        $error_str.=$KEY.' marked '.$VALUE.' criteria.<br/>';

                    }
                    $error_str.='<hr/>';

                }
            }



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
               if($KEY<>"average" && $KEY<>"debug")
               {
                  $html.=$KEY.' : '.$VALUE.'% ';


                  if($archived<>true)
                  {
                     $html.='<a href="?page=agreed-marking-users&id='.$assignmentID.'&markerUsername='.$KEY.'&student='.$thisUsername.'&view=deleteMarkCheck">(Remove Mark)</a><br/>';
                  }
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

            $html.='<td width="80">';
            if($archived<>true)
            {
               if(in_array($thisUsername, $cappedStudentArray) )
               {
                  $html.='<a class="button-secondary" href="?page=agreed-marking-users&id='.$assignmentID.'&view=uncapMarkCheck&username='.$thisUsername.'&userType='.$userType.'">Uncap Marks</a>';
               }
               else
               {
                  $html.='<a class="button-secondary" href="?page=agreed-marking-users&id='.$assignmentID.'&view=capMarkCheck&username='.$thisUsername.'&userType='.$userType.'">Cap Marks</a>';
               }
            }
            $html.='</td>';

            // Add the preview link
            $previewLink = $siteURL.'/?view=studentReport&assignmentID='.$assignmentID.'&username='.$thisUsername;
            $html.='<td><a href="'.$previewLink.'" class="button-primary" target="blank">Preview</a></td>';

         }



         $html.='<td>';
         if($archived<>true)
         {
            $html.='<a class="button-secondary" href="?page=agreed-marking-users&id='.$assignmentID.'&myAction=removeUser&username='.$thisUsername.'&userType='.$userType.'">Remove</a>';
         }
         $html.='</td>';
         $html.='</tr>';

         $CSVarray[] = $csvRow;


      }
      $html.= '</tbody>';

      $html.='</table>';
      $html.="
      <script>
      jQuery(document).ready( function () {

        
         jQuery('#".$tableID."').hide();
         jQuery('#".$tableID."').DataTable({
            'pageLength': 50
            }
         );
         jQuery('#".$tableID."').show();


      } );

      </script>
      ";

      if($error_str)
      {
        $error_str = '<h3>There appear to be some missing marks for '.$error_count.' student(s)</h3><div class="imperial-feedback imperial-feedback-error">'.$error_str.'</div>';

      }


      if($createCSV==false)
      {
         return $error_str.$html;
      }
      else
      {

         return $CSVarray;
      }


   }




}


?>
