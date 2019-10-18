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
      $html.= '<thead><tr><th>Student Name</th><th>Username</th><th>Status</th><th>Marked</th><th>Score 1</th><th>Score 2</th><th>Averaged Score</th></tr></thead>';

      // Get the users
      $myStudents = agreedMarkingQueries::getAssignmentStudents();

      foreach ($myStudents as $studentUsername => $studentMeta)
      {

         $studentName =  $studentMeta['lastName'].', '.$studentMeta['firstName'];

         $myStatus = '';
         if(in_array($studentUsername, $myStudentsArray) )
         {
            $myStatus= '<span class="successText">Marked by you</span>';
         }

         $thisMarkingCount = '<span class="greyText">0</span>';
         if(array_key_exists($studentUsername, $masterMarkingStatus) )
         {
            $markersArray = $masterMarkingStatus[$studentUsername];
            $thisMarkingCount = count($markersArray);
         }

         $html.= '<tr>';
         $html.='<td><a href="?view=markStudent&assignmentID='.$assignmentID.'&username='.$studentUsername.'">'.$studentName.'</a></td>';
         $html.='<td>'.$studentUsername.'</td>';
         $html.='<td>'.$myStatus.'</td>';
         $html.='<td>'.$thisMarkingCount.'</td>';
         $html.='<td>-</td>';
         $html.='<td>-</td>';
         $html.='<td>-</td>';

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

      // Get the assessor count for this student
      $assessors = agreedMarkingQueries::getAssessorsForStudent($assignmentID, $username);


      $assessorCount = count($assessors);

      $html.='<div class="agreedMarkingAssessorListWrap">';
      if($assessorCount >=1)
      {
         $html.= 'The following assessors have marked this student';
         $html.='<ul class="agreedMarkingAssessorList">';
         foreach ($assessors as $assessorInfo)
         {
            $firstName = $assessorInfo['firstName'];
            $lastName = $assessorInfo['lastName'];
            $html.= '<li>'.$firstName.' '.$lastName.'</li>';
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
      $formItemsArray = agreedMarkingQueries::getMarkingGrid();


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
      if(isset($savedMarks[$thisID][$thisAssessessorUsername]) )
      {
         $savedValue = $savedMarks[$thisID][$thisAssessessorUsername];
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
               $html.='<span><input type="radio" name="'.$thisID.'" id="'.$thisRadioID.'" value="'.$optionNumber.'"';
               if($optionNumber==$savedValue){$html.=' checked ';}
               $html.='/></span></label>';
               $optionNumber++;
            }
            $html.='</div>';


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

                           $html.= '<div class="imperial-feedback imperial-feedback-success">Marks give by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
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

                        $html.= '<div class="imperial-feedback imperial-feedback-alert">Marks given by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
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
}


?>
