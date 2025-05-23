<?php
include 'config.php';
if(isset($_POST['username'])){
   $username = $_POST['username'];
   // Check username
   $stmt = $conn->prepare("SELECT count(*) as cntUser FROM users WHERE UserName=:username");
   $stmt->bindValue(':username', $username, PDO::PARAM_STR);
   $stmt->execute(); 
   $count = $stmt->fetchColumn();
   $response = "<span style='color: green;'>Available.</span>";
   if($count > 0){
      $response = "<span style='color: red;'>Not Available.</span>";
   }
   echo $response;
   exit;
}