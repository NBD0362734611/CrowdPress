<?php
class release extends model {

    function get_new_release ( $start=0, $prcid=0, $sort=0){
        if ($prcid) {
            $source = "";
            for ($i=0; $i < count($prcid) ; $i++) {
                $source .= "prcid =";
                $source .=  $prcid[$i];
                if ($i < count($prcid) - 1) {
                    $source .= " OR ";
                }
            }
            switch ($sort) {
                case 1:
                    $sql = "SELECT * FROM `release` WHERE $source ORDER BY `clap` DESC LIMIT $start, 50";
                    break;
                case 2:
                    $sql = "SELECT * FROM `release` WHERE $source ORDER BY `scrap` DESC LIMIT $start, 50";
                    break;
                case 3:
                    $sql = "SELECT * FROM `release` WHERE $source ORDER BY `comment` DESC LIMIT $start, 50";
                    break;
                default:
                    $sql = "SELECT * FROM `release` WHERE $source ORDER BY `time` DESC LIMIT $start, 50";
                    break;
            }
        } else {
            switch ($sort) {
                case 1:
                    $sql = "SELECT * FROM `release` ORDER BY `clap` DESC LIMIT $start, 50";
                    break;
                case 2:
                    $sql = "SELECT * FROM `release` ORDER BY `scrap` DESC LIMIT $start, 50";
                    break;
                case 3:
                    $sql = "SELECT * FROM `release` ORDER BY `comment` DESC LIMIT $start, 50";
                    break;
                default:
                    $sql = "SELECT * FROM `release` ORDER BY `time` DESC LIMIT $start, 50";
                    break;
            }
        }
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_data[] = $row;
        }
        if (isset($release_data)) {
            return $release_data;
        } else {
            return array();
        }
    }

    function prev_next_rid ( $rid ){
        $prev_next_rid = array();
        $sql = "SELECT `rid` FROM `release` WHERE `rid` > '$rid' LIMIT 1";
        $result = mysql_query_excute($sql);
        $row = mysql_fetch_array($result);
        $prev_next_rid["next"] = $row["rid"];
        $sql = "SELECT `rid` FROM `release` WHERE `rid` < '$rid' ORDER BY `time` DESC LIMIT 1";
        $result = mysql_query_excute($sql);
        $row = mysql_fetch_array($result);
        $prev_next_rid["prev"] = $row["rid"];
        return $prev_next_rid;
    }

    function find_release_by_cname( $cname ){
        $sql = "SELECT * FROM `release` WHERE `cname` LIKE '%$cname%' ORDER BY `time` DESC LIMIT 50";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_data[] = $row;
        }
        if (isset($release_data)) {
            return $release_data;
        } else {
            return array();
        }    }

    function find_release_by_title( $words ){
        //空白文字で検索ワードを分割
        $word_array = preg_split("/[ ]+/",$words);
        $select ="SELECT * FROM `release`";
        $where = " WHERE ";

        for( $i = 0; $i <count($word_array); $i++ ){
            $where .= "(`title` LIKE '%$word_array[$i]%')";

            if ($i < count($word_array) - 1){
                $where .= " AND ";
            }
        }

        $sql = $select.$where;
        $sql .= " ORDER BY `time` DESC LIMIT 50";

        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_data[] = $row;
        }
        if (isset($release_data)) {
            return $release_data;
        } else {
            return array();
        }
    }

    function get_release_detail ($rid){
        $sql = "SELECT * FROM `release` where `rid` = $rid LIMIT 1";
        $result = mysql_query_excute($sql);
        if (isset($result)) {
            return mysql_fetch_assoc($result);
        } else {
            array();
        }
    }

    function get_user_scrap ($user_id, $start=0){
        $sql = "SELECT `release`.`title`, `release`.`rid`,`release`.`cname`, `release`.`img1`, `release`.`img2`, `release`.`img3`, `release`.`clap`, `release`.`scrap`, `release`.`time` FROM `release` INNER JOIN `r_scrap` ON `release`.`rid` = `r_scrap`.`rid` WHERE `r_scrap`.`user_id` = $user_id ORDER BY `r_scrap`.`time` DESC LIMIT $start, 50";
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

    function find_scrap_by_title ($user_id, $words){
        //空白文字で検索ワードを分割
        $word_array = preg_split("/[ ]+/",$words);
        $select ="SELECT `release`.`title`, `release`.`rid`,`release`.`cname`, `release`.`img1`, `release`.`img2`, `release`.`img3`, `release`.`clap`, `release`.`scrap`, `release`.`time`, `headline`, `comment` FROM `release` INNER JOIN `r_scrap` ON `release`.`rid` = `r_scrap`.`rid` LEFT JOIN `publish` ON `release`.`rid` = `publish`.`rid`";
        $where = " WHERE ";

        for( $i = 0; $i <count($word_array); $i++ ){
            $where .= "(`title` LIKE '%$word_array[$i]%')";

            if ($i < count($word_array) - 1){
                $where .= " AND ";
            }
        }

        $sql = $select.$where;
        $sql .= " ORDER BY `time` DESC LIMIT 50";
        // $sql = "SELECT `release`.`title`, `release`.`rid`,`release`.`cname`, `release`.`img1`, `release`.`img2`, `release`.`img3`, `release`.`clap`, `release`.`scrap`, `release`.`time`, `headline`, `comment` FROM `release` INNER JOIN `r_scrap` ON `release`.`rid` = `r_scrap`.`rid` LEFT JOIN `publish` ON `release`.`rid` = `publish`.`rid`  WHERE `r_scrap`.`user_id` = $user_id AND `release`.`title` LIKE '%$title%' ORDER BY `release`.`rid` DESC LIMIT 50";
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
        $exsist = mysql_query_excute("SELECT `id` FROM `p_clap` WHERE `user_id` = $user_id AND `paper_id` = $pid");
        $kind_id = mysql_fetch_assoc($exsist);
        $kind_id = $kind_id["id"];
        $row = mysql_num_rows($exsist);
        if ($row){
            $flag = 0;
            $deleate = "DELETE FROM `p_clap` WHERE `user_id` = $user_id and `paper_id` = $pid";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `clap` = `clap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $flag = 1;
            $insert = "INSERT INTO `p_clap`(`user_id`, `paper_id`) VALUES ($user_id, $pid)";
            mysql_query_excute($insert);
            // $sql = "UPDATE `release` SET `clap` = `clap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
            $kind_id = mysql_insert_id();
        }
        $sql = "SELECT count(*) AS `clap` FROM `p_clap` where `paper_id` = $pid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `paper` SET `clap` = $count[0] WHERE `id` = $pid";
        mysql_query_excute($sql);
        return array($count, $kind_id, $flag);
    }

    function paper_scrap_insert ($user_id, $pid){
        $exsist = mysql_query_excute("SELECT * FROM `p_scrap` WHERE `user_id` = '$user_id' AND `paper_id` = '$pid'");
        $kind_id = mysql_fetch_assoc($exsist);
        $kind_id = $kind_id["id"];
        $row = mysql_num_rows($exsist);
        if ($row){
            $flag = 0;
            $deleate = "DELETE FROM `p_scrap` WHERE `user_id` = '$user_id' and `paper_id` = '$pid'";
            mysql_query_excute($deleate);
            // $sql = "UPDATE `release` SET `scrap` = `scrap` - 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        } else {
            $flag = 1;
            $insert = "INSERT INTO `p_scrap`(`user_id`, `paper_id`) VALUES ('$user_id', '$pid')";
            mysql_query_excute($insert);
            $kind_id = mysql_insert_id();
            // $sql = "UPDATE `release` SET `scrap` = `scrap` + 1 WHERE `rid` = $rid";
            // mysql_query_excute($sql);
        }
        $sql = "SELECT count(`paper_id`) AS `scrap` FROM `p_scrap` where `paper_id` = $pid";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);
        $sql = "UPDATE `paper` SET `scrap` = $count[0] WHERE `id` = $pid";
        mysql_query_excute($sql);
        return array($count, $kind_id ,$flag);
    }


    function scrap_paper_comment($user_id, $rid, $headline, $comment){
        $sql = "SELECT `id` FROM `publish` WHERE `user_id` = '$user_id' AND `rid` = '$rid' ORDER BY `id` DESC LIMIT 1";
        $result = mysql_query_excute($sql);
        // if ( mysql_num_rows($result) ) {
        //     $sql = "DELETE FROM `publish` WHERE `user_id` = '$user_id' AND `rid` = '$rid' ORDER BY `id` DESC LIMIT 1";
        //     $result = mysql_query_excute($sql);
        // }
        $sql = "INSERT INTO `publish`(`user_id`, `rid`, `headline`, `comment`) VALUES ('$user_id', '$rid', '$headline', '$comment')";
        $result = mysql_query_excute($sql);
        return "OK";
    }

    function release_comment_ridselect($rid){
        $release_comment_data = array();
        $sql = "SELECT `r_comment`.`commentid`, `r_comment`.`reply`, `r_comment`.`comment`, `users`.`id`, `users`.`display_name`, `users`.`photo_url`, `r_comment`.`time` FROM `r_comment` INNER JOIN `users` ON `r_comment`.`user_id` = `users`.`id` WHERE `rid` = $rid";
        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $release_comment_data[] = $row;
        }
        return $release_comment_data;
    }

    function paper_comment_paper_id_select($paper_id){
        $release_comment_data = array();
        $sql = "SELECT `p_comment`.`id` AS `commentid`, `p_comment`.`comment`, `users`.`display_name`, `users`.`id`, `users`.`photo_url`, `p_comment`.`time` FROM `p_comment` INNER JOIN `users` ON `p_comment`.`user_id` = `users`.`id` WHERE `paper_id` = $paper_id";
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

    function get_paper_publish_number( $user_id ){
        //新聞を発行した数を取得
        $sql = "SELECT count(`id`) AS `paper_count` FROM `paper` WHERE `user_id` = $user_id";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);

        //ユーザーの新聞パブリッシュ数を更新
        $sql = "UPDATE `users` SET `publish_paper` = '$count[0]' WHERE `id` = '$user_id'";
        mysql_query_excute($sql);

        return $count[0];
    }

    function publish_id_insert_paper($user_id, $checked_rid){
        //変数publish_id_iにpublishのid（ユニークキー）を代入
        $i=1;
        foreach ($checked_rid as $rid) {
            $sql = "SELECT `id` FROM `publish` WHERE `user_id` = $user_id AND `rid` = $rid ORDER BY `created_at` DESC LIMIT 1";
            $result = mysql_query_excute($sql);
            $id = mysql_fetch_array($result);
            ${"publish_id_".$i} = $id[0];
            if ( !$id[0] ) {
                return false;
            }
            $i++;
        }

        //SQL文を作成
        $keys =  "publish_id_1";
        $values = $publish_id_1;
        for ($i=2; $i <= sizeof($checked_rid); $i++) {
            $keys .=  ","."publish_id_$i";
            $values.= ",".${"publish_id_".$i};
        }

        //新聞発行前の新聞発行数をカウント(欠番あり)
        $sql = "SELECT `count` FROM `paper` WHERE `user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT 1";
        $count_before_publish = mysql_query_excute($sql);

        //新聞を挿入
        $sql = "INSERT INTO `paper`(`user_id`, $keys, `created_at`) VALUES('$user_id', $values, NOW() )";
        mysql_query_excute($sql);

        //新聞を発行した数を取得(欠番なし)
        $sql = "SELECT count(`id`) AS `paper_count` FROM `paper` WHERE `user_id` = $user_id";
        $result = mysql_query_excute($sql);
        $count = mysql_fetch_array($result);

        //ユーザーの新聞パブリッシュ数を更新
        $sql = "UPDATE `users` SET `publish_paper` = '$count[0]' WHERE `id` = '$user_id'";
        mysql_query_excute($sql);

        //新聞の号
        // $sql = "UPDATE `paper` SET `count` = '$count[0]' WHERE `user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT 1";
        // mysql_query_excute($sql);
        if ( mysql_num_rows( $count_before_publish ) ) {
            $count = mysql_fetch_array( $count_before_publish );
            $newcount = $count[0] + 1;
            $sql = "UPDATE `paper` SET `count` = '$newcount' WHERE `user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT 1";
            mysql_query_excute($sql);
        } else {
            $sql = "UPDATE `paper` SET `count` = 1 WHERE `user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT 1";
            mysql_query_excute($sql);
        }

        //publishした新聞のID（ユニーク）を返す
        $sql = "SELECT `id` FROM `paper` WHERE `user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT 1";
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
        $sql = "SELECT `headline`, `comment`, `title`, `release`.`rid`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$id' LIMIT 1";
        $result = mysql_query_excute($sql);
        return mysql_fetch_assoc($result);
    }

    function find_publish_by_user_id( $user_id, $start=0 ){
        $publish_info_data = array();
        $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `paper`.`publish_id_1`, `paper`.`count`, `paper`.`clap`, `paper`.`scrap`, `users`.`upapername`, `users`.`photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id`  WHERE `user_id` = '$user_id' ORDER BY `id` DESC LIMIT $start, 50";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `publish`.`comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }

        return $publish_info_data;
    }

    function find_publish_by_follow( $user_id, $start=0 ){
        $paper_info_data = array();
        $publish_info_data = array();

        $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `publish_id_1`, `count`, `clap`, `paper`.`scrap`, `upapername`, `display_name`, `photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id` WHERE `paper`.`user_id` IN ( SELECT `user_id` FROM `following` WHERE `follower_id` = '$user_id' ) OR `paper`.`user_id` = '$user_id' ORDER BY `paper`.`id` DESC LIMIT $start, 50";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset ($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `publish`.`comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }
        return $publish_info_data;
    }

    function find_publish_by_scrap( $user_id ){
        $paper_info_data = array();
        $publish_info_data = array();

        $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `publish_id_1`, `count`, `clap`, `paper`.`scrap`, `upapername`, `display_name`, `photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id` INNER JOIN `p_scrap` ON  `paper`.`id` = `p_scrap`.`paper_id` WHERE `p_scrap`.`user_id` = $user_id ORDER BY `p_scrap`.`time` DESC";
        $result = mysql_query_excute($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset ($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `publish`.`comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }
        return $publish_info_data;
    }

    function find_publish_by_keyword( $users_id, $words, $scope ) {
        $paper_info_data = array();
        $publish_info_data = array();
        $publish_ids = array();
        $user_string = implode("," , $users_id );

        //空白文字で検索ワードを分割
        $word_array = preg_split("/[ ]+/",$words);
        $select = "SELECT `id` FROM `publish`";
        $where = " WHERE ";
        for( $i = 0; $i <count($word_array); $i++ ){
            $where .= "( (`headline` LIKE '%$word_array[$i]%') OR";
            $where .= "(`comment` LIKE '%$word_array[$i]%') )";
            if ($i < count($word_array) - 1){
                $where .= " AND ";
            }
        }
        $sql = $select.$where;
        $sql .= "ORDER BY `id` DESC";

        $result = mysql_query_excute($sql);
        while ($row = mysql_fetch_object($result)) {
            array_push($publish_ids, $row->id );
        }
        $ids = implode("," , $publish_ids );

        if ( $ids ) {
            if ( $scope == 1 ) {
                $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `publish_id_1`, `count`, `clap`, `paper`.`scrap`, `upapername`, `display_name`, `photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id` WHERE `paper`.`user_id` IN ( $user_string ) AND (  ( `publish_id_1` IN ( $ids ) ) OR ( `publish_id_2` IN ( $ids ) ) OR ( `publish_id_3` IN ( $ids ) ) OR ( `publish_id_4` IN ( $ids ) ) OR ( `publish_id_5` IN ( $ids ) )  ) ORDER BY `paper`.`id` DESC";
            }else{
                $sql = "SELECT `paper`.`id`, `paper`.`user_id`, `publish_id_1`, `count`, `clap`, `paper`.`scrap`, `upapername`, `display_name`, `photo_url`, `paper`.`created_at` FROM `paper` INNER JOIN `users` ON `paper`.`user_id` = `users`.`id` WHERE (  ( `publish_id_1` IN ( $ids ) ) OR ( `publish_id_2` IN ( $ids ) ) OR ( `publish_id_3` IN ( $ids ) ) OR ( `publish_id_4` IN ( $ids ) ) OR ( `publish_id_5` IN ( $ids ) )  ) ORDER BY `paper`.`id` DESC";
            }
        $result = mysql_query_excute($sql);

        while ( $row = mysql_fetch_assoc($result) ) {
            $paper_info_data[] = $row;
        }

        if ( isset ($paper_info_data) ) {
            foreach ($paper_info_data as $paper_info) {
            $publish_id_1 = $paper_info["publish_id_1"];
            $sql = "SELECT `headline`, `publish`.`comment`, `release`.`rid`, `title`, `img1` FROM `publish` INNER JOIN `release` ON `publish`.`rid` = `release`.`rid` WHERE `id` = '$publish_id_1' LIMIT 1";
            $result = mysql_query_excute($sql);
            $row = mysql_fetch_assoc($result);
            $publish_info_data[] = array_merge($row, $paper_info);
            }
        }
        }else{
            $publish_info_data = array();
        }
        return $publish_info_data;
    }

}
