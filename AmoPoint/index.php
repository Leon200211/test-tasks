<!doctype html>
<html lang="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>


<input type="file" id="js-file">

<div id="result">
    <!-- Результат из upload.php -->
</div>

<div id="fileText" style="margin-top: 50px; display: none">
    <input name="symbol" id="symbol" placeholder="Введите разделяющий символ">
    <input type="button" id="btn" value="Отфильтровать" />
</div>

<div id="result2">
    <!-- Результат из upload.php -->
</div>

<script src="assets/js/uploadfile.js"></script>
</body>
</html>