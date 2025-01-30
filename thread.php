<?php 
	require_once 'config.php';
	require_once "getid3/getid3.php";
	
	$myname = $db->query("SELECT * FROM board WHERE id = " .(int)$_GET['id'])->fetch(PDO::FETCH_ASSOC);
	$data = $db->query("SELECT * FROM posts WHERE board = " .(int)$_GET['id']. " ORDER BY date DESC");
	$error = '';

	if(empty($myname)){
		header("Location: index.php");
	}

	if(isset($_POST['post'])){
		if($_SESSION['code'] == $_POST['captcha']){
			if(empty(trim($_POST['text']))){
				$error = 'Нет текста<hr>';
			} else{
				$myname = $db->query("SELECT * FROM board WHERE id = " .(int)$_GET['id'])->fetch(PDO::FETCH_ASSOC);

				if($myname['type'] == 0){
					$error = 0;

					function fuckimg($src, $width, $height){
						global $_FILES, $error;

						if($_FILES['file']['type'] == 'image/jpeg'){
							$file = imagecreatefromjpeg($src);
						} elseif($_FILES['file']['type'] == 'image/png'){
							$file = imagecreatefrompng($src);
						} elseif($_FILES['file']['type'] == 'image/bmp'){
							$file = imagecreatefrombmp($src);
						} elseif($_FILES['file']['type'] == 'image/gif'){
							$file = imagecreatefromgif($src);
						} elseif($_FILES['file']['type'] == 'image/webp'){
							$file = imagecreatefromwebp($src);
						} else {
							http_response_code(400);
							$error = 1; 
						}
						
						$imgwidth= imagesx($file);
						$imgheight= imagesy($file);
						
						if(($imgheight / $imgwidth) >= 2.5){
							http_response_code(400);
							$error = 1; 
						} elseif(($imgheight / $imgwidth) <= 0.3){
							http_response_code(400);
							$error = 1;
						}                          
						
						if($error == 0){
							$imgwidth= imagesx($file);
							$imgheight= imagesy($file);
				
							if($width == 0){
								$width = ($height / $imgwidth) * $imgheight;
							} elseif($height == 0){
								$height = ($width / $imgheight) * $imgwidth;
							}
				
							$size = imagecreatetruecolor((int)$height, (int)$width);
				
							imagecopyresampled($size, $file, 0, 0, 0, 0, (int)$height, (int)$width,  imagesx($file), imagesy($file));
				
							$filesrc = 'cdn/' .uniqid(). '.jpg';
				
							imagejpeg($size, $filesrc, 80);

							return $filesrc;
						}
					}

					if(!empty($_FILES)){
						if($_FILES['file']['error'] == 0){
							if(!unlink(fuckimg($_FILES['file']['tmp_name'], 0, 50))){
								$error = 1;
							}
						}
					}

					if($error == 0){
						if(empty($_FILES) or $_FILES['file']['error'] != 0){
							$db->query("INSERT INTO posts (text, board, name, ip, date, type) VALUES (
								" .$db->quote(nl2br($_POST['text'])). ", 
								'" .(int)$_GET['id']. "', 
								" .$db->quote($_POST['name']). ", 
								" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
								'" .time(). "', 1)");
						} else {
							$db->query("INSERT INTO posts (text, board, name, ip, date, img, type) VALUES (
								" .$db->quote(nl2br($_POST['text'])). ", 
								'" .(int)$_GET['id']. "', 
								" .$db->quote($_POST['name']). ", 
								" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
								'" .time(). "', 
								'" .fuckimg($_FILES['file']['tmp_name'], 0, 640). "', 1)");
						}
						
						header("Refresh:0");
					} else {
						$error = 'Плохой формат изображение<hr>';
					}
				} elseif($myname['type'] == 1){
					$getid3 = new getID3();
					$file_info = $getid3->analyze($_FILES['file']['tmp_name']);
					if($file_info['mime_type'] != null){
						$file = "cdn/" .uniqid(). "." .$file_info['fileformat'];

						if(move_uploaded_file($_FILES['file']['tmp_name'], $file)){
							$file_json = array(
								'name' => $_FILES['file']['name'],
								'size' => $_FILES['file']['size'],
								'desc' => nl2br($_POST['text'])
							);

							$db->query("INSERT INTO posts (text, board, name, ip, date, img, type) VALUES (
								" .$db->quote(json_encode($file_json)). ", 
								'" .(int)$_GET['id']. "', 
								" .$db->quote($_POST['name']). ", 
								" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
								'" .time(). "', 
								'" .$file. "', 1)");

							header("Refresh:0");
						}
					} else {
						$error = 'Попробуйте запаковать свой файл в .zip архив со сжатием deflate (Приколы GetID3)<hr>';
					}
				} elseif($myname['type'] == 2){
					$getid3 = new getID3();
					$file_info = $getid3->analyze($_FILES['file']['tmp_name']);

					if($file_info['mime_type'] == 'application/x-shockwave-flash'){
						$file = "cdn/" .uniqid(). "." .$file_info['fileformat'];

						if(move_uploaded_file($_FILES['file']['tmp_name'], $file)){
							$file_json = array(
								'name' => $_FILES['file']['name'],
								'size' => $_FILES['file']['size'],
								'desc' => nl2br($_POST['text'])
							);

							$db->query("INSERT INTO posts (text, board, name, ip, date, img, type) VALUES (
								" .$db->quote(json_encode($file_json)). ", 
								'" .(int)$_GET['id']. "', 
								" .$db->quote($_POST['name']). ", 
								" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
								'" .time(). "', 
								'" .$file. "', 1)");

							header("Refresh:0");
						}
						
					} else {
						$error = 'Это не SWF файл!<hr>';
					}
				}
			}
		} else {
			$error = 'Неверная CAPTCHA<hr>';
		}
	}

	function conlink($text) {
		$pattern = '/(http|https|ftp):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
		
		$text = preg_replace($pattern, '<a href="$0" target="_blank">$0</a>', $text);
		
		return $text;
	}
	
?>

<html>
	<head>
		<title>OpenOne'ch</title>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		<link rel="stylesheet" href="<?php echo($_SESSION['theme']) ?>">
	</head>
	<body>
		<a href="index.php" class="right">Домой</a>
		<center>
			<?php if(!empty($error)) echo($error); ?>
			<?php echo("<h1>OpenOne'ch! / " .$myname['name']. "</h1>"); ?>
		</center>
		<form method="post"  enctype="multipart/form-data">
			<div class="table-wrapper">
				<table class="submit">
					<tr>
						<td>Ник (Анонимус): </td>
						<td><input type="text" name="name"></td>
					</tr>
					<tr>
						<td>Текст: </td>
						<td><textarea name="text" id="textt"></textarea></td>
					</tr>
					<tr>
						<td>Файл: </td>
						<td><input type="file" class="file" name="file"></td>
					</tr>
					<tr>
						<td><img src="captcha.php"></td>
						<td>Каптча: <br><input type="text" name="captcha"></td>
					</tr>
					<tr>
						<td><button type="submit" name="post">[ Создать тред ]</button></td>
					</tr>
				</table>
			</div>
		</form>
		
		<?php if($myname['type'] != 0): ?>
			<table class="files">
				<thead>
					<tr>
						<td>Пользователь</td>
						<td>Имя</td>
						<td>Размер</td>
						<td>Дата</td>
						<td>Время</td>
						<td>Описание</td>
					</tr>
				</thead>
				<tbody>
					<?php while($post = $data->fetch(PDO::FETCH_ASSOC)): ?>
						<?php
							if(!json_decode($post['text'], true)){
								unlink($post['img']);
								$db->query('DELETE FROM posts WHERE id = ' .$post['id']);
								header("Refresh:0");
							} 
							
							$decoded = json_decode($post['text'], true); 
						?>
						<tr onclick="window.location.href='board.php?id=<?php echo($post['id']); ?>';">
							<td>
								<?php 
									if($post['name'] != null) { 
										echo(htmlspecialchars($post['name'])); 
									} else { 
										echo('Анонимус'); 
									} 
								?>
							</td>
							<td><?php echo($decoded['name']) ?></td>
							<td><?php echo($decoded['size']) ?></td>
							<td><?php echo(date("m/d/y", $post['date'])) ?></td>
							<td><?php echo(date("H:i", $post['date'])) ?></td>
							<td><?php echo($decoded['desc']) ?></td>
							<td>В тред (<?php echo($db->query('SELECT * FROM posts WHERE thread = ' .$post['id'])->rowCount()) ?> Постов)</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		<?php else: ?>
			<?php while($post = $data->fetch(PDO::FETCH_ASSOC)): ?>
				<table class="post">
					<tr class="inter_post">
						<?php 
							if($post['img'] != null){ 
								echo('<td><img height="128px" src="' .$post['img']. '">'); 
								echo('<br><a href="' .$post['img']. '">Посмотреть</a></td>'); 
							} 
						?>
						<td>
							<p>
								<?php 
									if($post['name'] != null) { 
										echo(htmlspecialchars($post['name'])); 
									} else { 
										echo('Анонимус'); 
									} 
								?>
								<?php echo(date(" H:i m/d/y", $post['date'])) ?>
							</p>
							<?php echo('<p>' .str_replace('&lt;br /&gt;', '<br>', conlink(htmlspecialchars($post['text']))). '</p>'); ?>
							<a href="board.php?id=<?php echo($post['id']); ?>">В тред (<?php echo($db->query('SELECT * FROM posts WHERE thread = ' .$post['id'])->rowCount()) ?>)</a>
						</td>
					</tr>
				</table>
			<?php endwhile; ?>
		<?php endif; ?>
	</body>
</html>
