<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "arch2";

if(!$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname))
{

	die("failed to connect!");
}
