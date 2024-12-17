<?php 
	require_once 'config.php';
	require_once "getid3/getid3.php";

    $myname = $db->query("SELECT * FROM posts WHERE id = " .(int)$_GET['id'])->fetch(PDO::FETCH_ASSOC);
	$board_name = $db->query("SELECT * FROM board WHERE id = " .(int)$myname['board'])->fetch(PDO::FETCH_ASSOC);
	
	if(empty($myname)){
		header("Location: index.php");
	}

    if($board_name['type'] != 2){
        header("Location: index.php"); 
    }

	$getid3 = new getID3();
	$file_info = $getid3->analyze($myname['img']);
?>

<html>
	<head>
		<title>OpenOne'ch</title>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		<link rel="stylesheet" href="<?php echo($_SESSION['theme']) ?>">
	</head>
	<body>
        <a href="board.php?id=<?php echo($myname['id']); ?>" class="right">Назад</a>
		<center>
			<h1>OpenOne'ch! // Flash Player</h1>	
            <div class="block1">
                <?php $decoded = json_decode($myname['text'], true); ?>
                <h3><?php echo($decoded['name']) ?></h3>
                <embed src="<?php echo($myname['img']) ?>" type="application/x-shockwave-flash" width="<?php echo($file_info['video']['resolution_x']) ?>px" height="<?php echo($file_info['video']['resolution_y']) ?>px">
                <script src="https://unpkg.com/@ruffle-rs/ruffle"></script><br><br>
				<a href="<?php echo($myname['img']) ?>" download="<?php echo($decoded['name']) ?>">Скачать .swf файл</a>
            </div>
        </center>
	</body>
</html>
