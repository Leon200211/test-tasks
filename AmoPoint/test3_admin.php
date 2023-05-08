<?php


require_once 'task3/config.php';

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


    $now = date("H");  // текущий час
    $hourdif = 23 - $now;

    $visitCountPerDay = [

    ];
    for ($i = $now; $i >= 0; $i--){
        if($i < 10){
            $visitCountPerDay['0'.$i] = 0;
        }else{
            $visitCountPerDay[$i] = 0;
        }
    }
    for ($i = 23; $i >= $hourdif-2; $i--){
        if($i < 10){
            $visitCountPerDay['0'.$i] = 0;
        }else{
            $visitCountPerDay[$i] = 0;
        }
    }

    // разворачиваем массив часов
    $visitCountPerDay = array_reverse($visitCountPerDay, 1);



    // Формируем данные для графика
    foreach ($visitCount as $visit) {
        $visitHour = date('H', strtotime($visit));
        $visitCountPerDay[$visitHour] += 1;
    }

    $res = [
        'allInfo' => $allInfo,
        'city' => array_count_values($city),
        'count' => $visitCountPerDay
    ];

} catch (PDOExecption $e) {
    exit("Error!: " . $e->getMessage() . "</br>");
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Charts / Chart.js - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">



    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

</head>

<body>

<main id="main" class="main">

    <a href="task3.php">Выйти</a>
    <br>
    <br>
    <br>

    <div class="pagetitle">

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Посещения за 24 часа</h5>

                        <!-- Line Chart -->
                        <canvas id="lineChart" style="max-height: 400px;"></canvas>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new Chart(document.querySelector('#lineChart'), {
                                    type: 'line',
                                    data: {
                                        labels: [<?php
                                            foreach(array_keys($res['count']) as $key){
                                                echo $key . ', ';
                                            }
                                            ?>],
                                        datasets: [{
                                            label: 'Кол-во посещений',
                                            data: [            <?php
                                                foreach($res['count'] as $count){
                                                    echo $count . ', ';
                                                }
                                                ?>],
                                            fill: false,
                                            borderColor: 'rgb(75, 192, 192)',
                                            tension: 0.1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                        <!-- End Line CHart -->

                    </div>
                </div>
            </div>


            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Посещение по городам</h5>

                        <!-- Pie Chart -->
                        <canvas id="pieChart" style="max-height: 400px;"></canvas>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new Chart(document.querySelector('#pieChart'), {
                                    type: 'pie',
                                    data: {
                                        labels: [
                                            <?php
                                            foreach(array_keys($res['city']) as $city){
                                                echo "'" . $city . "', ";
                                            }
                                            ?>
                                        ],
                                        datasets: [{
                                            label: 'Кол-во',
                                            data: [
                                                <?php
                                                foreach($res['city'] as $cityCount){
                                                    echo $cityCount . ', ';
                                                }
                                                ?>
                                            ],
                                            hoverOffset: 4
                                        }]
                                    }
                                });
                            });
                        </script>
                        <!-- End Pie CHart -->

                    </div>
                </div>
            </div>



        </div>
    </section>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Таблица базы данных</h5>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">ip</th>
                        <th scope="col">City</th>
                        <th scope="col">Device</th>
                        <th scope="col">Browser</th>
                        <th scope="col">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($res['allInfo'] as $visit): ?>
                        <tr>
                            <th scope="row"><?=$visit['id']?></th>
                            <td><?=$visit['ip']?></td>
                            <td><?=$visit['city']?></td>
                            <td><?=$visit['device']?></td>
                            <td><?=$visit['browser']?></td>
                            <td><?=$visit['date']?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- End Bordered Table -->
            </div>
        </div>
    </div>

</main><!-- End #main -->



<!-- Vendor JS Files -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.min.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/tinymce/tinymce.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>


</body>

</html>