<?php

class notice extends controller {

	function comment_notification()
	{
		$release = $this->loadModel( "release" );
		$user = $this->loadModel( "user" );
		$unread_comment = $release->unread_paper_comment();
	}

	function clap_notification()
	{
		$release = $this->loadModel( "release" );
		$user = $this->loadModel( "user" );
	}

	function scrap_notification()
	{
		$release = $this->loadModel( "release" );
		$user = $this->loadModel( "user" );
	}

	function already_read()
	{
		$uri = $_SERVER['HTTP_REFERER'];
		$release = $this->loadModel( "release" );
		$user = $this->loadModel( "user" );
		$user->already_read($_SESSION["user"]);
		//post元に戻る
        header("Location: ".$uri);
	}

	/**
	 * データを取得
	 */
	function getData() {
		$user = $this->loadModel( "user" );
		$notifications = $user->notification( $_SESSION["user"] );

	    return  $notifications;
	}

	/**
	 * 更新チェック
	 *
	 * 対象データに変化が無ければループし続ける。
	 * 変化が有れば新しいデータ追加した全てのデータを返す。
	 */
	function getUpdatedData() {
	    $data = $this->getData();
	    $temp = $data;
	    while ($temp === $data) {
	        $temp = $this->getData();
	        sleep(1);
	        session_write_close();
	    }

	    $html = "";

	    if ($temp) {
			$html .= '<li><a href="?route=notice/already_read">既読にする</a></li>';
		} else {
			echo $html;
			return $temp;
		}

        foreach ($temp as $notification) {
			$html .= '<li><a href="?route=pages/display_paper/';
			$html .= $notification[3];
			$html .= '">';
			$html .= $notification[1];
			$html .= "が";
			$html .= $notification[0];
			$html .= '号に';
			$html .= $notification[2];
			$html .= 'しました';
			$html .= '</a></li>';
        }
	    echo $html;
	    return $temp;
	}

	/**
	 * データ追加
	 *
	 * 新しいデータを追加して全てのデータを返す。
	 */
	// function pushData($data) {
	//     if (!empty($data)) {
	//         $data = str_replace(array("\n", "\r"), '', $data)
	//                 . ' [' . date('c') . ']' . PHP_EOL;
	//         file_put_contents(DATA_FILE, $data, FILE_APPEND|LOCK_EX);
	//     }
	//     return getData();
	// }

	// if (isset($_GET['mode'])) {
	//     // モードの振り分け
	//     switch ($_GET['mode']) {
	//         // データを取得
	//         case 'view':
	//             $data = getData();
	//             break;

	//         // 更新チェック
	//         case 'check':
	//             $data = getUpdatedData();
	//             break;

	//         // データを保存
	//         // case 'add':
	//         //     $data = pushData($_POST['data']);
	//         //     break;
	//     }

	//     // 結果を表示
	//     echo nl2br(htmlspecialchars($data, ENT_QUOTES));
	// }

}
