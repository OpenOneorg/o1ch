<?php 
	require_once 'config.php';
	
	$data = $db->query("SELECT * FROM board");
?>

<html>
	<head>
		<title>OpenOne'ch</title>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		<style>
			body{
				background-color: #0402AC;
				color: #54FEFC;
			}
			
			a{
				color: #54FEFC;
			}
			
			a:hover{
				background-color: #54FEFC;
				color: #0402AC;
			}
			
			.block{
				padding: 8px;
				margin: 16px auto;
				border: 6px double #54FEFC;
				max-width: 540px;
			}

		</style>
	</head>
	<body>
		<center>
			<h1>OpenOne'ch</h1>
			<p>Имеджборд от <a href="https://t.me/openone">OpenOne</a> который пробует копировать 4chan и 4pda</p>			
		</center>
		
		<h2 align="center">Доски: </h2>
		<div class="block">
			<?php 
				while($boards = $data->fetch(PDO::FETCH_ASSOC)){
					echo('<a href="board.php?id=' .$boards['id']. '">' .$boards['name']. '</a><br>');
				}
			?>
		</div>
	</body>
</html>
