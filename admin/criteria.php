<h1>Criteria</h1>

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

   global $wpdb;
   global $agreedMarkingCriteriaGroups;
   global $agreedMarkingCriteria;
   global $agreedMarkingCriteriaOptions;
   switch ($action)
   {



      case "createCriteriaGroup":

         $groupName = sanitize_textarea_field($_POST['groupName']);
         $weighting = $_POST['weighting'];
         $groupOrder = $_POST['groupOrder'];

         if($groupName<>"")
         {

            $myFields="INSERT into $agreedMarkingCriteriaGroups (assignmentID, groupName, weighting, groupOrder) ";
            $myFields.="VALUES (%d, %s, %d, %d)";


            $RunQry = $wpdb->query( $wpdb->prepare($myFields,
               $assignmentID,
               $groupName,
               $weighting,
               $groupOrder
            ));
         }


      break;

      case "createCriteria":

        $criteriaName = sanitize_textarea_field($_POST['criteriaName']);
        $criteriaOrder = $_POST['criteriaOrder'];
        $criteriaType = $_POST['criteriaType'];
        $groupID = $_POST['groupID'];

        if($criteriaName<>"")
        {

           $myFields="INSERT into $agreedMarkingCriteria (groupID, criteriaName, criteriaOrder, criteriaType) ";
           $myFields.="VALUES (%d, %s, %d, %s)";


           $RunQry = $wpdb->query( $wpdb->prepare($myFields,
               $groupID,
               $criteriaName,
               $criteriaOrder,
               $criteriaType
           ));
        }


      break;


      case "createOption":

       $optionValue = sanitize_textarea_field($_POST['optionValue']);
       $optionOrder = $_POST['optionOrder'];
       $criteriaID = $_POST['criteriaID'];


       if($optionValue<>"")
       {

           $myFields="INSERT into $agreedMarkingCriteriaOptions (criteriaID, optionValue, optionOrder) ";
           $myFields.="VALUES (%d, %s, %d)";


           $RunQry = $wpdb->query( $wpdb->prepare($myFields,
               $criteriaID,
               $optionValue,
               $optionOrder
           ));
       }


      break;

   }


}




$myGroups = agreedMarkingQueries::getCriteriaGroups($assignmentID);

?>
<div class="admin-settings-group">

<form action="?page=agreed-marking-criteria&id=<?php echo $assignmentID;?>&myAction=createCriteriaGroup" method="post">
<label for="groupName">Group Name<br/>
<input type="text" name="groupName" id="groupName"><label>
<br/>
<label for="weighting">Weighting<br/>
<input type="text" name="weighting" id="weighting"><label>
<br/>
<label for="groupOrder">Order<br/>
<input type="text" name="groupOrder" id="groupOrder"><label>
<br/>
<input type="submit" value="Create Group" class="button-secondary">
</form>
</div>

<?php

foreach ($myGroups as $groupMeta)
{

   echo '<div class="admin-settings-group">';
   $groupID = $groupMeta['groupID'];
   $groupName = $groupMeta['groupName'];
   $weighting = $groupMeta['weighting'];
   $groupOrder = $groupMeta['groupOrder'];

   echo '<div width="100%" style="background:#003366; color:#fff; font-size:14px; padding:10px;">Group : '.$groupName.'</div>';

   echo '<div class="smalltext">weighting : '.$weighting.' and order : '.$groupOrder.'</div>';
   echo '<hr/>';

   // Get the Crtieria
   $myCriteria = agreedMarkingQueries::getCriteriaInGroup($groupID);
   echo '<h3>Criteria</h3>';
   ?>

   <form action="?page=agreed-marking-criteria&id=<?php echo $assignmentID;?>&myAction=createCriteria" method="post">
   <label for="criteriaName_<?php echo $groupID;?>">Criteria Name<br/>
   <input type="text" name="criteriaName" id="criteriaName_<?php echo $groupID;?>"></label>
   <br/>
   <label for="criteriaType__<?php echo $groupID;?>">Type<br/>
   <select name="criteriaType" id="criteriaType_<?php echo $groupID;?>">
      <option value="radio">Radio Button</option>
      <option value="checkbox">Checkbox</option>
      <option value="textarea">Textarea</option>
   </select>
   </label>

   <br/>
   <label for="criteriaOrder_<?php echo $groupID;?>">Order<br/>
   <input type="text" name="criteriaOrder" id="criteriaOrder_<?php echo $groupID;?>"></label>
   <br/>
   <input type="hidden" name="groupID" value="<?php echo $groupID;?>">
   <input type="submit" value="Create New Criteria in <?php echo $groupName;?>" class="button-secondary">
</form><br/><br/>
   <?php

   foreach ($myCriteria as $criteriaMeta)
   {

      $criteriaID = $criteriaMeta['criteriaID'];
      $criteriaName = $criteriaMeta['criteriaName'];
      $criteriaType = $criteriaMeta['criteriaType'];
      $criteriaOrder = $criteriaMeta['criteriaOrder'];

      echo '<div style="border: 1px solid #ccc; padding:5px; margin:5px;">';
      echo $criteriaType.' ('.$criteriaOrder.')<br/>';

      echo '<div width="100%" style="background:#336699; color:#fff; font-size:14px; padding:10px;">'.$criteriaName.'</div>';




      $myOptions = agreedMarkingQueries::getCriteriaOptions($criteriaID);

      foreach ($myOptions as $optionMeta)
      {

         $optionID = $optionMeta['optionID'];
         $optionValue = $optionMeta['optionValue'];
         $optionOrder = $optionMeta['optionOrder'];


         echo $optionValue.' ('.$optionOrder.')<hr/>';


      }
      ?>

     <form action="?page=agreed-marking-criteria&id=<?php echo $assignmentID;?>&myAction=createOption" method="post">
     <label for="optionValue_<?php echo $criteriaID;?>">Option Value<br/>
     <input type="text" name="optionValue" id="optionValue_<?php echo $criteriaID;?>"><label>
     <br/>


     <br/>
     <label for="optionOrder_<?php echo $criteriaID;?>">Order<br/>
     <input type="text" name="optionOrder" id="optionOrder_<?php echo $criteriaID;?>"><label>
     <br/>
     <input type="hidden" name="criteriaID" value="<?php echo $criteriaID;?>">
     <input type="submit" value="Create New option" class="button-secondary">
     </form>

     <?php

      echo '</div>';

   }


   echo '</div>'; // End of group







}


?>
