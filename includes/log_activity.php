<?php
function logActivity($conn, $user_id, $action, $record){
$query = "INSERT INTO activity_logs 
(user_id, action_type, affected_record) 
VALUES ('$user_id','$action','$record')";
mysqli_query($conn,$query);
}
?>