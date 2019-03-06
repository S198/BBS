<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>掲示板</title>
		<style>
            body{
               background: rgba(170,220,170,0.7);
               margin: 150px 5px 5px 5px;
               padding: 15px;
            }
            description{ 
                position: fixed;
                top:0px;
                left:0px;
                width:100%;
                background: rgba(170,220,170,0.7);
                line-height: 1;
                padding: 15px;
            }
		</style>
	</head>
<body>
	<description>
	<h1>掲示板</h1>
	<p>ご自由にご利用ください。</p>
	</description>
	<form method="POST">
		<!--名前・コメント記入欄を用意する。-->
		<p><input type="text" name="namae" value="" placeholder="名前"></p>
		<p><input type="text" name="komento" value="" placeholder="コメント"></p>
		<p><input type="text" name="pasuwaado1" value="" placeholder="パスワード">
		<input type="submit" name="submit" value="投稿"></p>
		<!--削除用フォームを用意する。-->
		<p><input type="text" name="sakujo" value="" placeholder="削除対象番号">
		<p><input type="text" name="pasuwaado2" value="" placeholder="パスワード">
		<input type="submit" name="submit" value="削除"></p>
		<!--編集番号指定用フォームを用意する。-->
		<p><input type="text" name="henshuu" value="" placeholder="編集対象番号＆上記記入">
		<p><input type="text" name="pasuwaado3" value="" placeholder="パスワード">
		<input type="submit" name="submit" value="編集"></p>
	</form>
	<?php
	/*MySQLデータベースへの接続を行う。*/
	$dsn = 'mysql:dbname=データベース名; host=localhost';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

	/*データベース内にテーブルを作成する*/
	$sql = "CREATE TABLE IF NOT EXISTS BBStable"
	."("
	. "id INT,"
	. "name char(32),"
	. "comment TEXT,"
	. "date	char(32),"
	. "password	char(32)"		
	.");";
	$stm = $pdo -> query($sql);
	
	/**送信ボタンが押された時の処理**/
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		/*変数の宣言。それぞれにHTMLからの値を代入します。
		（"POST"formの中の、inputタグ内nameが識別子）*/
		$name = $_POST["namae"];
		$comment = $_POST["komento"];
		$date = date("Y年m月d日 H時i分s秒");
		$deletion = $_POST["sakujo"];
		$edit = $_POST["henshuu"];
		$password_new = $_POST["pasuwaado1"];
		$password_delete = $_POST["pasuwaado2"];
		$password_edit = $_POST["pasuwaado3"];
			
		/**削除を行う場合**/
		if (!empty($deletion)) {
			
			/*削除が指定された行を、パスワード認証して削除する*/
			$sql = "DELETE FROM BBStable WHERE id = $deletion AND password = '$password_delete'";
			$stm = $pdo -> prepare($sql);
			$stm -> execute();
			
			/*削除した後のデータベースのデータを表示する*/
			$sql = 'SELECT * FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row){
				echo $row['id'].',';
				echo $row['name'].',';
				echo $row['comment'].',';
				echo $row['date'].'<br>';
			}
		
		/**編集を行う場合**/
		} elseif (!empty($edit)) {
			
			/*編集が指定された行を、パスワード認証して編集する*/
			$sql = "UPDATE BBStable SET name=:name, comment=:comment, date=:date, password=:password WHERE id=:id AND password = '$password_edit'";
			$stm = $pdo -> prepare($sql);
			$stm -> bindParam(':id', $edit, PDO::PARAM_STR);
			$stm -> bindParam(':name', $name, PDO::PARAM_STR);
			$stm -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$stm -> bindParam(':date', $date, PDO::PARAM_STR);
			$stm -> bindParam(':password', $password_new, PDO::PARAM_STR);
			$stm -> execute();

			/*編集後のデータをselectによって表示する*/
			$sql = 'SELECT * FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row) {
				echo $row['id'].',';
				echo $row['name'].',';
				echo $row['comment'].',';
				echo $row['date'].'<br>';
			}
			
		/**名前が空の場合**/
		} elseif (empty($name)) {
			/*メッセージを表示する*/
			echo "お名前の欄が空でした。";
			/*データベースのデータを表示する*/
			$sql = 'SELECT * FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row){
				echo $row['id'].',';
				echo $row['name'].',';
				echo $row['comment'].',';
				echo $row['date'].'<br>';
			}

		/**コメントが空の場合**/
		} elseif (empty($comment)) {
			/*メッセージを表示する*/
			echo "コメントの欄が空でした。";
			/*データベースのデータを表示する*/
			$sql = 'SELECT * FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row){
				echo $row['id'].',';
				echo $row['name'].',';
				echo $row['comment'].',';
				echo $row['date'].'<br>';
			}

		/**入力された内容を保存＆投稿する場合**/
		} else {
			
			/*変数$idの値を取得する*/
			$id = 1;
			$sql = 'SELECT id FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row){
				$id = ++$row['id'];
			}

			/*作成したテーブルにinsertを行って、データを入力する。*/
			$sql = $pdo -> prepare("INSERT INTO BBStable (id, name, comment, date, password) VALUES (:id, :name, :comment, :date, :password)");
			/*bindParamの引数は、テーブルのカラム名が入る*/
			$sql -> bindParam(':id', $id, PDO::PARAM_INT);
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$sql -> bindParam(':date', $date, PDO::PARAM_STR);
			$sql -> bindParam(':password', $password_new, PDO::PARAM_STR);
			$sql -> execute();

			/*データベースのデータ＋新規追加されたデータを表示する*/
			$sql = 'SELECT * FROM BBStable';
			$stm = $pdo -> query($sql);
			$results = $stm -> fetchAll();
			foreach ($results as $row){
				echo $row['id'].',';
				echo $row['name'].',';
				echo $row['comment'].',';
				echo $row['date'].'<br>';
			}
		}

	/**送信ボタンが押される前の処理**/
	} else {
		/**データベースのデータを表示する**/
		$sql = 'SELECT * FROM BBStable';
		$stm = $pdo -> query($sql);
		$results = $stm -> fetchAll();
		foreach ($results as $row){
			echo $row['id'].',';
			echo $row['name'].',';
			echo $row['comment'].',';
			echo $row['date'].'<br>';
		}
	}
	?>
</body>
</html>