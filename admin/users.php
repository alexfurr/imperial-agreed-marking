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
            echo imperialNetworkDraw::drawAdminNotice($message);

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
            echo imperialNetworkDraw::drawAdminNotice($message);

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
            echo imperialNetworkDraw::drawAdminNotice($message);

            update_post_meta( $assignmentID, $metaKey, $tempArray );


         break;



         case "addUsers":

            $userType=$_POST['userType'];


            if($userType=="student")
            {
               $metaKeyName = "myStudents";
               $tempArray = get_post_meta( $assignmentID, 'myStudents', true );
               $newUserList = $_POST['studentList'];
            }

            if($userType=="marker")
            {
               $metaKeyName = "myMarkers";
               $tempArray = get_post_meta( $assignmentID, 'myMarkers', true );
               $newUserList = $_POST['markerList'];
            }

            // If its not an array then there must be mo existing users
            if(!is_array($tempArray) )
            {
               $tempArray = array();
            }

            $tempUploadArray = explode("\n", $newUserList);

            foreach ($tempUploadArray as $thisUser)
            {
               $thisUser = trim($thisUser);
               if($thisUser==""){continue;}
               $tempArray[] = $thisUser;
            }


            $arrayToAdd = array_unique($tempArray);
            $message = ucfirst($userType).'s added';
            echo imperialNetworkDraw::drawAdminNotice($message);

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
            echo imperialNetworkDraw::drawAdminNotice($message);


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
      $studentMeta = imperialQueries::getUserInfo($studentUsername);
      $markerMeta = imperialQueries::getUserInfo($markerUsername);

      $markerFullname = $markerMeta['first_name'].' '.$markerMeta['last_name'];
      $studentFullname = $studentMeta['first_name'].' '.$studentMeta['last_name'];
      echo 'Are you sure you want to remove the marks for <strong>'.$studentFullname.'</strong> submitted by <strong>'.$markerFullname.'</strong>?<br/>';
      echo '<br/>';
      echo 'This will delete all marks and written feedback and cannot be undone!<hr/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&markerUsername='.$markerUsername.'&student='.$studentUsername.'&myAction=deleteMark"class="button-primary">Yes, delete these marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;

   case "capMarkCheck":
      $studentUsername = $_GET['username'];
      $studentMeta = imperialQueries::getUserInfo($studentUsername);

      $studentFullname = $studentMeta['first_name'].' '.$studentMeta['last_name'];
      echo 'Are you sure you want to cap the marks for <strong>'.$studentFullname.'</strong> at '.$cappedMarks.'%<br/>';
      echo '<br/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&username='.$studentUsername.'&myAction=capMark"class="button-primary">Yes, cap the marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;

   case "uncapMarkCheck":
      $studentUsername = $_GET['username'];
      $studentMeta = imperialQueries::getUserInfo($studentUsername);

      $studentFullname = $studentMeta['first_name'].' '.$studentMeta['last_name'];
      echo 'Are you sure you want to UNCAP the marks for <strong>'.$studentFullname.'</strong> <br/>';
      echo '<br/>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'&username='.$studentUsername.'&myAction=uncapMark"class="button-primary">Yes, UNCAP the marks</a>';
      echo '<a href="?page=agreed-marking-users&id='.$assignmentID.'" class="button-secondary">Cancel</a>';


   break;


   default:

      echo '<div class="admin-settings-group">';
      echo '<h2>Markers</h2>';
      if($archived<>true)
      {
         echo drawUserUploadForm($assignmentID, "marker");
      }
      echo agreedMarkingAdminDraw::drawUserTable($assignmentID, "marker");
      echo '</div>';

      echo '<div class="admin-settings-group">';
      echo '<h2>Students</h2>';
      if($archived<>true)
      {
         echo drawUserUploadForm($assignmentID, "student");
      }
      echo agreedMarkingAdminDraw::drawUserTable($assignmentID, "student");
      echo '</div>';

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


   if($userType=="student")
   {
      // Download button
      $html.='<div>';
      $html.='<a href="options.php?page=agreed-marking-users&id='.$assignmentID.'&myAction=downloadMarks" class="button-secondary"><i class="fas fa-download"></i> Download Marks</a>';
      $html.='</div>';
   }
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
