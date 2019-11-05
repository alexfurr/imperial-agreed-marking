<h1>Markers and Students</h1>

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!isset($_GET['id']) )
{
   echo 'No Assessment ID!';
   die();
}
$assessmentID = $_GET['id'];



if(isset($_GET['myAction']) )
{
   $action = $_GET['myAction'];

   switch ($action)
   {


      case "removeUser":
         $userType=$_GET['userType'];
         $username = $_GET['username'];


         $metaKey = 'my'.ucfirst($userType).'s';
         $tempArray = get_post_meta( $assessmentID, $metaKey, true );



         // remove this from the array
         if (($key = array_search($username, $tempArray)) !== false) {
             unset($tempArray[$key]);
         }

         // Now resave the array
         $message = ucfirst($userType).' removed';
         echo imperialNetworkDraw::drawAdminNotice($message);

         update_post_meta( $assessmentID, $metaKey, $tempArray );







      break;



      case "addUsers":

      $userType=$_POST['userType'];


         if($userType=="student")
         {
            $metaKeyName = "myStudents";
            $tempArray = get_post_meta( $assessmentID, 'myStudents', true );
            $newUserList = $_POST['studentList'];
         }

         if($userType=="marker")
         {
            $metaKeyName = "myMarkers";
            $tempArray = get_post_meta( $assessmentID, 'myMarkers', true );
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

         update_post_meta( $assessmentID, $metaKeyName, $arrayToAdd );


      break;


   }

}


echo '<div class="admin-settings-group">';
echo '<h2>Markers</h2>';
echo drawUserUploadForm($assessmentID, "marker");
echo drawUserTable($assessmentID, "marker");
echo '</div>';

echo '<div class="admin-settings-group">';
echo '<h2>Students</h2>';
echo drawUserUploadForm($assessmentID, "student");
echo drawUserTable($assessmentID, "student");
echo '</div>';


function drawUserUploadForm($assessmentID, $userType)
{

   $html='';

   $html.='<a href="javascript:toggleUploadForm(\''.$userType.'\')" class="button-secondary">Add '.$userType.'s</a>';
   $html.='<div id="userDiv_'.$userType.'" style="display:none;">';
   $html.= '<span class="smallText">Add one username on each row</span>';
   $html.= '<form action="?page=agreed-marking-users&id='.$assessmentID.'&myAction=addUsers" method="post" class="imperial-form">';
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

function drawUserTable($assessmentID, $userType)
{


   $html='';
   $userCount = 0;


   switch($userType)
   {

      case "marker":
         $userArray = get_post_meta( $assessmentID, 'myMarkers', true );
      break;

      case "student";
         $userArray = get_post_meta( $assessmentID, 'myStudents', true );
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


   $html.='<table class="imperial-table">';
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

      $html.='<tr class="'.$errorClass.'">';
      $html.='<td>'.$fullName.'</td>';
      $html.='<td>'.$thisUsername.'</td>';
      $html.='<td><a class="button-secondary" href="?page=agreed-marking-users&id=4&myAction=removeUser&username='.$thisUsername.'&userType='.$userType.'">Remove</a></td>';
      $html.='</tr>';


   }
   $html.='</table>';
   return $html;

}

?>
