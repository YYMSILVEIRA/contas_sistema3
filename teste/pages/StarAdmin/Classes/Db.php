<?php

namespace Db;

	$servidor   = "localhost";
	$user 		=  "root";
	$pass		=  "C@n&c@D&C@f&2024@__@";
	$db_name	=  "bi";
/**
* 
*/

	$mysqli =  mysqli_connect($servidor, $user , $pass, $db_name);	
	if (mysqli_connect_error()) {
				echo "Falha na conexao" . mysqli_connect_error();
			}		

