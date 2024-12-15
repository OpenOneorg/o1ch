<?php 
	require_once 'config.php';

    $myname = $db->query("SELECT * FROM posts WHERE id = " .(int)$_GET['id'])->fetch(PDO::FETCH_ASSOC);
	$board_name = $db->query("SELECT * FROM board WHERE id = " .(int)$myname['board'])->fetch(PDO::FETCH_ASSOC);
	
	if(empty($myname)){
		header("Location: index.php");
	}

    if($board_name['type'] != 2){
        header("Location: index.php"); 
    }
?>

<html>
	<head>
		<title>OpenOne'ch</title>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		<link rel="stylesheet" href="css.css">
	</head>
	<body>
        <a href="board.php?id=<?php echo($myname['id']); ?>">Назад</a>
		<center>
			<h1>OpenOne'ch! // Flash Player</h1>	
            <div class="block1">
                <?php $decoded = json_decode($myname['text'], true); ?>
                <h3><?php echo($decoded['name']) ?></h3>
                <embed src="<?php echo($myname['img']) ?>" type="application/x-shockwave-flash">
                <script src="https://unpkg.com/@ruffle-rs/ruffle"></script>
            </div>
        </center>
	</body>
</html>
