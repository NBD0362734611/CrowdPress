<?php
class user extends model {
	function create( $email, $password, $display_name, $first_name, $last_name, $photo_url){
		$sql = "INSERT INTO users ( email, password, display_name, first_name, last_name, photo_url, created_at ) VALUES ( '$email', '$password', '$display_name', '$first_name', '$last_name', '$photo_url', NOW() ) ";

		mysql_query_excute($sql);

		return mysql_insert_id();
	}

	function update( $user_id, $email, $password, $first_name, $last_name){
		$sql = "UPDATE users SET email = '$email', password = '$password', first_name = '$first_name', last_name = '$last_name' WHERE id = '$user_id' LIMIT 1";

		return mysql_query_excute($sql);
	}

	function update_mypage( $user_id, $display_name, $upapername, $paper_explain, $facebook_url, $twitter_url, $website_url){
		$sql = "UPDATE `users` SET `display_name` = '$display_name', `upapername` = '$upapername', `paper_explain` = '$paper_explain', `facebook_url` = '$facebook_url', `twitter_url` = '$twitter_url', `website_url` = '$website_url' WHERE id = '$user_id' LIMIT 1";

		return mysql_query_excute($sql);
	}

	function select_newrelease( $user_id, $first_name, $last_name, $upapername, $paper_explain){
		$sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', upapername = '$upapername', paper_explain = '$paper_explain' WHERE id = '$user_id' LIMIT 1";

		return mysql_query_excute($sql);
	}

	function find_by_id( $id ){
		$sql = "SELECT * FROM users WHERE id = '$id' LIMIT 1";

		$result = mysql_query_excute($sql);

		return mysql_fetch_assoc($result);
	}

	function find_by_email( $email ){
		$sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";

		$result = mysql_query_excute($sql);

		return mysql_fetch_assoc($result);
	}

	function find_by_email_and_password( $email, $password ){
		$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";

		$result = mysql_query_excute($sql);

		return mysql_fetch_assoc($result);
	}

	function release_comment_insert($reply, $rid, $user_id, $comment){
		$sql = "INSERT INTO `r_comment`(`reply`, `rid`, `user_id`, `comment`) VALUES ('$reply', '$rid', '$user_id', '$comment')";

		return mysql_query_excute($sql);
	}

	function paper_comment_insert($reply, $paper_id, $user_id, $comment){
		$sql = "INSERT INTO `p_comment`(`reply`, `paper_id`, `user_id`, `comment`) VALUES ('$reply', '$paper_id', '$user_id', '$comment')";

		return mysql_query_excute($sql);
	}

	function release_comment_select($rid, $user_id){
		$release_comment_data = array();
		$sql = "SELECT `comment`, `photo_url` FROM `r_comment` INNER JOIN `users` ON `r_comment`.`user_id` = `users`.`id` WHERE `rid` = $rid AND `user_id` = $user_id";
		$result = mysql_query_excute($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$release_comment_data[] = $row;
        }
        if( isset($release_comment_data) ){
			return $release_comment_data;
		}else{
			return array();
		}
	}

	function paper_comment_select($paper_id){
		$paper_comment_data = array();
		$sql = "SELECT `comment`, `photo_url` FROM `p_comment` INNER JOIN `users` ON `p_comment`.`user_id` = `users`.`id` WHERE `paper_id` = $paper_id";
		$result = mysql_query_excute($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$paper_comment_data[] = $row;
        }
        if( isset($paper_comment_data) ){
			return $paper_comment_data;
		}else{
			return array();
		}
	}

	function following($userid){
	$users = array();
	$sql = "SELECT DISTINCT `user_id` FROM `following` WHERE `follower_id` = '$userid'";
	$result = mysql_query_excute($sql);

	while($data = mysql_fetch_object($result)){
		array_push($users, $data->user_id);
	}

	return $users;

}
}
