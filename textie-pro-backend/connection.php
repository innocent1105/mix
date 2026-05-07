<?php

	$dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "";
	$dbname = "textie_pro";

	if(!$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname)){
		die("failed to connect!");
	}
