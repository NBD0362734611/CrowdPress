<?php
class user extends model {
	function create( $email, $password, $display_name, $first_name, $last_name, $photo_url){
		$sql = "INSERT INTO users ( email, password, display_name, first_name, last_name, photo_url, created_at ) VALUES ( '$email', '$password', '$display_name', '$first_name', '$last_name', '$photo_url', NOW() ) ";

		mysql_query_excute($sql);

		return mysql_insert_id();
	}

	function update( $user_id, $email, $password, $first_name, $last_name ){
		$sql = "UPDATE users SET email = '$email', password = '$password', first_name = '$first_name', last_name = '$last_name' WHERE id = '$user_id' LIMIT 1";

		return mysql_query_excute($sql);
	}

	function update_mypage( $user_id, $display_name, $upapername, $paper_explain, $facebook_url, $twitter_url, $website_url, $photo_url, $cover_url ){
		$sql = "UPDATE `users` SET `display_name` = '$display_name', `upapername` = '$upapername', `paper_explain` = '$paper_explain', `facebook_url` = '$facebook_url', `twitter_url` = '$twitter_url', `website_url` = '$website_url', `photo_url` = '$photo_url', `cover_url` = '$cover_url' WHERE id = '$user_id' LIMIT 1";

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

    function find_by_keyword ( $words ){
        $user_ids = array();
        //空白文字で検索ワードを分割
        $word_array = preg_split("/[ ]+/",$words);
        $select = "SELECT `id` FROM `users`";
        $where = " WHERE ";
        for( $i = 0; $i <count($word_array); $i++ ){
            $where .= "( (`display_name` LIKE '%$word_array[$i]%') OR";
            $where .= "(`upapername` LIKE '%$word_array[$i]%') OR";
            $where .= "(`paper_explain` LIKE '%$word_array[$i]%') )";
            if ($i < count($word_array) - 1){
                $where .= " AND ";
            }
        }
        $sql = $select.$where;
        $sql .= "ORDER BY `id` DESC";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_object($result)) {
            array_push( $user_ids, $row->id );
        }
        return $user_ids;
    }

	function find_by_ids ( $ids = 0 ){
		$user_string = implode("," , $ids );
		$sql = "SELECT * FROM `users` WHERE `id` in ( $user_string ) ORDER BY FIELD (`id`, $user_string )";
		$result = mysql_query_excute($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$user_datas[] = $row;
        }
        return $user_datas;
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

	function release_comment_insert( $reply, $rid, $user_id, $comment ){
		$sql = "INSERT INTO `r_comment`(`reply`, `rid`, `user_id`, `comment`) VALUES ('$reply', '$rid', '$user_id', '$comment')";

		return mysql_query_excute($sql);
	}

    function release_comment_remove( $commentid, $rid, $user_id ){
        $sql = "DELETE FROM `r_comment` WHERE ( `commentid` = '$commentid' ) AND ( `user_id` = '$user_id' )";

        return mysql_query_excute($sql);
    }

	function paper_comment_insert($reply, $paper_id, $user_id, $comment){
		$sql = "INSERT INTO `p_comment`(`reply`, `paper_id`, `user_id`, `comment`) VALUES ('$reply', '$paper_id', '$user_id', '$comment')";
        mysql_query_excute($sql);
		return mysql_insert_id();
	}

    function request_comment_insert( $user_id, $comment ){
        $sql = "INSERT INTO `request`(`user_id`, `comment`) VALUES ('$user_id', '$comment')";

        return mysql_query_excute($sql);
    }

    function request_comment_remove( $request_id ,$user_id ){
        $sql = "DELETE FROM `request` WHERE ( `id` = '$request_id' ) AND ( `user_id` = '$user_id' )";

        return mysql_query_excute($sql);
    }

    function paperid_to_userid( $paper_id ){
        $sql = "SELECT `user_id` FROM `paper` WHERE `id` = '$paper_id' LIMIT 1";
        $result = mysql_query_excute($sql);
        $user_id = mysql_fetch_assoc($result);
        return $user_id["user_id"];
    }

    function paper_comment_notice($paper_id, $notice_user_id, $user_id, $kind, $kind_id, $flag){
        $sql = "SELECT * FROM `notice` WHERE `kind` = '$kind' AND `kind_id` = '$kind_id'";
        $result = mysql_query_excute($sql);
        if ($flag) { //flag=1 insert
            if (!mysql_num_rows($result)) {
                $sql = "INSERT INTO `notice`(`paper_id`, `notice_user_id`, `user_id`, `kind`, `kind_id`) VALUES ('$paper_id', '$notice_user_id', '$user_id', '$kind', $kind_id)";
                mysql_query_excute($sql);
            }
        }else{//flag=0 delate
            if (mysql_num_rows($result)) {
                $sql = "DELETE FROM `notice` WHERE `kind` = '$kind' AND `kind_id` = '$kind_id'";
                mysql_query_excute($sql);
            }
        }
    }

    function paper_comment_remove( $commentid, $pid, $user_id ){
        $sql = "DELETE FROM `p_comment` WHERE ( `id` = '$commentid' ) AND ( `user_id` = '$user_id' )";

        return mysql_query_excute($sql);
    }

	function release_comment_select($rid, $user_id=0){
		$release_comment_data = array();
		$sql = "SELECT `users`.`id`, `comment`, `display_name`, `photo_url` FROM `r_comment` INNER JOIN `users` ON `r_comment`.`user_id` = `users`.`id` WHERE `rid` = $rid ORDER BY `time` ASC";
		$result = mysql_query_excute($sql);
		while ( $row = mysql_fetch_assoc($result) ) {
			$release_comment_data[] = $row;
        }
        if( isset($release_comment_data) ){
			return $release_comment_data;
		}else{
			return array();
		}
	}

    function paper_remove( $paper_id, $user_id ){
        //新聞削除
        $sql = "DELETE FROM `paper` WHERE ( `id` = '$paper_id' ) AND ( `user_id` = '$user_id' )";
        mysql_query_excute($sql);
        //新聞のscrapから削除した新聞のカラムを全部削除
        $sql = "DELETE FROM `p_scrap` WHERE `paper_id` = '$paper_id'";
        mysql_query_excute($sql);
    }

    function user_id_by_scrap_paper( $paper_id ){
        //その新聞をスクラップしていたユーザー全員取得
        $sql = "SELECT `user_id` FROM `p_scrap` WHERE `paper_id` = '$paper_id'";
        $result = mysql_query_excute($sql);
        if ( mysql_num_rows( $result ) ) {
            while ( $row = mysql_fetch_array($result) ) {
             $user_id_array[] = $row["user_id"];
            }
            return $user_id_array;
        } else {
            return 0;
        }
    }

	function paper_comment_select($paper_id){
		$paper_comment_data = array();
		$sql = "SELECT `users`.`id`, `comment`, `display_name`, `photo_url` FROM `p_comment` INNER JOIN `users` ON `p_comment`.`user_id` = `users`.`id` WHERE `paper_id` = '$paper_id' ORDER BY `time`";
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

	function latest_publish_comment_select($rid, $user_id){
		$sql = "SELECT * FROM `publish` WHERE `rid` = '$rid' AND `user_id` = '$user_id' ORDER BY `publish`.`created_at` DESC LIMIT 1";
		$result = mysql_query_excute($sql);
		return mysql_fetch_assoc($result);
	}

	// function following($userid){
	// $users = array();
	// $sql = "SELECT DISTINCT `user_id` FROM `following` WHERE `follower_id` = '$userid'";
	// $result = mysql_query_excute($sql);

	// while($data = mysql_fetch_object($result)){
	// 	array_push($users, $data->user_id);
	// }

	// return $users;
	// }

	function follow ($user_id, $follower_id){
        $exsist = mysql_query("SELECT * FROM `following` WHERE `user_id` = '$user_id' AND `follower_id` = '$follower_id'");
        $row = mysql_num_rows($exsist);
         if ($row){
        	$follow_status = "購読解除";
        } else {
        	$follow_status = "購読する";
        }
        if ($user_id == $follower_id) {
        	$follow_status = null;
        }
        return $follow_status;
    }

	function following ($user_id, $follower_id){
        $exsist = mysql_query("SELECT * FROM `following` WHERE `user_id` = '$user_id' AND `follower_id` = '$follower_id'");
        $row = mysql_num_rows($exsist);
        if ($row){
        	$follow_status = "購読する";
            $deleate = "DELETE FROM `following` WHERE `user_id` = '$user_id' AND `follower_id` = '$follower_id'";
            mysql_query_excute($deleate);
        } else {
        	$follow_status = "購読解除";
            $insert = "INSERT INTO `following`(`user_id`, `follower_id`) VALUES ($user_id, $follower_id)";
            mysql_query_excute($insert);
        }
        //followの数
        $sql = "SELECT count(`user_id`) AS `follow` FROM `following` where `follower_id` = $follower_id";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `users` SET `follow` = $count[0] WHERE `id` = '$follower_id'";
        mysql_query_excute($sql);
        //followerの数
        $sql = "SELECT count(`follower_id`) AS `follower` FROM `following` where `user_id` = $user_id";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `users` SET `follower` = $count[0] WHERE `id` = '$user_id'";
        mysql_query_excute($sql);
        $count[] = $follow_status;
        return $count;
    }

	function paper_scrap_number_update($user_id){
		$sql = "SELECT count(`user_id`) AS `scrap` FROM `p_scrap` WHERE `user_id` = '$user_id'";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `users` SET `scrap` = $count[0] WHERE `id` = '$user_id'";
        mysql_query_excute($sql);
        return $count;
	}

    function get_latest_users( $user_id ){
        $users = array();
        $sql = "SELECT DISTINCT `id` AS `user_id` FROM `users` WHERE `id` <> '$user_id' ORDER BY `id` DESC LIMIT 5";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_object($result)) {
            array_push($users, $row->user_id);
        }
        return $users;
    }

	function get_user_following ( $user_id ){
		$following = array();
		$sql = "SELECT DISTINCT `user_id` FROM `following` WHERE `follower_id` = '$user_id' ORDER BY `following`.`created_at` DESC";
		$result = mysql_query_excute($sql);
		while ($row = mysql_fetch_object($result)) {
			array_push($following, $row->user_id);
        }
        return $following;
	}

	function get_user_follower ( $user_id ){
		$follower = array();
		$sql = "SELECT DISTINCT `follower_id` FROM `following` WHERE `user_id` = '$user_id' ORDER BY `following`.`created_at` DESC";
		$result = mysql_query_excute($sql);
		while ($row = mysql_fetch_object($result)) {
			array_push($follower, $row->follower_id);
        }
        return $follower;
	}

    function notification ( $user_id ){
        $notifications = array();
        $sql = "SELECT * FROM `notice` WHERE `notice_user_id` = '$user_id' ORDER BY `time` DESC";
        $result = mysql_query_excute($sql);
        if ( mysql_num_rows( $result )) {
            while ( $row = mysql_fetch_assoc($result) ) {
                $notification = array( $this->paperid_to_papercount($row["paper_id"]), $this->userid_to_displayname($row["user_id"]), $this->kind_to_string($row["kind"]), $row["paper_id"], $row["time"] );
                $notifications[] = $notification;
            }
        }
        return $notifications;
    }

    function kind_to_string( $kind ){
        if ($kind == 1) {
            $string = "クラップ";
        } elseif  ($kind == 2 ) {
            $string = "スクラップ";
        } else {
            $string = "コメント";
        }
        return $string;
    }

    function paperid_to_papercount( $paper_id ){
        $sql = "SELECT `count` FROM `paper` WHERE `id` = '$paper_id' LIMIT 1";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_assoc($result);
        return $count["count"];
    }

    function userid_to_displayname( $user_id ){
        $sql = "SELECT `display_name` FROM `users` WHERE `id` = '$user_id' LIMIT 1";
        $result = mysql_query_excute($sql);
        $display_name = mysql_fetch_assoc($result);
        return $display_name["display_name"];
    }

    function already_read( $user_id ){
        $sql = "DELETE FROM `notice` WHERE `notice_user_id` = '$user_id'";
        $result = mysql_query_excute($sql);
    }

    function insert_request_like( $request_id ){
        $sql = "UPDATE `request` SET `like` = `like` + 1 WHERE `id` = '$request_id'";
        $result = mysql_query_excute($sql);
        $sql = "SELECT `like` FROM `request` WHERE `id` = '$request_id'";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function get_request_data(){
        $request_data = array();
        $sql = "SELECT * FROM `users` INNER JOIN `request` ON `users`.id = `request`.`user_id` ORDER BY `request`.`id` DESC";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $request_data[] = $row;
        }
        return $request_data;
    }

   function get_clap_user_list($rid){
        $clap_user_list = array();
        $sql = "SELECT * FROM `r_clap` WHERE `rid` = '$rid'";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_object($result) ) {
            array_push($clap_user_list, $this->find_by_id($row->user_id));
        }
        return $clap_user_list;
    }

    function get_scrap_user_list($rid){
        $scrap_user_list = array();
        $sql = "SELECT * FROM `r_scrap` WHERE `rid` = '$rid'";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_object($result) ) {
            $scrap_user_list[] = $this->find_by_id($row->user_id);
        }
        return $scrap_user_list;
    }
}
