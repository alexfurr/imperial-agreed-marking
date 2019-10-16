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
               agreedMarkingActions::markStudent($assignmentID);
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
      if(!current_user_can('edit_pages') )
      {
         return 'You do not have permission to view this page';
      }
      $html = '';

      $html.= '<table id="assignmentStudentsTable">';
      $html.= '<thead><tr><th>Student Name</th><th>Username</th><th>Status</th></tr></thead>';

      // Get the users
      $myStudents = agreedMarkingQueries::getAssignmentStudents();

      foreach ($myStudents as $username => $studentMeta)
      {

         $studentName =  $studentMeta['lastName'].', '.$studentMeta['firstName'];

         $html.= '<tr><td><a href="?view=markStudent&assignmentID='.$assignmentID.'&username='.$username.'">'.$studentName.'</a></td><td>'.$username.'</td><td></td></tr>';
      }

      $html.= '</table>';

      $html.='<script>

      jQuery(document).ready(function(){
         if (jQuery(\'#assignmentStudentsTable\').length>0)
         {
            jQuery(\'#assignmentStudentsTable\').dataTable({
               "bAutoWidth": true,
               "bJQueryUI": true,
               "sPaginationType": "full_numbers",
               "iDisplayLength": 50, // How many numbers by default per page
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
      foreach ($formItemsArray as $itemMeta)
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

      $thisAssessessorUsername = $_SESSION['icl_username'];

      $html = '<div class="agreedMarkingFormItem item_'.$itemType.'">';
      $html.='<div class="formItemDescription">'.$description.'</div>';

      switch ($itemType)
      {

         case "radio":
            $optionNumber = 1;
            if(isset($savedMarks[$thisID][$thisAssessessorUsername]) )
            {
               $thisValue = $savedMarks[$thisID][$thisAssessessorUsername];
            }
            foreach ($options as $optionValue)
            {

               $html.='<label for="'.$thisRadioID.'">';
               $html.='<span>'.$optionValue.'</span>';
               $html.='<span><input type="radio" name="'.$thisID.'" id="'.$thisRadioID.'" value="'.$optionNumber.'"';
               if($optionNumber==$thisValue){$html.=' checked ';}
               $html.='/></span></label>';

               $optionNumber++;

            }


            //If there is a discrepenecy then Highlight this
            $isAgreed = agreedMarkingUtils::checkDiscrepancy($thisID, $savedMarks);
            if($isAgreed==true)
            {
               $html.='AGREED';
            }
            else
            {
               $html.='NOT AGREED<br/>';

               // Show the the values that have been saved
               $theseSavedValues = $savedMarks[$thisID];

               foreach ($theseSavedValues as $tempAssessorUsername => $tempMarks)
               {

                  if($thisAssessessorUsername <> $tempAssessorUsername)
                  {
                     $thisAssessorNameInfo = $assessors[$tempAssessorUsername];
                     $thisAssessorName = $thisAssessorNameInfo['firstName'].' '.$thisAssessorNameInfo['lastName'];


                     $html.= $thisAssessorName.' gave the make : '.$tempMarks;
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

      }

      $html.= '</div>';
      return $html;

   }
}


?>
