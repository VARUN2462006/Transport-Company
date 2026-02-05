<?php

$host="localhost";
$user="root";
$password="";
$db="login";

$conn=new mysqli($host,$user,$password,$db);
if($conn->connect_error){
  echo "Failed to connect Database".$conn->connect_error;
}
?>