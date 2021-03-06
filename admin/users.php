<h1>Markers and Students</h1>

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!isset($_GET['id']) )
{
   echo 'No Assessment ID!';
   die();
}
$assignmentID = $_GET['id'];


// Get the archived status
$archived = get_post_meta( $assignmentID, 'archived', true );
$cappedMarks = get_post_meta( $assignmentID, 'archived', true );
if($cappedMarks==""){$cappedMarks=40;}

$cappedStudentArray = get_post_meta( $assignmentID, 'cappedStudentArray', true );
if(!is_array($cappedStudentArray) ){$cappedStudentArray = array();}


if($archived<>true)
{
   if(isset($_GET['myAction']) )
   {
      $action = $_GET['myAction'];

      switch ($action)
      {

         case "capMark":
            $studentUsername=$_GET['username'];

            // Get the capped Marks Array

            $cappedStudentArray[] = $studentUsername;
            update_post_meta( $assignmentID, 'cappedStudentArray', $cappedStudentArray );

            // Now resave the array
            $message = "User has been capped";
            echo agreedMarkingAdminDraw::drawAdminNotice($message);

         break;

         case "uncapMark":
            $studentUsername=$_GET['username'];
            $cappedStudentArray = array_unique($cappedStudentArray);

            if (($key = array_search($studentUsername, $cappedStudentArray)) !== false) {
                unset($cappedStudentArray[$key]);
            }

            update_post_meta( $assignmentID, 'cappedStudentArray', $cappedStudentArray );

            // Now resave the array
            $message = "User has been uncapped";
            echo agreedMarkingAdminDraw::drawAdminNotice($message);

         break;


         case "removeUser":
            $userType=$_GET['userType'];
            $username = $_GET['username'];


            $metaKey = 'my'.ucfirst($userType).'s';
            $tempArray = get_post_meta( $assignmentID, $metaKey, true );


            // remove this from the array
            if (($key = array_search($username, $tempArray)) !== false) {
                unset($tempArray[$key]);
            }

            // Now resave the array
            $message = ucfirst($userType).' removed';
            echo agreedMarkingAdminDraw::drawAdminNotice($message);

            update_post_meta( $assignmentID, $metaKey, $tempArray );


         break;



         case "addUsers":

            $userType=$_POST['userType'];
            $tempArray = array();

            
            
            //print_r( $userType );
            if($userType=="student")
            {
               $metaKeyName = "myStudents";
               $tempArray = get_post_meta( $assignmentID, 'myStudents', true );
               $newUserList = $_POST['studentList'];
               
               //print_r( $tempArray );
               //die();
            }

            if($userType=="marker")
            {
               $metaKeyName = "myMarkers";
               $tempArray = get_post_meta( $assignmentID, 'myMarkers', true );
               $newUserList = $_POST['markerList'];
            }
                
            if ( empty($tempArray) || ! is_array($tempArray) ) {
                $tempArray = array();
            };
                
            // If its not an array then there must be mo existing users


            $tempUploadArray = explode("\n", $newUserList);

            foreach ($tempUploadArray as $thisUser)
            {
               $thisUser = trim($thisUser);
               if($thisUser==""){continue;}
               $tempArray[] = strtolower($thisUser);
            }


            $arrayToAdd = array_unique($tempArray);
            $message = ucfirst($userType).'s added';
            echo agreedMarkingAdminDraw::drawAdminNotice($message);

            update_post_meta( $assignmentID, $metaKeyName, $arrayToAdd );


         break;

         case "deleteMark":
            $studentUsername=$_GET['student'];
            $markerUsername=$_GET['markerUsername'];

            global $agreedMarkingUserMarks;
            global $wpdb;


            $wpdb->query( $wpdb->prepare( "DELETE FROM $agreedMarkingUserMarks WHERE assessorUsername = %s and username =  %s and assignmentID = %d",
            $markerUsername,
            $studentUsername,
            $assignmentID  ) );




            // Now resave the array
            $message = "This mark has been removed";
            echo agreedMarkingAdminDraw::drawAdminNotice($message);


         break;



      }

   }

} // End of if archived
$view='';
if(isset($_GET['view']) )
{
   $view = $_GET['view'];
}

switch ($view)
{

   case "deleteMarkCheck":
      $markerUsername = $_GET['markerUsername'];
      $studentUsername = $_GET['student'];
      $studentMeta = \icl_network\user_queries::get_user_info( $studentUsername );

      $markerMeta = \icl_network\user_queries::get_user_info( $markerUsername );

      $markerFullname = $markerMeta->first_name.' '.$markerMeta->last_name;
      $studentFullname = $studentMeta->first_name .' '.$studentMeta->last_name;
      echo 'Are you sure you want to remove the marks for <strong>'.$studentFullname.'</strong> submitted by <strong>'.$markerFullname.'</strong>?<br/>';
      echo '<br/>';
      echo 'This will delete all marks and written feedback and cannot be undone!<hr/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&markerUsername='.$markerUsername.'&student='.$studentUsername.'&myAction=deleteMark"class="button-primary">Yes, delete these marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;

   case "capMarkCheck":
      $studentUsername = $_GET['username'];
      $studentMeta = \icl_network\user_queries::get_user_info( $studentUsername );

      $studentFullname = $studentMeta->first_name.' '.$studentMeta->last_name;
      echo 'Are you sure you want to cap the marks for <strong>'.$studentFullname.'</strong> at '.$cappedMarks.'%<br/>';
      echo '<br/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&username='.$studentUsername.'&myAction=capMark"class="button-primary">Yes, cap the marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;

   case "uncapMarkCheck":
      $studentUsername = $_GET['username'];
      $studentMeta = \icl_network\user_queries::get_user_info( $studentUsername );

      $studentFullname = $studentMeta->first_name.' '.$studentMeta->last_name;
      echo 'Are you sure you want to UNCAP the marks for <strong>'.$studentFullname.'</strong> <br/>';
      echo '<br/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&username='.$studentUsername.'&myAction=uncapMark"class="button-primary">Yes, UNCAP the marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;


   default:



      $markersContent = "";
      if($archived<>true)
      {
         $markersContent.= drawUserUploadForm($assignmentID, "marker");
      }
      $markersContent.= agreedMarkingAdminDraw::drawUserTable($assignmentID, "marker");

      $studentsContent = "";



      if($archived<>true)
      {
         $studentsContent.= drawUserUploadForm($assignmentID, "student");
      }

      $studentsContent.= agreedMarkingAdminDraw::drawUserTable($assignmentID, "student");


      $tabArray = array(

         array(
            "tab_title" => 'Students',
            "tab_id" => "studentsTab",
            "tab_content" => $studentsContent,
         ),

         array(
            "tab_title" => "Markers",
            "tab_id" => "markersTab",
            "tab_content" => $markersContent,

         ),
      );

      // Download button
      echo '<div class="downloadAgreedMarksButton">';
      echo '<a href="options.php?page=agreed-marking-users&id='.$assignmentID.'&myAction=downloadMarks" class="button-secondary"><i class="fas fa-download"></i> Download Marks</a>';
      echo '</div>';

      echo \imperial_tabs::draw($tabArray);



   break;


}



function drawUserUploadForm($assignmentID, $userType)
{



   $html='';
   $html.='<div class="agreedMarkingUploadFormWrap">';

   $html.='<div class="agreedMarkingStudentButtonsWrap">';
   $html.='<div>';
   $html.='<a href="javascript:toggleUploadForm(\''.$userType.'\')" class="button-secondary">Add '.$userType.'s</a>';
   $html.='</div>';


   $html.='</div>';

   $html.='<div id="userDiv_'.$userType.'" style="display:none;">';
   $html.= '<span class="smallText">Add one username on each row</span>';
   $html.= '<form action="?page=agreed-marking-users&id='.$assignmentID.'&myAction=addUsers" method="post" class="imperial-form">';
   $html.= '<textarea name="'.$userType.'List" id="'.$userType.'List" cols="20" rows="10"></textarea>';
   $html.= '<input type="submit" value="Add '.ucfirst($userType).'s"  >';
   $html.= '<input type="hidden" name="userType" value="'.$userType.'">';
   $html.= '</form>';
   $html.='</div>';
   $html.='</div>';


   $html.='<script>
   function toggleUploadForm(userType)
   {
      var myDiv = "userDiv_"+userType;
      console.log(myDiv);
   	jQuery( "#"+myDiv ).toggle( "fast");
   }

   </script>';

   return $html;

}



?>
