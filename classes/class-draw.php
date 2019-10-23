<?php

class agreedMarkingDraw
{


	static function drawAgreedMarkingPage($atts)
	{

		// if there are no params sent to the shortcode then set some defaults
		// This is optional - you may not need ANY params
		$atts = shortcode_atts(
			array(
				'id'		=> '',
				),
			$atts
		);

		$assignmentID = (int) $atts['id']; // It's expecting a number so check using 'int'
		$html = '';


       // Ge the assignment title
       $assignmentTitle = get_the_title($assignmentID);

       echo '<h2>'.$assignmentTitle.'</h2>';

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
		$view='';
		if(isset($_GET['view']) )
		{
			$view = $_GET['view'];
		}


		switch ($view)
		{


         case "markStudent":
            $username = $_GET['username'];

            $studentMeta = imperialQueries::getUserInfo($username);
            $html.='<h3>'.$studentMeta['first_name'].' '.$studentMeta['last_name'].'</h3>';
            $html.=imperialThemeDraw::drawBackButton("Back to student list", "?");
            $html.='<hr/>';
            $html.=agreedMarkingDraw::drawMarkingGrid($assignmentID, $username);
         break;

         case "studentReport":
            $username = $_GET['username'];

            $studentMeta = imperialQueries::getUserInfo($username);
            $html.='<h3>'.$studentMeta['first_name'].' '.$studentMeta['last_name'].'</h3>';
            $html.='<hr/>';
            $html.=agreedMarkingDraw::drawStudentFeedback($assignmentID, $username);
         break;

			default:

            $html.=agreedMarkingDraw::drawStudentList($assignmentID);

			break;

		}





		return $html;
	}


   public static function drawStudentList($assignmentID)
   {

      $thisUsername = $_SESSION['icl_username'];
      $myStudentsArray = array();
      if(!current_user_can('edit_pages') )
      {
         return 'You do not have permission to view this page';
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

      $html = '';

      $html.='You have marked '.$myMarkingCount.' student(s)<hr/>';

      $html.= '<table id="assignmentStudentsTable">';
      $html.= '<thead><tr>
      <th>Student Name</th>
      <th>Username</th>
      <th>Status</th>
      <th>Marked</th>
      <th>Score 1</th>
      <th>Score 2</th>
      <th>Averaged Score</th>
      <th>Preview Report</th>
      </tr></thead>';

      // Get the users
      $myStudents = agreedMarkingQueries::getAssignmentStudents();

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

         $thisMarkingCount = '<span class="greyText">0</span>';
         if(array_key_exists($studentUsername, $masterMarkingStatus) )
         {
            $markersArray = $masterMarkingStatus[$studentUsername];
            $thisMarkingCount = count($markersArray);
         }

         $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $studentUsername);

         // Get the scores
         $finalMarks = agreedMarkingUtils::getFinalMarks($savedMarks);


         $thisMarker = 1;

         if(isset($finalMarks['average']) )
         {
            foreach ($finalMarks as $KEY => $VALUE)
            {
               $varName = 'marker'.$thisMarker.'Score';
               if($KEY<>"average"){
                  $$varName = $VALUE.'%';
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

         if($hasMarkedByYou==false)
         {
            $marker1Score = '-';
            $marker2Score = '-';
            $averageScore = '-';
         }

         $discrepancyClass = '';
         $discrepancyText = '';
         if($finalMarkDiscrepancy>=FINAL_MARK_DISCREPANCY_THRESHOLD)
         {
            $discrepancyClass = 'failText';
            $discrepancyText='<br/><span class="smallText">Discrepancy<span>';
         }

         $html.= '<tr>';
         $html.='<td><a href="?view=markStudent&assignmentID='.$assignmentID.'&username='.$studentUsername.'">'.$studentName.'</a></td>';
         $html.='<td>'.$studentUsername.'</td>';
         $html.='<td>'.$myStatus.'</td>';
         $html.='<td>'.$thisMarkingCount.'</td>';
         $html.='<td>'.$marker1Score.'</td>';
         $html.='<td>'.$marker2Score.'</td>';
         $html.='<td><strong class="'.$discrepancyClass.'">'.$averageScore.$discrepancyText.'</strong></td>';
         $html.='<td>';
         if($thisMarkingCount>=1)
         {
            $html.='<a class="imperial-button" href="?view=studentReport&assignmentID='.$assignmentID.'&username='.$studentUsername.'">Report
            </a>';
         }
         $html.='</td>';

         $html.='</tr>';
      }

      $html.= '</table>';

      $html.='<script>

         jQuery(document).ready(function(){
            if (jQuery(\'#assignmentStudentsTable\').length>0)
            {
               jQuery(\'#assignmentStudentsTable\').dataTable({
                  "bAutoWidth": true,
                  "bJQueryUI": true,
                  "paging":   false,
               });
            }

         });
            </script>';


            return $html;
   }


   public static function drawMarkingGrid($assignmentID, $username)
   {

      $html = '';

      // Get the students marked grades
      $assessorUsername = $_SESSION['icl_username'];
      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);

      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($savedMarks);


      if(isset($finalMarks[$_SESSION['icl_username']]) )
      {
         $finalMarksForThisAssessor = $finalMarks[$_SESSION['icl_username']];

         $html.='<div class="finalMarkWrap">Your Mark : '.$finalMarksForThisAssessor.'%</div>';
      }

      $markingDisrepancy = agreedMarkingUtils::getMarkingDiscrepancy($finalMarks);

      if($markingDisrepancy>=FINAL_MARK_DISCREPANCY_THRESHOLD)
      {
         $html.='<h3 class="failText">Marking Discrepancy of '.$markingDisrepancy.'%</h3>';
      }


      // Get the assessor count for this student
      $assessors = agreedMarkingQueries::getAssessorsForStudent($assignmentID, $username);

      $assessorCount = count($assessors);

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
      $formItemsArray = agreedMarkingQueries::getCriteria();


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
      if(!is_array($savedMarks) )
      {
         $savedMarks = array();
      }


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
                  $html.='<h3>Marks from other assessors</h3>';
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
            $optionNumber = 1;
            foreach ($options as $optionValue)
            {
               $thisCheckboxID = $thisID.'_'.$optionNumber;

               $isChecked = false;
               if(isset($savedMarks[$thisCheckboxID][$thisAssessessorUsername]) )
               {
                  $isChecked = true;
               }
               $html.='<label for="'.$thisCheckboxID.'">';
               $html.='<input type="checkbox" name="'.$thisCheckboxID.'" id="'.$thisCheckboxID.'" value="'.$optionNumber.'"';
               if($isChecked){$html.=' checked ';}
               $html.='/>'.$optionValue.'</label>';

               $optionNumber++;

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


      $html = '';

      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);


      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($savedMarks);


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
