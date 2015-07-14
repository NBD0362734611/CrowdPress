<?php
	// load hybridauth base file, change the following paths if necessary
	// note: in your application you probably have to include these only when required.
	$hybridauth_config = dirname(__FILE__) . '/hybridauth/config.php';
	require_once( "hybridauth/Hybrid/Auth.php" );

	// database config
	$database_host = "localhost:8888";
	$database_user = "root";
	$database_pass = "alue1029";
	$database_name = "CrowdPress";

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

    function h($str){
    	// 変換本体
    	$str=htmlentities($str, ENT_QUOTES, mb_internal_encoding());
    	// 「(」「)」を変換
    	$str=mb_ereg_replace("\(","&#40;",$str);
    	$str=mb_ereg_replace("\)","&#41;",$str);

    	echo $str;
    	return $str;
    }

    function hbr($str){
    	// 変換本体
    	$str=htmlentities($str, ENT_QUOTES, mb_internal_encoding());
    	// 「(」「)」を変換
    	$str=mb_ereg_replace("\(","&#40;",$str);
    	$str=mb_ereg_replace("\)","&#41;",$str);

    	echo nl2br($str);
    	return nl2br($str);
    }

    function htag($str){
        // 変換本体
        $str=htmlentities($str, ENT_QUOTES, mb_internal_encoding());
        // 「(」「)」を変換
        $str=mb_ereg_replace("\(","&#40;",$str);
        $str=mb_ereg_replace("\)","&#41;",$str);

        //一部のタグを許可する
        $search = array('&lt;p&gt;','&lt;/p&gt;','&lt;br&gt;','&lt;/br&gt;');
        $replace = array('<p>','</p>','<br>','</br>');

        echo str_replace($search,$replace,$str);
        return str_replace($search,$replace,$str);
    }

    function escape( $value ){
        // 数値以外をクオートする
        if (!is_numeric($value)) {
            if ( is_array ($value) ) {
                $value = array_map("escape", $value);
            } else {
                $value = mysql_real_escape_string($value);
            }
        }
        return $value;
    }
