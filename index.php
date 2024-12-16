<?php 
	require_once 'config.php';
	
	$data = $db->query("SELECT * FROM board");

	if(isset($_GET['theme'])){
		$_SESSION['theme'] = $_GET['theme'];
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
		<center>
			<h1>OpenOne'ch</h1>
			<p>Имиджборд от <a href="https://t.me/openone_channel">OpenOne</a> который пробует копировать 4chan и 2ch, но в более простом виде</p>			
		</center>
		
		<h2 align="center">Доски: </h2>
		<div class="block">
			<?php 
				while($boards = $data->fetch(PDO::FETCH_ASSOC)){
					echo('<a href="thread.php?id=' .$boards['id']. '">' .$boards['name']. ' (' .$db->query("SELECT * FROM posts WHERE board = " .$boards['id'])->rowCount(). ')</a><br>');
				}
			?>
			<p>Всего постов на сайте: <?php echo($db->query("SELECT * FROM posts")->rowCount()); ?></p>
		</div>
		<p style="text-align: center;">Темы:<br> | 
			<?php
				$directory = dirname(__FILE__) . "/";
				$files = scandir($directory);

				foreach ($files as $file) {
					if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
						echo('<a href="?theme=' .$file. '">'. $file .'</a> | ');
					}
				}
			?><br><br>Ссылки:<br> | 
			<?php
				foreach ($links as $name => $link){
					echo('<a href="'. $link .'">'. $name .'</a> | ');
				}
			?><br><br>
			Почта: <?php echo($email); ?>
		</p>
	</body>
</html>
