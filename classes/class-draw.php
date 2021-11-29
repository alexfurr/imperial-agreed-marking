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
               //echo agreedMarkingActions::markStudent($assignmentID);
               agreedMarkingActions::markStudent($assignmentID);
            break;
         }

      }


		switch ($view)
		{


         case "markStudent":
            if(isset($_GET['username']))
            {
               $username = $_GET['username'];
               $html.=agreedMarkingDraw::drawMarkingGrid($assignmentID, $username);
            }
            else
            {
               $html.='No username found';
            }
         break;

         case "studentReport":

            if(isset($_GET['username']) )
            {
               $username = $_GET['username'];
            }
            else
            {

                $current_user = wp_get_current_user();
                $username = $current_user->user_login;
            }

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

      $current_user = wp_get_current_user();
      $thisUsername = $current_user->user_login;

      $notMarkedByYouTable = '';
      $markedByYouTable = '';
      $markedTable = '';
      $notMarkedTable = '';

      $maxMarkers = 2; // Change this to allow multi markers other than 2. TO DO
      $myStudentsArray = array();


      $max_discrepancy = get_post_meta( $assignmentID, 'max_discrepancy', true );


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

               if($KEY<>"average" && $KEY<>"debug")
               {
                    $varName = 'marker'.$thisMarker.'Score';

                    $$varName = $VALUE.'%';
                    $$varName.='<br/><span class="smallText">'.$KEY.'</span>';
                    $thisMarker++;
                    $tempCompareArray[] = $VALUE;
               }
               elseif($KEY=='average')
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
         if($finalMarkDiscrepancy>$max_discrepancy && $hasMarkedByYou==true)
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

            $$myStrVar.='<a class="button" href="?view=studentReport&assignmentID='.$assignmentID.'&username='.$studentUsername.'">Preview</a>';
            $$myStrVar.='</td>';
         }
         else
         {
            $$myStrVar.='<td>';
            if(current_user_can('delete_pages') && $thisMarkingCount>=1)
            {
               $$myStrVar.='<a class="button" href="?view=studentReport&assignmentID='.$assignmentID.'&username='.$studentUsername.'">Preview</a>';
            }
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
      <th></th>
      </tr></thead>';
      $markedTable.='<h3>Students Marked by you</h3>';
      $notMarkedTable.='<h3>Students Not Yet Marked by you</h3>';

      $markedTable.= '<table id="assignmentStudentsTable1" class="table">'.$markedTableHeader;
      $notMarkedTable.= '<table id="assignmentStudentsTable2" class="table">'.$unMarkedTableHeader;


      //$html.='You have marked '.$myMarkingCount.' student(s)<hr/>';

      $markedTable.=$markedByYouTable.'</table>';
      $notMarkedTable.=$notMarkedByYouTable.'</table>';

      $html = '';
	  $html.=\icl_network\draw::content_box_open();
	  $html.=$markedTable;
	  $html.=\icl_network\draw::content_box_close();

	  $html.=\icl_network\draw::content_box_open();
	  $html.=$notMarkedTable;
	  $html.=\icl_network\draw::content_box_close();

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



         $current_user = wp_get_current_user();
         $thisUsername = $current_user->user_login;


      // Now go through all existing post meta and save the  info
		$args = array(
		'post_type' => "agreed-marking",
        "numberposts"   => -1,

		);


      $html='';
		$assessments = get_posts( $args );
      $html='<table class="table is-fullwidth"><tr>';
  //    $html.='<th>Assessment Name</th><th>Assignment Date</th><th>Marks Release Date</th><th>Students</th><th>Markers</th></tr>';
      $html.='<th>Assessment Name</th><th>Assignment Date</th><th>Marks Release Date</th></tr>';

      $assignmentToShowCount = 0;
		foreach ($assessments as $postMeta)
		{
         $isStudent=false;
         $assignmentID = $postMeta->ID;
         $assessmentDate = get_post_meta( $assignmentID, 'assessmentDate', true );
         $releaseDate = get_post_meta( $assignmentID, 'releaseDate', true );
         $students = get_post_meta( $assignmentID, 'myStudents', true );
         $markers = get_post_meta( $assignmentID, 'myMarkers', true );
         $isArchived = get_post_meta( $assignmentID, 'archived', true );



         $showAssignment = false;

         // Are they editor?
         if(current_user_can('delete_posts') )
         {
            //$showAssignment = true;
         }

         // Are they a marker?
         if(is_array($markers) )
         {



            if(in_array($thisUsername, $markers) )
            {
               $showAssignment = true;
            }
         }

         // Are they a student?
         if(is_array($students) )
         {

            if(in_array($thisUsername, $students) )
            {
               $isStudent=true;

               // Check if the marking reslease date if valid
               $now = date('Y-m-d');

               if($releaseDate=="")
               {
                  continue;
               }
               if($releaseDate<=$now)
               {
                  $showAssignment = true;
               }
            }
         }

         if($showAssignment==false)
         {
            continue;
         }

         $assignmentToShowCount++;

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

         $thisURL = "?view=studentList&assignmentID=".$assignmentID;

         if($isStudent==true)
         {
            $thisURL = "?view=studentReport&assignmentID=".$assignmentID;
         }

         $archivedText = '';
         if($isArchived==true)
         {
            $archivedText = ' <span class="greyText">ARCHIVED</span>';
         }

         $html.='<td><a href="'.$thisURL.'">'. get_the_title($assignmentID).'</a>'.$archivedText.'</td>';

         $html.='<td>'. $assessmentDate.' </td>';
         $html.='<td>'. $releaseDate.' </td>';
         //$html.='<td>'. $studentCount.' </td>';
        // $html.='<td>'. $markerCount.' </td>';

         $html.='<tr>';

      }
      $html.='</table>';

      if($assignmentToShowCount==0)
      {
         return 'No assignments found';
      }


	  $return_html = '';
	  $return_html.=\icl_network\draw::content_box_open();
	  $return_html.=$html;
	  $return_html.=\icl_network\draw::content_box_close();
      return $return_html;

   }


   public static function drawMarkingGrid($assignmentID, $username)
   {

       $current_user = wp_get_current_user();
       $loggedInUsername = $current_user->user_login;

      $html = '';

      //$archivedMessage = imperialNetworkDraw::imperialFeedback("This assignment has been archived and changes will not be saved.", "alert");
      // Is this archived?
      $isArchived = get_post_meta( $assignmentID, 'archived', true );
      if($isArchived==true)
      {
         //$html.=$archivedMessage;
         \icl_network\imperial_feedback::set_feedback( "This assignment has been archived and changes will not be saved." );
      }

      $max_discrepancy = get_post_meta( $assignmentID, 'max_discrepancy', true );

      // Get the title
      $assignmentName = get_the_title($assignmentID);

      $studentMeta = \icl_network\user_queries::get_user_info( $username );


      $html.='<div id="agreed_marking_form_wrap">';
      $html.='<h2>'.$assignmentName.'</h2>';
      $html.='<h3>'.$studentMeta->first_name.' '.$studentMeta->last_name.'</h3>';
      $html.= \icl_network\draw::back_button( array( 'value'=>"Back to student list", 'url'=>"?view=studentList&assignmentID=".$assignmentID ) );
      $html.='<hr/>';

      //$current_user = wp_get_current_user();
      //$assessorUsername = $current_user->user_login;


      $formItemsArray = agreedMarkingQueries::getMarkingCriteria($assignmentID);

      // Get the students marked grades
      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);

      // Get the assessor count for this student
      $assessors = agreedMarkingQueries::getAssessorsForStudent($assignmentID, $username);

      $assessorCount = count($assessors);

      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);

      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $loggedInUsername)==false)
      {
         return 'You do not have access to this page';
      }


      if(isset($finalMarks[$loggedInUsername]) )
      {
         $finalMarksForThisAssessor = $finalMarks[$loggedInUsername];
         $html.='<div class="finalMarkWrap">Your Mark : '.$finalMarksForThisAssessor.'%</div>';
      }


      $args = array();
      $args['assessors'] = $assessors;
      $args['assessorUsername'] = $loggedInUsername;
      $args['assignmentID'] = $assignmentID;
      $args['savedMarks'] = $savedMarks;


      $html.= agreedMarkingDraw::showCheckboxErrors($args);

      $markingDisrepancy = agreedMarkingUtils::getMarkingDiscrepancy($finalMarks);


      if($markingDisrepancy>$max_discrepancy)
      {

		 $message = '<strong>Marking Discrepancy of '.$markingDisrepancy.'%</strong><br/>';
         $message.='The mark discrepancy is too high (difference >'.$max_discrepancy.' Marks). Please discuss with your co-marker to resolve and resubmit your amended marks.';
		 $html.=\icl_network\draw::notification($message, 'danger');

      }
      else
      {

         if($assessorCount>1)
         {
            $message='Marking difference is <='.$max_discrepancy.' Marks. No further action required</div>';
			$html.=\icl_network\draw::notification($message, 'success');
         }

      }



      if($assessorCount >1)
      {
         $html.='<div class="agreedMarkingAssessorListWrap">';

         $html.= 'The following assessors have marked this student';
         $html.='<ul class="agreedMarkingAssessorList">';
         foreach ($assessors as $assessorUsername => $assessorInfo)
         {
            $firstName = $assessorInfo['firstName'];
            $lastName = $assessorInfo['lastName'];
            $html.= '<li><i class="fas fa-user"></i>  '.$firstName.' '.$lastName.' : '.$finalMarks[$assessorUsername].'%</li>';
         }
         $html.='</ul>';
         $html.='</div>';


      }
      elseif($assessorCount==0)
      {
         $html.= '<div style="margin:3px; padding:5px; background:#f7f7f7;">Nobody has marked this student yet</div>';
      }



      // Get the current page URL
      $formAction = '?view=markStudent&myAction=markStudent&assignmentID='.$assignmentID.'&username='.$username;


      $html.='<form action="'.$formAction.'" method="post" class="imperial-form" id="myform">';
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
               "loggedInUsername" => $loggedInUsername,
            );

            $html.=agreedMarkingDraw::drawFormItem($args);
         }
         $html.='</div>';

         $html.='</div>';


      }

      // Only allow marking if this is not archived
      if($isArchived==true)
      {
         $html.=$archivedMessage;
      }
      else
      {
         $html.='<input type="submit" value="Submit" data-method="submit_agreed_form" class="button" id="agreedMarkingSubmitButton">';
      }

      $html.='</form>';

      $html.='<p id="checkOnline"></p>';

      $html.='</div>'; // End of agreed_marking_form_wrap

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
      $loggedInUsername = $args['loggedInUsername'];
      $savedValue = '';


      if(!is_array($savedMarks) ) { $savedMarks = array(); }

      if(!is_array($options)){$options = array(); } // if there are no options create blank array



      $thisAssessessorUsername = $loggedInUsername;
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
          case "stepScale":

            $optionNumber = 1;

            if($itemType=="stepScale")
            {
                $options = agreedMarkingQueries::getStepScale();
            }

            $radio_str='<div class="formItemRadioWrap" id="radio_group_'.$thisID.'">';

            // have other people given this mark?
            $assessor_marks_lookup = array();
            // Create temp lookup array of the marks given for this item. Allows us to add the class of other assessor grades
            if(array_key_exists($thisID, $savedMarks) )
            {
                $marks_for_this_item = $savedMarks[$thisID];

                foreach ($marks_for_this_item as $temp_assessor_username => $temp_mark)
                {
                    // Only add the other mark if it's not THIS assessor
                    if($temp_assessor_username<>$loggedInUsername)
                    {
                        $assessor_marks_lookup[$temp_mark][] = $temp_assessor_username;
                    }
                }
            }


            foreach ($options as $optionValue)
            {

               $thisRadioID = $thisID.'_'.$optionNumber;

               $optionValue = $optionNumber;

               // Convert the value to an actual percent if its stepScale

               if($itemType=="stepScale")
               {
                   $optionValue = $options[$optionNumber-1];
               }

               $radio_str.='<label for="'.$thisRadioID.'"';


               // check to see if they have marked this student and don't show discrepencies until they have
               if($isMarkedByYou==true)
               {

                   if(array_key_exists($optionValue, $assessor_marks_lookup) )
                   {
                       $marksDifference = ($optionValue - $savedValue);
                       $marksDifference = ($marksDifference *1);
                       if($optionValue==$savedValue)
                       {
                           $radio_str.=' class="icl-am-radio-success" ';

                       }
                       else
                       {
                           $radio_str.=' class="icl-am-radio-error" ';

                       }

                   }
               }

               $radio_str.='>';
               $radio_str.='<span>'.$optionValue.'</span>';




               $radio_str.='<span><input required type="radio" name="'.$thisID.'" id="'.$thisRadioID.'" value="'.$optionValue.'"';
               if($optionValue==$savedValue){$radio_str.=' checked ';}
               $radio_str.='/></span></label>';
               $optionNumber++;
            }
            $radio_str.='</div>';


            if($isMarkedByYou==true)
            {

               //If there is a discrepenecy then Highlight this
               if(count($savedMarks)>=1 )
               {

                   $saved_marks_str = '';

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

                              $saved_marks_str.= '<div class="imperial-feedback imperial-feedback-success">Marks given by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
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



                           $saved_marks_str.= '<div class="imperial-feedback '.$feedbackClass.'">Marks given by '.$thisAssessorName.' : <strong>'.$tempMarks.'</strong></div>';
                        }
                     }

                  }
               }
            }


            $html.=$radio_str.$saved_marks_str;


         break;


         case "checkbox":
            foreach ($options as $optionID => $optionValue)
            {
               $thisCheckboxID = $thisID.'_'.$optionID;
               $thisCheckboxName = 'checkbox_'.$thisID;

               $isChecked = false;
               if(isset($savedMarks[$thisID][$thisAssessessorUsername]) )
               {

                  $checkArray = unserialize($savedMarks[$thisID][$thisAssessessorUsername]);
                  if(in_array($optionID, $checkArray) )
                  {
                     $isChecked = true;
                  }
               }
               $html.='<label for="'.$thisCheckboxID.'">';
               $html.='<input type="checkbox"  name="'.$thisCheckboxName.'[]" id="'.$thisCheckboxID.'" value="'.$optionID.'"';
               if($isChecked){$html.=' checked ';}
               $html.='/>'.$optionValue.'</label><br/>';

            }

         break;


         case "textarea":
            $html.='<textarea class="agreedMarkingTextarea" name="textarea_'.$thisID.'" id="textarea_'.$thisID.'" required>'.$savedValue.'</textarea>';
         break;

      }

      $html.= '</div>';
      return $html;

   }

   public static function drawStudentFeedback($assignmentID, $username)
   {

      $html = '';
      $isMarker = false;
      $feedbackText  = '';

      $current_user = wp_get_current_user();
      $thisUsername = $current_user->user_login;

      // Get the cap

      // Is the student in the cap?
      $cappedMarks = get_post_meta( $assignmentID, 'cappedMarks', true );

      if($cappedMarks==""){$cappedMarks=40;}

      $cappedStudentArray = get_post_meta( $assignmentID, 'cappedStudentArray', true );
      if(!is_array($cappedStudentArray) ){$cappedStudentArray = array();}



      // Get the title
      $assignmentName = get_the_title($assignmentID);

      // Are they a marker?
      if(agreedMarkingUtils::checkMarkerAccess($assignmentID, $thisUsername)==false)
      {
         if($thisUsername<>$username) // is this THERE student report?
         {
            return 'You do not have access to this page';
         }
      }
      else
      {
         $isMarker = true;
      }

      $studentMeta = \icl_network\user_queries::get_user_info( $username );
      $html.='<h2>'.$assignmentName.'</h2>';
      $html.='<h3>'.$studentMeta->first_name.' '.$studentMeta->last_name.'</h3>';
      if($isMarker==true)
      {
         $html.= \icl_network\draw::back_button( array( 'value'=>"Back to student list", 'url'=>"?view=studentList&assignmentID=".$assignmentID ) );
      }
      $html.='<hr/>';


      $savedMarks = agreedMarkingQueries::getUserMarks($assignmentID, $username);

      // Get the scores
      $finalMarks = agreedMarkingUtils::getFinalMarks($assignmentID, $savedMarks);


      $finalMark = $finalMarks['average'];

      // Are they capped?
      if(in_array($username, $cappedStudentArray) )
      {
         $feedbackText.= 'Your mark has been capped at '.$cappedMarks.'%';
         //$html.= imperialNetworkDraw::imperialFeedback($feedbackText, "alert");
         \icl_network\imperial_feedback::set_feedback( $feedbackText, "alert" );

         if($finalMark>$cappedMarks)
         {
            $finalMark = $cappedMarks;
         }
      }

      $html.= '<div class="agreedMarksFeedbackMark">Your mark : <span class="theMark">'.$finalMark.'%</span></div>';

      $formItemsArray = agreedMarkingQueries::getMarkingCriteria($assignmentID);

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
            if($thisAverage)
            {
               // $thisAverage = round($thisAverage, 2);

               $html.= $thisAverage.' / '.$optionCount;
            }
            else
            {
               $html.='Not yet marked';
            }

         break;

         case "stepScale":
            $tempTotal = 0;
            $assessorCount = 0;

            if($thisFeedback)
            {
               foreach ($thisFeedback as $tempValue)
               {
                  $tempTotal = $tempTotal + $tempValue;
                  $assessorCount++;
               }
               $thisAverage = $tempTotal/$assessorCount;

            }
            if($thisAverage)
            {
            //    $thisAverage = round($thisAverage, 2);
               $html.= $thisAverage.'% ';
            }
            else
            {
               $html.='Not yet marked';
            }

         break;



         case "checkbox":
            // go through the
            $checkboxesToShow = array();

            $tempCheckboxIDarray = array();
            foreach ($savedMarks as $KEY => $VALUE)
            {

               if (strpos($KEY, $itemID) !== false)
               {
                  //unserialise the array
                  foreach ($VALUE as $serialisedChecks)
                  {
                     $tempCheckboxIDarray = unserialize($serialisedChecks);


                     foreach ($tempCheckboxIDarray as $tempID)
                     {
                        $checkboxesToShow[] = $tempID;
                     }
                  }
               }
            }

            $checkboxesToShow = array_unique($checkboxesToShow);



            $i = 1;

            $html.='<ul>';
            foreach ($checkboxesToShow as $tempOptionID)
            {
               if(is_numeric($tempOptionID) )
               {
                  $html.='<li>'.$options[($tempOptionID)].'</li>';
               }
            }
            $html.='</ul>';

         break;


         default:
            if(is_array($thisFeedback) )
            {
               foreach ($thisFeedback as $tempFeedback)
               {
                  $html.= \icl_network\utils::convert_text_from_db( $tempFeedback ) . '<hr/>';
               }
            }
         break;


      }

      return $html;

   }

   public static function showCheckboxErrors($args)
   {


      $html = '';

      $assessors = $args['assessors'];
      $assessorUsername = $args['assessorUsername'];
      $assignmentID = $args['assignmentID'];
      $savedMarks = $args['savedMarks'];

      // If they have marked this then check then check for checkboxes
      if(array_key_exists($assessorUsername, $assessors) )
      {
         // Check that all checkbox groups have got a grade
         $checkboxItems = agreedMarkingUtils::getCheckboxGroups($assignmentID);

         foreach ($checkboxItems as $criteriaID => $checkboxName)
         {
            $checkboxFeedbackGiven = false;
            foreach ($savedMarks as $keyCheck => $theseSavedMarks)
            {
               if($checkboxFeedbackGiven==true){break;} // Quit if feedback is given


               if ($keyCheck== $criteriaID) {

                   foreach ($theseSavedMarks as $tempAssessor => $tempSavedMarks)
                   {
                      if($assessorUsername==$tempAssessor)
                      {
                        $checkboxFeedbackGiven = true;
                        // Remove this checkbox from the checked array
                        unset($checkboxItems[$criteriaID]);
                        break;
                      }
                   }
               }
            }
         }

         $checkboxesNotMarkedCount = count($checkboxItems);

         if($checkboxesNotMarkedCount>=1)
         {

            $feedbackText = '';
            $feedbackText.= '<strong>There are possible problems with this submission.</strong><br/>';
            $feedbackText.='The following checkbox groups have no feedback<hr/>';

            foreach ($checkboxItems as $checkboxName)
            {
               $feedbackText.= ' - '. $checkboxName.' does not have any feedback<br/>';
            }


            $feedbackText.= '<br/>Please provide feedback from the pre-selected comments.';

            //$html.= imperialNetworkDraw::imperialFeedback($feedbackText, "error");
            echo \icl_network\draw::notification( $feedbackText, "error" );

         }
      }

      return $html;
   }




}


?>
