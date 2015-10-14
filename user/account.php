<?php
/**
getUserPassword - Receives Users Password 
getUserID – gets the usersID based on their username.
getUserName – gets username based on ID
changePassword – Change password with hashing password
RemoveDevice – remove devices and cascade.

**/
require('../include/lib/password.php');
include('../include/search/function.php');
//getUserPassword - Receives Users Password 
function getUserPassword($user){
 global $conn;
    $sql = "SELECT * FROM users WHERE username='$user'";
// SEARCH CATEGORY IN DATABASE
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      $x = 0;
    // output data of each row
      while($row = $result->fetch_assoc()) {
        
        $password = $row["password"];
        $email = $row["email"];
        $arr = array("email" => $email, "password" => $password);
      }
     
    } else {
    //Exception 
     $arr = array('success' =>  0); 
      echo json_encode($arr);
    }
  
    return $arr;
  }

//getUserID – gets the usersID based on their username.
function getUserID($username){
     global  $conn;
    $sql = "SELECT idusers from users where username = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
      if($row = $result->fetch_assoc()) {
        $usersID = $row['idusers'];
        return $usersID;
      }
    } else {
      //Exception Handling
      echo "0 results";
    }
    
  }


//getUserName – gets username based on ID
function getUsername($userID){
     global  $conn;
    $sql = "SELECT username from users where idusers = '$userID'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
      if($row = $result->fetch_assoc()) {
        $usersID = $row['username'];
        return $usersID;
      }
    } else {
      //Exception Handling
      $arr = array('success' =>  2); 
      echo json_encode($arr);
    }
    
  }


//changePassword – Change password with hashing password
function ChangePassword($userID, $oldPwd, $newPwd, $confirmNPwd){
  if($newPwd == $confirmNPwd){
  global $conn;
    
  $username = getUsername($userID);
  $userInfo = getUserPassword($username);
   
  $hash = password_hash($newPwd, PASSWORD_BCRYPT);
  if(password_verify($oldPwd, $userInfo["password"])){
    $sql = "UPDATE users SET password='$hash' WHERE idusers='$userID'";
// SEARCH CATEGORY IN DATABASE
  if ($conn->query($sql) === TRUE) {
   $arr = array('success' =>  1); 
      echo json_encode($arr);
  } else {
    $arr = array('success' =>  0); 
      echo json_encode($arr);
    }
  }else{
    $arr = array('success' =>  0); 
      echo json_encode($arr);
  }
$conn->close();
  }else{
  $arr = array('success' =>  2); 
      echo json_encode($arr);
  }
}

//RemoveDevice – remove devices and cascade.
function RemoveDevice($userID, $deviceID){ 
  global $conn; 
    $sql = "DELETE FROM devices WHERE iddevices='$deviceID'";
    if ($conn->query($sql) === TRUE) {
      $arr = array('success' =>  1); 
      echo json_encode($arr);
    } else {
      $arr = array('success' =>  0); 
      echo json_encode($arr);
    }

   
}
?>