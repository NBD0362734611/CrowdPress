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

}
