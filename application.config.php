<?php
	// load hybridauth base file, change the following paths if necessary
	// note: in your application you probably have to include these only when required.
	$hybridauth_config = dirname(__FILE__) . '/hybridauth/config.php';
	require_once( "hybridauth/Hybrid/Auth.php" );

	// database config
	$database_host = "localhost";
	$database_user = "root";
	$database_pass = "alue1029";
	$database_name = "crowdpress";

	$database_link = @ mysql_connect( $database_host, $database_user, $database_pass );

	if ( ! $database_link ) {
		die( "Please edit the configuration file: <b>application.config.php</b>. <hr><b>Mysql error</b>: " . mysql_error() );
	}

	$db_selected = mysql_select_db( $database_name );
	mysql_set_charset('utf8');

	if ( ! $db_selected ) {
		die( "Please edit the configuration file: <b>application.config.php</b>. <hr><b>Mysql error</b>: " . mysql_error() );
	}

	function mysql_query_excute( $sql ){
		$result = mysql_query($sql);

		if (!$result) {
			$message  = 'Invalid query: ' . mysql_error() . "\n";
			$message .= 'Whole query: ' . $sql;
			die($message);
		}

		return $result;
	}
