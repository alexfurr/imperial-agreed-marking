<h1>Markers and Students</h1>

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!isset($_GET['id']) )
{
   echo 'No Assessment ID!';
   die();
}
$assignmentID = $_GET['id'];



if(isset($_GET['myAction']) )
{
   $action = $_GET['myAction'];

   switch ($action)
   {


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


   default:

      echo '<div class="admin-settings-group">';
      echo '<h2>Markers</h2>';
      echo drawUserUploadForm($assignmentID, "marker");
      echo drawUserTable($assignmentID, "marker");
      echo '</div>';

      echo '<div class="admin-settings-group">';
      echo '<h2>Students</h2>';
      echo drawUserUploadForm($assignmentID, "student");
      echo drawUserTable($assignmentID, "student");
      echo '</div>';

   break;


}



function drawUserUploadForm($assignmentID, $userType)
{

   $html='';

   $html.='<a href="javascript:toggleUploadForm(\''.$userType.'\')" class="button-secondary">Add '.$userType.'s</a>';
   $html.='<div id="userDiv_'.$userType.'" style="display:none;">';
   $html.= '<span class="smallText">Add one username on each row</span>';
   $html.= '<form action="?page=agreed-marking-users&id='.$assignmentID.'&myAction=addUsers" method="post" class="imperial-form">';
   $html.= '<textarea name="'.$userType.'List" id="'.$userType.'List" cols="20" rows="10"></textarea>';
   $html.= '<input type="submit" value="Add '.ucfirst($userType).'s"  >';
   $html.= '<input type="hidden" name="userType" value="'.$userType.'">';
   $html.= '</form>';
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

function drawUserTable($assignmentID, $userType)
{


   $html='';
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
   $html.='</tr></thead>';

   foreach ($userArray as $thisUsername)
   {

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
         $html.='<td class="smallText">';
         foreach ($finalMarks as $KEY => $VALUE)
         {
            if($KEY<>"average")
            {
               $html.=$KEY.' : '.$VALUE.'% <a href="?page=agreed-marking-users&id='.$assignmentID.'&markerUsername='.$KEY.'&student='.$thisUsername.'&view=deleteMarkCheck">(Remove Mark)</a><br/>';
            }
         }

         $html.='</td>';
         $html.='<td>'.$finalMark.'</td>';
      }

      $html.='<td><a class="button-secondary" href="?page=agreed-marking-users&id='.$assignmentID.'&myAction=removeUser&username='.$thisUsername.'&userType='.$userType.'">Remove</a></td>';
      $html.='</tr>';


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
   return $html;

}

?>
