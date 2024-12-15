<?php
    // Если у вас InfinityFree или другой говнохостинг, то оставьте тут false
    $antiddos = false;

    if($antiddos == true){
        require "anti-ddos-lite/anti-ddos-lite.php";
    }

    session_start();

    $dbconn = array(
        'server' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db' => 'o1ch'
    );

    $db = new PDO("mysql:host=" .$dbconn['server']. ";dbname=" .$dbconn['db'],
        $dbconn['user'],
        $dbconn['pass']
    );

    $db->exec("set names utf8mb4");

    if($db == false){
        echo('Ошибка подключение базы данных');
    }
?>
