<?php 
	require_once 'config.php';
	
	$myname = $db->query("SELECT * FROM posts WHERE id = " .(int)$_GET['id'])->fetch(PDO::FETCH_ASSOC);
	$board_name = $db->query("SELECT * FROM board WHERE id = " .(int)$myname['board'])->fetch(PDO::FETCH_ASSOC);
	$data = $db->query("SELECT * FROM posts WHERE thread = " .(int)$_GET['id']. " ORDER BY date ASC");
	
	if(empty($myname)){
		header("Location: index.php");
	}

	if(isset($_POST['post'])){
		if($_SESSION['code'] == $_POST['captcha']){
			if(empty(trim($_POST['text']))){
				echo('No text<hr>');
			} else{
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
						$db->query("INSERT INTO posts (text, thread, name, ip, date, type) VALUES (
							" .$db->quote(nl2br($_POST['text'])). ", 
							'" .(int)$_GET['id']. "', 
							" .$db->quote($_POST['name']). ", 
							" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
							'" .time(). "', 0)");
					} else {
						$db->query("INSERT INTO posts (text, thread, name, ip, date, img, type) VALUES (
							" .$db->quote(nl2br($_POST['text'])). ", 
							'" .(int)$_GET['id']. "', 
							" .$db->quote($_POST['name']). ", 
							" .$db->quote($_SERVER['REMOTE_ADDR']). ", 
							'" .time(). "', 
							'" .fuckimg($_FILES['file']['tmp_name'], 0, 640). "', 0)");
					}
				} else {
					echo('Bad image<hr>');
				}

				header("Refresh:0");
			}
		} else {
			echo('Invalid CAPTCHA<hr>');
		}
	}

	function create_link($string) {
		$pattern = "/&gt;&gt;(\d+)/";
    	$replacement = "<a href=\"#$1\">$0</a>";
    	return preg_replace($pattern, $replacement, $string);
	}
?>

<html>
	<head>
		<title>OpenOne'ch</title>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		<link rel="stylesheet" href="css.css">
		<script>
			function answer(id){
				texthtml = document.getElementById('textt').innerHTML;
				document.getElementById('textt').innerHTML = texthtml + ">>" + id + " ";
			}
		</script>
	</head>
	<body>
		<a href="thread.php?id=<?php echo($board_name['id']); ?>">Назад</a>
		<center><?php echo("<h1>OpenOne'ch! / " .$board_name['name']. "</h1>"); ?></center>
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
						<td>Картинка: </td>
						<td><input type="file" class="file" name="file"></td>
					</tr>
					<tr>
						<td><img src="captcha.php"></td>
						<td>Каптча: <br><input type="text" name="captcha"></td>
					</tr>
					<tr>
						<td><button type="submit" name="post">[ Выложить ]</button></td>
					</tr>
				</table>
			</div>
			
		</form>

		<?php if($board_name['type'] == 1): ?>
			<?php $decoded = json_decode($myname['text'], true); ?>
			<table class="post">
				<tr class="inter_post">
					<td>
						<p>
							<?php 
								if($myname['name'] != null) { 
									echo(htmlspecialchars($myname['name'])); 
								} else { 
									echo('Анонимус'); 
								} 
							?>
							<?php echo(date(" H:i m/d/y", $myname['date'])) ?>
						</p>
						<?php echo('<a href="' .$myname['img']. '" download="' .$decoded['name']. '">' .$decoded['name']. ' (' .$decoded['size']. ' байт)</a>'); ?>
						<?php echo('<p>' .str_replace('&lt;br /&gt;', '<br>', htmlspecialchars($decoded['desc'])). '</p>'); ?>
					</td>
				</tr>
			</table>
		<?php elseif($board_name['type'] == 2): ?>
			<?php $decoded = json_decode($myname['text'], true); ?>
			<table class="post">
				<tr class="inter_post">
					<td>
						<p>
							<?php 
								if($myname['name'] != null) { 
									echo(htmlspecialchars($myname['name'])); 
								} else { 
									echo('Анонимус'); 
								} 
							?>
							<?php echo(date(" H:i m/d/y", $myname['date'])) ?>
						</p>
						<?php echo('<a href="flash.php?id='.(int)$_GET['id'].'">' .$decoded['name']. ' (Сыграть)</a>'); ?>
						<?php echo('<p>' .str_replace('&lt;br /&gt;', '<br>', htmlspecialchars($decoded['desc'])). '</p>'); ?>
					</td>
				</tr>
			</table>
		<?php else: ?>
			<table class="post">
				<tr class="inter_post">
					<?php 
						if($myname['img'] != null){ 
							echo('<td><img height="128px" src="' .$myname['img']. '">'); 
							echo('<br><a href="' .$myname['img']. '">Посмотреть</a></td>'); 
						} 
					?>
					<td>
						<p>
							<?php 
								if($myname['name'] != null) { 
									echo(htmlspecialchars($myname['name'])); 
								} else { 
									echo('Анонимус'); 
								} 
							?>
							<?php echo(date(" H:i m/d/y", $myname['date'])) ?>
						</p>
						<?php echo('<p>' . str_replace('&lt;br /&gt;', '<br>', htmlspecialchars($myname['text']). '</p>')); ?>
					</td>
				</tr>
			</table>
		<?php endif; ?><hr>

		<?php while($post = $data->fetch(PDO::FETCH_ASSOC)): ?>
			<table class="post" id="<?php echo($post['id']); ?>">
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
							<?php echo($post['id']); ?>
						</p>
						<?php echo('<p>' .str_replace('&lt;br /&gt;', '<br>', create_link(htmlspecialchars($post['text']))). '</p>'); ?>
						<a href="javascript:answer('<?php echo($post['id']); ?>');">Ответить</a>
					</td>
				</tr>
			</table>
		<?php endwhile; ?>
	</body>
</html>
