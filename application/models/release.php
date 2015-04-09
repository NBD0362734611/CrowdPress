<?php
class release extends model {
	function get_new_release (){
		$sql = "SELECT * FROM `release` ORDER BY `rid` DESC LIMIT 50";
		$result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_data[] = $row;
        }
		return $release_data;
	}

    function find_release_by_cname( $cname ){
        $sql = "SELECT * FROM `release` WHERE `cname` LIKE '%$cname%' ORDER BY `rid` DESC LIMIT 50";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_data[] = $row;
        }
        return $release_data;
    }

    function get_release_detail ($rid){
        $sql = "SELECT * FROM `release` where `rid` = $rid LIMIT 1";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function get_user_scrap ($user_id){
        // $sql = "SELECT `rid`, `headline`, `comment` FROM `publish` WHERE `user_id` = '$user_id'";
        // $result = mysql_query_excute($sql);
        // while ($row = mysql_fetch_assoc($result)) {
        //     $rid = $row["rid"];
        //     $publish_data[$rid] = $row;
        // }
        $sql = "SELECT `release`.`title`, `release`.`rid`,`release`.`cname`, `release`.`img1`, `release`.`img2`, `release`.`img3`, `release`.`clap`, `release`.`scrap`, `release`.`time`, `headline`, `comment` FROM `release` INNER JOIN `r_scrap` ON `release`.`rid` = `r_scrap`.`rid` LEFT JOIN `publish` ON `release`.`rid` = `publish`.`rid`  WHERE `r_scrap`.`user_id` = $user_id ORDER BY `release`.`rid` DESC LIMIT 50";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $scrap_data[] = $row;
        }
        if ( isset($scrap_data) ) {
            return $scrap_data;
        } else {
            return array();
        }
    }

    function find_scrap_by_cname ($user_id, $cname){
        $sql = "SELECT `release`.`title`, `release`.`rid`,`release`.`cname`, `release`.`img1`, `release`.`img2`, `release`.`img3`, `release`.`clap`, `release`.`scrap`, `release`.`time`, `headline`, `comment` FROM `release` INNER JOIN `r_scrap` ON `release`.`rid` = `r_scrap`.`rid` LEFT JOIN `publish` ON `release`.`rid` = `publish`.`rid`  WHERE `r_scrap`.`user_id` = $user_id AND `release`.`cname` LIKE '%$cname%' ORDER BY `release`.`rid` DESC LIMIT 50";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $scrap_data[] = $row;
        }
        if ( isset($scrap_data) ) {
            return $scrap_data;
        } else {
            return array();
        }
    }

    function clap_insert ($user_id, $rid){
        $exsist = mysql_query("SELECT * FROM `r_clap` WHERE `user_id` = $user_id AND `rid` = $rid");
        $row = mysql_num_rows($exsist);
        if ($row){
            $deleate = "DELETE FROM `r_clap` WHERE `user_id` = $user_id and `rid` = $rid";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `clap` = `clap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $insert = "INSERT INTO `r_clap`(`user_id`, `rid`) VALUES ($user_id, $rid)";
            mysql_query_excute($insert);
            // $sql = "UPDATE `release` SET `clap` = `clap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        }
        $sql = "SELECT count(*) AS `clap` FROM `r_clap` where `rid` = $rid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `release` SET `clap` = $count[0] WHERE `rid` = $rid";
        mysql_query_excute($sql);
        return $count;
    }

    function scrap_insert ($user_id, $rid){
        $exsist = mysql_query("SELECT * FROM `r_scrap` WHERE `user_id` = $user_id AND `rid` = $rid");
        $row = mysql_num_rows($exsist);
        if ($row){
            $deleate = "DELETE FROM `r_scrap` WHERE `user_id` = $user_id and `rid` = $rid";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `scrap` = `scrap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $insert = "INSERT INTO `r_scrap`(`user_id`, `rid`) VALUES ($user_id, $rid)";
            mysql_query_excute($insert);
            // $sql = "UPDATE `release` SET `scrap` = `scrap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        }
        $sql = "SELECT count(*) AS `scrap` FROM `r_scrap` where `rid` = $rid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `release` SET `scrap` = $count[0] WHERE `rid` = $rid";
        mysql_query_excute($sql);
        return $count;
    }

    function paper_clap_insert ($user_id, $pid){
        $exsist = mysql_query("SELECT * FROM `p_clap` WHERE `user_id` = $user_id AND `paper_id` = $pid");
        $row = mysql_num_rows($exsist);
        if ($row){
            $deleate = "DELETE FROM `p_clap` WHERE `user_id` = $user_id and `paper_id` = $pid";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `clap` = `clap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $insert = "INSERT INTO `p_clap`(`user_id`, `paper_id`) VALUES ($user_id, $pid)";
            mysql_query_excute($insert);
            // $sql = "UPDATE `release` SET `clap` = `clap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        }
        $sql = "SELECT count(*) AS `clap` FROM `p_clap` where `paper_id` = $pid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `paper` SET `clap` = $count[0] WHERE `id` = $pid";
        mysql_query_excute($sql);
        return $count;
    }

    function paper_scrap_insert ($user_id, $pid){
        $exsist = mysql_query("SELECT * FROM `p_scrap` WHERE `user_id` = $user_id AND `paper_id` = $pid");
        $row = mysql_num_rows($exsist);
        if ($row){
            $deleate = "DELETE FROM `p_scrap` WHERE `user_id` = $user_id and `paper_id` = $pid";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `scrap` = `scrap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $insert = "INSERT INTO `p_scrap`(`user_id`, `paper_id`) VALUES ($user_id, $pid)";
            mysql_query_excute($insert);
            // $sql = "UPDATE `release` SET `scrap` = `scrap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        }
        $sql = "SELECT count(`paper_id`) AS `scrap` FROM `p_scrap` where `paper_id` = $pid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `paper` SET `scrap` = $count[0] WHERE `id` = $pid";
        mysql_query_excute($sql);
        return $count;
    }


    function scrap_paper_comment($user_id, $rid, $headline, $comment){
        $sql = "REPLACE INTO `publish`(`user_id`, `rid`, `headline`, `comment`) VALUES ('$user_id', '$rid', '$headline', '$comment')";
        $result = mysql_query_excute($sql);
        return "OK";
    }

    function release_comment_ridselect($rid){
        $release_comment_data = array();
        $sql = "SELECT `r_comment`.`reply`, `r_comment`.`comment`, `users`.`display_name`, `users`.`photo_url`, `r_comment`.`time` FROM `r_comment` INNER JOIN `users` ON `r_comment`.`user_id` = `users`.`id` WHERE `rid` = $rid";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_comment_data[] = $row;
        }
        return $release_comment_data;
    }

    function paper_comment_paper_id_select($paper_id){
        $release_comment_data = array();
        $sql = "SELECT `p_comment`.`comment`, `users`.`display_name`, `users`.`photo_url`, `p_comment`.`time` FROM `p_comment` INNER JOIN `users` ON `p_comment`.`user_id` = `users`.`id` WHERE `paper_id` = $paper_id";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_comment_data[] = $row;
        }
        return $release_comment_data;
    }

    function get_release_comment_number($rid){
        $sql = "SELECT COUNT(`comment`) AS `number` FROM `r_comment` WHERE `rid` = $rid";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function get_paper_comment_number($paper_id){
        $sql = "SELECT COUNT(`comment`) AS `number` FROM `p_comment` WHERE `paper_id` = $paper_id";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function publish_id_insert_paper($user_id, $checked_rid){
        $i=1;
        foreach ($checked_rid as $rid) {
            $sql = "SELECT `id` FROM `publish` WHERE `user_id` = $user_id AND `rid` = $rid";
            $result = mysql_query_excute($sql);
            $id = mysql_fetch_array($result);
            ${"publish_id_".$i} = $id[0];
            $i++;
        }

        $keys =  "publish_id_1";
        $values = $publish_id_1;
        for ($i=2; $i <= sizeof($checked_rid); $i++) {
            $keys .=  ","."publish_id_$i";
            $values.= ",".${"publish_id_".$i};
        }

        $sql = "UPDATE `users` SET `publish_paper` = `publish_paper` + 1 WHERE `id` = '$user_id'";
        mysql_query_excute($sql);
        $sql = "SELECT `publish_paper` FROM `users` WHERE `id` = '$user_id'";
        $publish_paper_count = mysql_fetch_array( mysql_query_excute($sql) );
        $sql = "INSERT INTO `paper`(`user_id`, $keys, `count`) VALUES('$user_id', $values, $publish_paper_count[0])";
        mysql_query_excute($sql);
        $sql = "SELECT `id` FROM `paper` ORDER BY `paper`.`id` DESC LIMIT 1";
        $result = mysql_query_excute($sql);
        return mysql_fetch_array($result);
    }

//ひとつだけ
    function find_paper_by_id( $id ){
        $sql = "SELECT * FROM `paper` WHERE id = '$id' LIMIT 1";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

//ひとつだけ
    function find_publish_by_id( $id ){
        $sql = "SELECT `headline`, `comment`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$id' LIMIT 1";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function find_publish_by_user_id( $user_id ){
        $publish_info_data = array();
        $sql = "SELECT `id`, `publish_id_1`, `count`, `clap`, `scrap`, `created_at` FROM `paper` WHERE `user_id` = '$user_id' ORDER BY `id` DESC";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }

        return $publish_info_data;
    }

    function find_publish_by_follow(){
        $paper_info_data = array();
        $publish_info_data = array();

        $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `publish_id_1`, `count`, `clap`, `paper`.`scrap`, `upapername`, `display_name`, `photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id` ORDER BY `paper`.`id` DESC";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset ($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }

        return $publish_info_data;
    }

}
