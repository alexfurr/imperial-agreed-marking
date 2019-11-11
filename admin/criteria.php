<h1>Markers and Students</h1>

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!isset($_GET['id']) )
{
   echo 'No Assessment ID!';
   die();
}
$assignmentID = $_GET['id'];
?>

Criteria Groups Top Level. Criteria Groups have:<br/>
- Criteria Name<br/>
- Criteria Weighting<br/>
- Order (Drag and drop)<br/>
- Assignment ID
<hr/>
Criteria are in groups and have<br/>
- Criteria Name<br/>
- Criteria Order (Drag and Drop)
- Criteria Type (radio / checkbox / textarea)<br/>
- Criteria Group ID (Parent)
<hr/>
Criteria Options belong to a criteria and have:<br/>
- Option Name<br/>
- option Order (Drag and drop)<br/>
- Criteria ID (Parent)<br/>
