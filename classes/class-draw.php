<?php

class agreedMarkingDraw
{


	static function drawAgreedMarkingPage($atts)
	{

		// if there are no params sent to the shortcode then set some defaults
		// This is optional - you may not need ANY params
		$atts = shortcode_atts(
			array(
			//	'id'		=> '',
				),
			$atts
		);

      $view='';
      if(isset($_GET['view']) )
      {
         $view = $_GET['view'];
      }

      $assignmentID='';
      $assignmentName = '';
      if(isset($_GET['assignmentID']) )
      {
         $assignmentID = $_GET['assignmentID'];

         // Get the title
         $assignmentName = get_the_title($assignmentID);
      }

		//$assignmentID = (int) $atts['id']; // It's expecting a number so check using 'int'
		$html = '';

       // Check for actions

      if(isset($_GET['myAction']) )
      {
         $action = $_GET['myAction'];

         switch ($action)
         {
            case "markStudent":
               echo agreedMarkingActions::markStudent($assignmentID);
            break;
         }

      }


		switch ($view)
		{


         case "markStudent":
            $username = $_GET['username'];
            $studentMeta = imperialQueries::getUserInfo($username);
            $html.='<h2>'.$assignmentName.'</h2>';
            $html.='<h3>'.$studentMeta['first_name'].' '.$studentMeta['last_name'].'</h3>';
            $html.=imperialThemeDraw::drawBackButton("Back to student list", "?view=studentList&assignmentID=".$assignmentID);
            $html.='<hr/>';
            $html.=agreedMarkingDraw::drawMarkingGrid($assignmentID, $username);
         break;

         case "studentReport":
            $username = $_GET['username'];

            $studentMeta = imperialQueries::getUserInfo($username);
            $html.='<h2>'.$assignmentName.'</h2>';
            $html.='<h3>'.$studentMeta['first_name'].' '.$studentMeta['last_name'].'</h3>';
            $html.='<hr/>';
            $html.=agreedMarkingDraw::drawStudentFeedback($assignmentID, $username);
         break;

         case "studentList":
            $html.='<h2>'.$assignmentName.'</h2>';

            $html.=agreedMarkingDraw::drawStudentList($assignmentID);
         break;

			default:

            $html.=agreedMarkingDraw::drawAssignmentList();

			break;

		}

		return $html;
	}


   public static function drawStudentList($assignmentID)
   {

      $thisUsername = $_SESSION['icl_username'];

      $notMarkedByYouTable = '';
      $markedByYouTable = '';
      $markedTable = '';
      $notMarkedTable = '';

      $maxMarkers = 2; // Change this to allow multi markers other than 2. TO DO
      $myStudentsArray = array();



      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $thisUsername)==false)
      {
         return 'You do not have access to this page';
      }

      // Get a list of all the students who have been marked, along with the people that have marked them
      $masterMarkingStatus = agreedMarkingQueries::getAllAssignmentMarks($assignmentID);

      $myMarkingCount = 0;
      foreach ($masterMarkingStatus as $username => $markers)
      {
         if(in_array($thisUsername, $markers) )
         {
            $myMarkingCount++;
            $myStudentsArray[] = $username;
         }
      }



      // Get the users
      $myStudents = agreedMarkingQueries::getAssignmentStudents($assignmentID);

      foreach ($myStudents as $studentUsername => $studentMeta)
      {

         $studentName =  $studentMeta['lastName'].', '.$studentMeta['firstName'];

         $myStatus = '';
         $hasMarkedByYou = false;
         if(in_array($studentUsername, $myStudentsArray) )
         {
            $hasMarkedByYou = true;
            $myStatus= '<span class="successText">Marked by you</span>';
         }

         $thisMarkingCount = 0;
         if(array_key_exists($studentUsername, $masterMarkingStatus) )
         {
            $markersArray = $masterMarkingStatus[$studentUsername];
            $thisMarkingCount = count($markersArray);

         }

         $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $studentUsername);

         // Get the scores
         $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);

         // Create blank vars for all possible marker values
         $i=1;
         while ($i<=$maxMarkers)
         {
            $varName = 'marker'.$i.'Score';
            $$varName = '-';
            $i++;
         }

         $thisMarker = 1;

         if(isset($finalMarks['average']) )
         {
            foreach ($finalMarks as $KEY => $VALUE)
            {
               $varName = 'marker'.$thisMarker.'Score';
               if($KEY<>"average"){

                  $$varName = $VALUE.'%';
                  $$varName.='<br/><span class="smallText">'.$KEY.'</span>';
                  $thisMarker++;
                  $tempCompareArray[] = $VALUE;

               }
               else
               {
                  $averageScore = $VALUE.'%';
               }
            }
         }


         $finalMarkDiscrepancy = agreedMarkingUtils::getMarkingDiscrepancy($finalMarks);

         // Can this user mark the student? Is max markers complete, OR have they marked it
         $allowMarking=false;
         if($thisMarkingCount<$maxMarkers || $hasMarkedByYou==true)
         {
            $allowMarking=true;
         }

         if($hasMarkedByYou==false)
         {
            $marker1Score = '-';
            $marker2Score = '-';
            $averageScore = '-';
         }

         $discrepancyClass = '';
         $discrepancyText = '';
         $rowClass='';
         if($finalMarkDiscrepancy>=FINAL_MARK_DISCREPANCY_THRESHOLD && $hasMarkedByYou==true)
         {
            $discrepancyClass = 'failText';
            $discrepancyText='<br/><span class="smallText">'.$finalMarkDiscrepancy.'% discrepancy<span>';
            $rowClass = 'rowAlert';
         }

         $myStrVar = 'notMarkedByYouTable';
         if($hasMarkedByYou==true)
         {
            $myStrVar = 'markedByYouTable';
         }

         $$myStrVar.= '<tr class="'.$rowClass.'">';
         $$myStrVar.='<td>';
         if($allowMarking==true)
         {
            $$myStrVar.='<a href="?view=markStudent&assignmentID='.$assignmentID.'&username='.$studentUsername.'">';
         }
         $$myStrVar.=$studentName;
         if($allowMarking==true)
         {
            $$myStrVar.='</a>';
         }
         $$myStrVar.='</td>';
         $$myStrVar.='<td>'.$studentUsername.'</td>';
         $$myStrVar.='<td>'.$thisMarkingCount.'</td>';

         if($hasMarkedByYou==true)
         {
            //$$myStrVar.='<td>'.$myStatus.'</td>';
            $$myStrVar.='<td>'.$marker1Score.'</td>';
            $$myStrVar.='<td>'.$marker2Score.'</td>';
            $$myStrVar.='<td><strong class="'.$discrepancyClass.'">'.$averageScore.$discrepancyText.'</strong></td>';
            $$myStrVar.='<td>';

            $$myStrVar.='<a class="imperial-button" href="?view=studentReport&assignmentID='.$assignmentID.'&username='.$studentUsername.'">Preview
               </a>';
            $$myStrVar.='</td>';
         }

         $$myStrVar.='</tr>';
      }



      // Show those students that have been marked by you
      $html = '';

      $markedTableHeader= '<thead><tr>
      <th>Student Name</th>
      <th>Username</th>
      <th>Marked</th>
      <th>Score 1</th>
      <th>Score 2</th>
      <th>Averaged Score</th>
      <th>Preview Report</th>
      </tr></thead>';


      $unMarkedTableHeader= '<thead><tr>
      <th>Student Name</th>
      <th>Username</th>
      <th>Marker Count</th>
      </tr></thead>';
      $markedTable.='<h3>Students Marked by you</h3>';
      $notMarkedTable.='<h3>Students Not Yet Marked by you</h3>';

      $markedTable.= '<table id="assignmentStudentsTable1">'.$markedTableHeader;
      $notMarkedTable.= '<table id="assignmentStudentsTable2">'.$unMarkedTableHeader;


      //$html.='You have marked '.$myMarkingCount.' student(s)<hr/>';

      $markedTable.=$markedByYouTable.'</table>';
      $notMarkedTable.=$notMarkedByYouTable.'</table>';


      $html = $markedTable.'<hr/>'.$notMarkedTable;

      $html.='<script>

         jQuery(document).ready(function(){
            if (jQuery(\'#assignmentStudentsTable1\').length>0)
            {
               jQuery(\'#assignmentStudentsTable1\').dataTable({
                  "bAutoWidth": true,
                  "bJQueryUI": true,
                  "paging":   false,
               });
            }

            if (jQuery(\'#assignmentStudentsTable2\').length>0)
            {
               jQuery(\'#assignmentStudentsTable2\').dataTable({
                  "bAutoWidth": true,
                  "bJQueryUI": true,
                  "paging":   false,
               });
            }

         });


            </script>';


            return $html;
   }

   public static function drawAssignmentList()
   {

      // Now go through all existing post meta and save the  info
		$args = array(
		'post_type' => "agreed-marking",
		);


      $html='';
		$assessments = get_posts( $args );
      $html='<table class="imperial-table-1"><tr>';
      $html.='<th>Assessment Name</th><th>Date</th><th>Students</th><th>Markers</th></tr>';

		foreach ($assessments as $postMeta)
		{
         $assignmentID = $postMeta->ID;
         $assessmentDate = get_post_meta( $assignmentID, 'assessmentDate', true );
         $students = get_post_meta( $assignmentID, 'myStudents', true );
         $markers = get_post_meta( $assignmentID, 'myMarkers', true );
         $studentCount = 0;
         $markerCount = 0;

         if(is_array($students) )
         {
            $studentCount = count($students);
         }

         if(is_array($markers) )
         {
            $markerCount = count($markers);
         }

         $html.='<tr>';
			$assignmentID = $postMeta->ID;
         $html.='<td><a href="?view=studentList&assignmentID='.$assignmentID.'">'. get_the_title($assignmentID).'</a></td>';

         $html.='<td>'. $assessmentDate.' </td>';
         $html.='<td>'. $studentCount.' </td>';
         $html.='<td>'. $markerCount.' </td>';

         $html.='<tr>';

      }
      $html.='</table>';

      return $html;

   }


   public static function drawMarkingGrid($assignmentID, $username)
   {
      $assessorUsername = $_SESSION['icl_username'];

      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $assessorUsername)==false)
      {
         return 'You do not have access to this page';
      }

      $html = '';




      // Get the students marked grades
      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);

      // Get the assessor count for this student
      $assessors = agreedMarkingQueries::getAssessorsForStudent($assignmentID, $username);

      $assessorCount = count($assessors);


      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);


      if(isset($finalMarks[$_SESSION['icl_username']]) )
      {
         $finalMarksForThisAssessor = $finalMarks[$_SESSION['icl_username']];

         $html.='<div class="finalMarkWrap">Your Mark : '.$finalMarksForThisAssessor.'%</div>';
      }

      $markingDisrepancy = agreedMarkingUtils::getMarkingDiscrepancy($finalMarks);

      if($markingDisrepancy>=FINAL_MARK_DISCREPANCY_THRESHOLD)
      {
         $html.='<div class="imperial-feedback imperial-feedback-error">';
         $html.='<strong>Marking Discrepancy of '.$markingDisrepancy.'%</strong><br/>';
         $html.='The mark discrepancy is too high (difference >7 Marks). Please discuss with your co-marker to resolve and resubmit your amended marks.</div>';

      }
      else
      {

         if($assessorCount>1)
         {
            $html.='<div class="imperial-feedback imperial-feedback-success">';
            $html.='Marking difference is <=7 Marks. No further action required</div>';

         }

      }



      $html.='<div class="agreedMarkingAssessorListWrap">';
      if($assessorCount >=1)
      {
         $html.= 'The following assessors have marked this student';
         $html.='<ul class="agreedMarkingAssessorList">';
         foreach ($assessors as $assessorUsername => $assessorInfo)
         {
            $firstName = $assessorInfo['firstName'];
            $lastName = $assessorInfo['lastName'];
            $html.= '<li>'.$firstName.' '.$lastName.' : '.$finalMarks[$assessorUsername].'%</li>';
         }
         $html.='</ul>';

      }
      else
      {
         $html.= 'Nobody has marked this student yet';
      }
      $html.='</div>';



      // Get the current page URL
      $formAction = '?view=markStudent&myAction=markStudent&assignmentID='.$assignmentID.'&username='.$username;
      $formItemsArray = agreedMarkingQueries::getMarkingCriteria($assignmentID);


      $html.='<form action="'.$formAction.'" method="post" class="imperial-form">';
      foreach ($formItemsArray as $criteriaGroup)
      {
         $groupName = $criteriaGroup['name'];
         $groupWeighting='';
         if(isset($criteriaGroup['weighting']) )
         {

            $groupWeighting = $criteriaGroup['weighting'];
         }

         $groupCritiera = $criteriaGroup['criteria'];

         if($groupWeighting==0)
         {
            $groupWeighting = '';
         }
         elseif($groupWeighting)
         {
            $groupWeighting = 'Weighting : '.$groupWeighting.'%';
         }


         $html.='<div class="cirteriaGroupWrap">';
         $html.='<div class="criteriaGroupTitle">';
         $html.='<div>'.$groupName.'</div>';
         $html.='<div>'.$groupWeighting.'</div>';
         $html.='</div>';

         $html.='<div class="criteriaList">';
         foreach ($groupCritiera as $itemMeta)
         {

            $description = $itemMeta['description'];
            $itemType = $itemMeta['type'];
            $options = $itemMeta['options'];
            $thisID = $itemMeta['thisID'];

            $args = array(
               "description" => $description,
               "itemType" => $itemType,
               "options" => $options,
               "savedMarks" => $savedMarks,
               "thisID" => $thisID,
               "assessors" => $assessors,
            );

            $html.=agreedMarkingDraw::drawFormItem($args);
         }
         $html.='</div>';

         $html.='</div>';


      }
      $html.='<input type="submit" value="Submit" class="imperial-button">';

      $html.='</form>';

      return $html;
   }

   public static function drawFormItem($args)
   {


      $description = $args['description'];
      $itemType = $args['itemType'];
      $options = $args['options'];
      $savedMarks = $args['savedMarks'];
      $thisID = $args['thisID'];
      $assessors = $args['assessors'];
      $savedValue = '';
      if(!is_array($savedMarks) ) { $savedMarks = array(); }

      if(!is_array($options)){$options = array(); } // if there are no options create blank array



      $thisAssessessorUsername = $_SESSION['icl_username'];
      $isMarkedByYou = false;
      if(isset($savedMarks[$thisID][$thisAssessessorUsername]) )
      {
         $savedValue = $savedMarks[$thisID][$thisAssessessorUsername];
         $isMarkedByYou = true;
      }

      $html = '<div class="agreedMarkingFormItem item_'.$itemType.'">';
      $html.='<div class="formItemDescription">'.$description.'</div>';

      switch ($itemType)
      {

         case "radio":
            $optionNumber = 1;

            $html.='<div class="formItemRadioWrap">';

            foreach ($options as $optionValue)
            {

               $thisRadioID = $thisID.'_'.$optionNumber;

               $html.='<label for="'.$thisRadioID.'">';
               $html.='<span>'.$optionValue.'</span>';
               $html.='<span><input required type="radio" name="'.$thisID.'" id="'.$thisRadioID.'" value="'.$optionNumber.'"';
               if($optionNumber==$savedValue){$html.=' checked ';}
               $html.='/></span></label>';
               $optionNumber++;
            }
            $html.='</div>';



            if($isMarkedByYou==true)
            {

               //If there is a discrepenecy then Highlight this
               if(count($savedMarks)>=1 )
               {
                //  $html.='<h3>Marks from other assessors</h3>';
                  $isAgreed = agreedMarkingUtils::checkDiscrepancy($thisID, $savedMarks);
                  if($isAgreed==true)
                  {
                     if(isset($savedMarks[$thisID] ) )
                     {
                        $theseSavedValues = $savedMarks[$thisID];

                        foreach ($theseSavedValues as $tempAssessorUsername => $tempMarks)
                        {

                           if($thisAssessessorUsername <> $tempAssessorUsername)
                           {
                              $thisAssessorNameInfo = $assessors[$tempAssessorUsername];
                              $thisAssessorName = $thisAssessorNameInfo['firstName'].' '.$thisAssessorNameInfo['lastName'];

                              $html.= '<div class="imperial-feedback imperial-feedback-success">Marks given by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
                           }
                        }
                     }

                  }
                  else
                  {
                     // Show the the values that have been saved
                     $theseSavedValues = $savedMarks[$thisID];

                     foreach ($theseSavedValues as $tempAssessorUsername => $tempMarks)
                     {

                        if($thisAssessessorUsername <> $tempAssessorUsername)
                        {
                           $thisAssessorNameInfo = $assessors[$tempAssessorUsername];
                           $thisAssessorName = $thisAssessorNameInfo['firstName'].' '.$thisAssessorNameInfo['lastName'];


                           $marksDifference = ($tempMarks - $savedValue);

                           if($marksDifference<0)
                           {
                              $marksDifference = ($marksDifference * -1);
                           }


                           $feedbackClass = "imperial-feedback-alert";
                           if($marksDifference>=3)
                           {
                              $feedbackClass = "imperial-feedback-error";
                           }



                           $html.= '<div class="imperial-feedback '.$feedbackClass.'">Marks given by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
                        }
                     }

                  }
               }
            }


         break;


         case "checkbox":
            printArray($options);
            foreach ($options as $optionID => $optionValue)
            {
               $thisCheckboxID = $thisID.'_'.$optionID;

               $isChecked = false;
               if(isset($savedMarks[$thisCheckboxID][$thisAssessessorUsername]) )
               {
                  $isChecked = true;
               }
               $html.='<label for="'.$thisCheckboxID.'">';
               $html.='<input type="checkbox" name="checkbox_'.$thisID.'[]" id="'.$thisCheckboxID.'" value="'.$optionID.'"';
               if($isChecked){$html.=' checked ';}
               $html.='/>'.$optionValue.'</label>';

            }

         break;


         case "textarea":
            $html.='<textarea class="agreedMarkingTextarea" name="'.$thisID.'" id="'.$thisID.'">'.$savedValue.'</textarea>';
         break;

      }

      $html.= '</div>';
      return $html;

   }

   public static function drawStudentFeedback($assignmentID, $username)
   {

      $thisUsername = $_SESSION['icl_username'];

      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $thisUsername)==false)
      {
         if($thisUsername<>$username)
         {
            return 'You do not have access to this page';
         }

      }

      $html = '';

      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);


      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);


      $finalMark = $finalMarks['average'];

      $html.= '<div class="agreedMarksFeedbackMark">Your mark : <span class="theMark">'.$finalMark.'%</span></div>';

      $formItemsArray = agreedMarkingQueries::getCriteria();

      foreach ($formItemsArray as $criteriaGroup)
      {
         $groupName = $criteriaGroup['name'];
         $groupWeighting='';
         if(isset($criteriaGroup['weighting']) )
         {

            $groupWeighting = $criteriaGroup['weighting'];
         }

         $groupCritiera = $criteriaGroup['criteria'];

         if($groupWeighting==0)
         {
            $groupWeighting = '';
         }
         elseif($groupWeighting)
         {
            $groupWeighting = 'Weighting : '.$groupWeighting.'%';
         }


         $html.='<div class="cirteriaGroupWrap">';
         $html.='<div class="criteriaGroupTitle">';
         $html.='<div>'.$groupName.'</div>';
         $html.='<div>'.$groupWeighting.'</div>';
         $html.='</div>';


         $html.='<div class="criteriaList">';
         foreach ($groupCritiera as $itemMeta)
         {

            $description = $itemMeta['description'];
            $itemType = $itemMeta['type'];
            $options = $itemMeta['options'];
            $thisID = $itemMeta['thisID'];

            $html.= '<strong>'.$description.'</strong><br/>';

            $thisFeedback = '';
            if(isset($savedMarks[$thisID]) )
            {
               $thisFeedback = $savedMarks[$thisID];
            }

            $args = array(
               "itemType" => $itemType,
               "thisFeedback" => $thisFeedback,
               "options" => $options,
               "itemID" => $thisID,
               "savedMarks" => $savedMarks,
            );
            $html.=agreedMarkingDraw::drawFormItemFeedback($args);

            $html.='<hr/>';




         }
         $html.='</div>';
         $html.='</div>';

      }

      return $html;
   }


   public static function drawFormItemFeedback($args)
   {
      $itemType = $args['itemType'];
      $itemID = $args['itemID'];
      $thisFeedback = $args['thisFeedback'];
      $options = $args['options'];
      $savedMarks = $args['savedMarks'];


      $html = '';

      switch ($itemType)
      {
         case "radio":
            $tempTotal = 0;
            $assessorCount = 0;

            $optionCount = count($options);

            if($thisFeedback)
            {
               foreach ($thisFeedback as $tempValue)
               {
                  $tempTotal = $tempTotal + $tempValue;
                  $assessorCount++;
               }
               $thisAverage = $tempTotal/$assessorCount;

            }

            $thisAverage = round($thisAverage, 2);

            $html.= $thisAverage.' / '.$optionCount;

         break;

         case "checkbox":
            // go through the

            $tempCheckboxIDarray = array();
            foreach ($savedMarks as $KEY => $VALUE)
            {
               if (strpos($KEY, $itemID) !== false)
               {
                  foreach ($VALUE as $tempID)
                  {
                     $tempCheckboxIDarray[] = $tempID;
                  }
               }
            }

            $tempCheckboxIDarray = array_unique($tempCheckboxIDarray);


            $i = 1;

            $html.='<ul>';
            foreach ($tempCheckboxIDarray as $tempOptionID)
            {
               if(is_numeric($tempOptionID) )
               {
                  $html.='<li>'.$options[($tempOptionID-1)].'</li>';
               }
            }
            $html.='</ul>';

         break;


         default:
            if(is_array($thisFeedback) )
            {
               foreach ($thisFeedback as $tempFeedback)
               {
                  $html.=imperialNetworkUtils::convertTextFromDB($tempFeedback).'<hr/>';
               }
            }
         break;


      }

      return $html;

   }


}


?>
