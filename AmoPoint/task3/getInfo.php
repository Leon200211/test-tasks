<?php



require_once 'config.php';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $opt);


    $allInfo = [];
    $city = [];

    $sql = 'SELECT * FROM visit';
    foreach ($pdo->query($sql) as $row) {
        $allInfo[] = $row;
        $city[] = $row['city'];
    }

    $visitCount = [];
    $now = date("Y-m-d H:i:s");
    $yesterday = (new DateTime('-1 days'))->format('Y-m-d H:i:s');
    $sql = "SELECT * FROM visit WHERE `date` BETWEEN '$yesterday' AND '$now'";
    foreach ($pdo->query($sql) as $row) {
        $visitCount[] = $row['date'];
    }

    $visitCountPerDay = [
        '00' => 0,
        '01' => 0,
        '02' => 0,
        '03' => 0,
        '04' => 0,
        '05' => 0,
        '06' => 0,
        '07' => 0,
        '08' => 0,
        '09' => 0,
        '10' => 0,
        '11' => 0,
        '12' => 0,
        '13' => 0,
        '14' => 0,
        '15' => 0,
        '16' => 0,
        '17' => 0,
        '18' => 0,
        '19' => 0,
        '20' => 0,
        '21' => 0,
        '22' => 0,
        '23' => 0,
    ];

    // Формируем данные для графика
    foreach ($visitCount as $visit){
        $visitHour = date('H', strtotime($visit));
        $visitCountPerDay[$visitHour] += 1;
    }

    $res = [
        'allInfo' => $allInfo,
        'city' => array_count_values($city),
        'count' => $visitCountPerDay
    ];



    echo json_encode($res);

}catch(PDOExecption $e) {
    exit("Error!: " . $e->getMessage() . "</br>");
}




