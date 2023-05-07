<?php

$data = $_REQUEST;

if(empty($_REQUEST)){
    exit(0);
}



// Валидируем данные
if (!filter_var($_REQUEST['ip'], FILTER_VALIDATE_IP)) {
    echo "IP-адрес '{$_REQUEST['ip']}' указан не верно.";
    exit();
}
$ip = htmlspecialchars(stripslashes(trim($_REQUEST['ip'])));
$city = htmlspecialchars(stripslashes(trim($_REQUEST['city'])));
$device = htmlspecialchars(stripslashes(trim($_REQUEST['device'])));
$browser = htmlspecialchars(stripslashes(trim($_REQUEST['browser'])));


require_once 'config.php';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $opt);

    $stmt = $pdo->prepare("INSERT INTO visit (ip, city, device, browser) VALUES(?,?,?,?)");

    try {
        $pdo->beginTransaction();
        $stmt->execute(array($ip, $city, $device, $browser));
        $id = $pdo->lastInsertId();
        $pdo->commit();

        $res = [
            'id' => $id,
            'error' => ''
        ];
        echo json_encode($res);
    } catch (PDOExecption $e) {
        $pdo->rollback();
        exit("Error!: " . $e->getMessage() . "</br>");
    }

}catch(PDOExecption $e) {
    exit("Error!: " . $e->getMessage() . "</br>");
}




