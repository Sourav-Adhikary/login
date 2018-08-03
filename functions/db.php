<?php
$host = "sql209.byethost5.com";
$user="b5_22467925";
$password="dbproject";
$database ="b5_22467925_north_hall_management";


$host = "localhost";
$user="root";
$password="";
$database ="login_db";

$con = mysqli_connect( $host, $user, $password, $database);







function row_count($result){

  return mysqli_num_rows($result);
}


function escape($string){
  global $con;
  return mysqli_real_escape_string($con, $string);
}


function query($query){
  global $con ;
  return mysqli_query($con,$query);
}

function confirm($result){
  global $con;
  if(!$result){
    die("query failed".mysqli_error($con));
  }


}

function fetch_array($result){
  global $con;
return  mysqli_fetch_array($result);

}


 ?>
