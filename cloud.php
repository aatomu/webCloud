<script>
// Script written by Adam Khoury @ DevelopPHP.com
// Video Tutorial: http://www.youtube.com/watch?v=EraNFJiY0Eg
// Code By https://github.com/tariq-shuvo/fileupload-progressbar
function _(el){
	return document.getElementById(el);
}
function uploadFile(){
	var file = _("up_file").files[0];
	var formdata = new FormData();
	formdata.append("up_file", file);
    console.log(formdata);
	var ajax = new XMLHttpRequest();
	ajax.upload.addEventListener("progress", progressHandler, false);
  //Safariの場合progress非表示
  var ua = window.navigator.userAgent.toLowerCase();
  if (ua.indexOf('safari') !== -1 && ua.indexOf('chrome') === -1 && ua.indexOf('edge') === -1){
    _("progress").style.visibility ="hidden"
  } else {
    _("progress").style.visibility ="visible"
  }
  //キャンセルボタン表示
  _("cancel").style.visibility ="visible"
	ajax.open("POST", "");
	ajax.send(formdata);
}
function progressHandler(event){
	var percent = Math.round((event.loaded / event.total) * 100 * 100) / 100;
	_("Bar").style.width = percent+"%";
	_("Bar").innerHTML = percent+"%&nbsp;upload&nbsp;("+event.loaded.toLocaleString()+"/"+event.total.toLocaleString()+")byte";
}
</script>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Web Cloud</title>
    <style>
      body {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <!--メイン-->
    <?php
      $save_path = "./Your/Directory/PATH/";

      //アップロードのためにecho
      echo "<br><br>";
      echo "\n    <form method=\"post\" enctype=\"multipart/form-data\">";
      echo "\n      <font size=5>File:</font>";
      echo "\n      <input type=\"file\" name=\"up_file\" id=\"up_file\">";
      echo "\n      <font size=5>Pass:</font>";
      echo "\n      <input type=\"text\" name=\"up_pass\"><br>";
      echo "\n      <input type=\"submit\" name=\"up_send\" value=\"アップロード\" onclick=\"uploadFile()\"><br>";
      echo "\n      <input type=\"button\" value=\"キャンセル\" onclick=\"location.reload();\" id=\"cancel\" style=\"visibility :hidden;\"><br>";
      echo "\n      <div id=\"progress\" style=\"visibility :hidden;\">";
      echo "\n        Upload Progress <div style=\"width: 40%; height: 2em; margin: auto; border: solid 5px #4bc2c5; text-align: left; \">";
      echo "\n          <div id=\"Bar\" style=\"width:0%; height: 100%; background : #78fee0; padding: auto;\"></div>";
      echo "\n        </div><br>";
      echo "\n        <font id=\"state\"></font>";
      echo "\n      </div>";
      echo "\n    </form>";
      echo "\n    <br>";
      //postをcheck
      if (isset($_POST['up_send']) === true ) {
        //passとファイルのチェック
        if (isset($_FILES['up_file']) === true) {
          //ファイルサイズを保存
          $file_size = $_FILES["up_file"]["size"];
          if ($_FILES["up_file"]["error"] > 0) {
            echo "ファイルアップロードに失敗しました。<br>";
          } else {
            //日付を保存
            $date = date("Y.m.d,H_i_s");
            //ファイル名(pass無し)を保存
            $file_name = ($date.",".$_FILES["up_file"]["name"]);
            //名前から特殊文字を消す
            if ( preg_match("|__|",$file_name) != "" ) {
              $file_name = preg_replace("|__|",".",$file_name);
              echo ("change file name to ".$file_name."<br>");
            }
            //一時ファイルとpassを入手
            $file_tmp = $_FILES["up_file"]["tmp_name"];
            $file_pass = $_POST['up_pass'];
            //アップロードに成功するか確認
            if (is_uploaded_file($file_tmp)) {
              //一時ファイルから通常ファイルに変更
              if ( move_uploaded_file($file_tmp , $save_path.$file_name."__".$file_pass)) {
                //終了メッセージ
                echo ($file_name."をアップロードしました。<br>upload to server : ".$file_name." , Size : ".$file_size."byte password : \"".$file_pass."\"<br>");
                sleep(1);
                ob_end_clean();
                $url = ((empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
                header("Location: ".$url);
                exit;    
              } else {
                //失敗メッセージ
                echo "ファイルをアップロードできません。";
              }
            }
          }
        } else {
          //ファイルがおかしいときに
          echo "ファイルが設定されていません<br><br>";
        }
      }

      //DownLoad Function
      function Download() {
        global $save_path;
        global $send_pass;
        global $send_file;
        //ファイル名保存
        $file_path = $save_path.$send_file."__".$send_pass;
        //postからpassを確認
        if (file_exists($file_path)) {
          // ファイルタイプを指定
          header('Content-Type: application/force-download');
          // ファイルサイズを取得し、ダウンロードの進捗を表示
          header('Content-Length: '.filesize($file_path));
          // ファイルのダウンロード、リネームを指示
          header('Content-Disposition: attachment; filename="'.$send_file.'"');
          // ファイルを読み込みダウンロードを実行
          ob_end_clean();
          readfile($file_path);
        } else {
          //パスワードチェック
          echo "ファイル名 か パスワード が 間違っています<br><br>";
        }
      }
      //postチェック
      if (isset($_POST['down_send']) === true && isset($_POST['down_file']) === true) {
        if (isset($_POST['delete']) === true) {
          $send_pass = $_POST['down_pass'];
          $send_file = $_POST['down_file'];
          $check = shell_exec("ls -1 ".$save_path." | grep \"".$send_file."__".$send_pass."\"");
          $check = preg_replace("|\n|","",$check);
          if ( $check === $send_file."__".$send_pass) {
            shell_exec("mv \"".$save_path.$send_file."__".$send_pass."\" ".$save_path."trash/");
            ob_end_clean();
            $url = ((empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
            header("Location: ".$url);
            exit;
          } else {
            echo "パスワードが間違っています";
          }
        } else {
          ob_end_clean();
          $send_pass=$_POST['down_pass'];
          $send_file=$_POST['down_file'];
          Download();
        }
      }

      //URLだけでDL
      if (isset($_GET['file']) === true && isset($_GET['pass']) === true) {
        ob_end_clean();
        $send_pass=$_GET['pass'];
        $send_file=$_GET['file'];
        Download();
      }

      //ファイルの一覧表示
      $files=shell_exec("ls -1 ".$save_path.' | grep "__" | sed -e "s|__.*$||g"');
      //ファイルをradio型のhtmlに変換
      $file_list = "";
      while($files != "") {
        $file_name=preg_replace("|\n.*|u","",$files);
        $file_list=($file_list."\n".'      <br><label><input type="radio" name="down_file" value="'.$file_name.'">'.$file_name."</label>");
        $files=str_replace($file_name."\n","",$files);
      }
      //一覧表示
      echo "\n    <form method=\"post\">";
      echo "\n      <font size=5>～～～ファイル一覧～～～～</font>";
      echo "\n       ".$file_list."<br>";
      echo "\n      <font size=5>Pass:</font>";
      echo "\n      <input type=\"text\" name=\"down_pass\">";
      echo "\n      <label><input type=\"checkbox\" name=\"delete\" value=\"true\"><font size=4>削除</font></label><br>";
      echo "\n      <input type=\"submit\" name=\"down_send\" value=\"ダウンロード OR 削除\">";
      echo "\n    </form>";
    ?>
  </body>
</html>
