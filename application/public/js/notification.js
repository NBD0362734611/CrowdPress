jQuery(function($) {

  /**
   * データ取得
   */
  function getData() {
    $.post('?route=notice/getData', {}, function(data) {
      checkUpdate();
    });
  }

  /**
   * 更新チェック
   */
  function checkUpdate() {
    $.post('?route=notice/getUpdatedData', {}, function(data) {
        $('#notice').next().html("");
        if (data) {
            $("#notice").html('<i class="fa fa-bell-o"></i>');
            $("#notice").next().append(data);
        } else {
            $("#notice").html('<i class="fa fa-bell-slash"></i>');
            $("#notice").next().append('<li><a>通知はありません</a></li>');
        }
        checkUpdate();
    });
  }

  getData();
});
