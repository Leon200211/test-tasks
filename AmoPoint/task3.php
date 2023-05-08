<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>




    <script src="http://yastatic.net/jquery/2.1.1/jquery.min.js"></script>
    <script src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.4/mobile-detect.min.js"></script>
    <script language="Javascript">

        // Получение значений куки
        function getCookieVal (offset) {
            var endstr = document.cookie.indexOf (";", offset);
            if (endstr == -1)
                endstr = document.cookie.length;
            return unescape(document.cookie.substring(offset, endstr));
        }

        // Получение куки
        function GetCookie (name) {
            var arg = name + "=";
            var alen = arg.length;
            var clen = document.cookie.length;
            var i = 0;
            while (i < clen) {
                var j = i + alen;
                if (document.cookie.substring(i, j) == arg)
                    return getCookieVal (j);
                i = document.cookie.indexOf(" ", i) + 1;
                if (i == 0)
                    break;
            }
            return null;
        }

        // Установка куки
        function SetCookie (name, value) {
            var argv = SetCookie.arguments;
            var argc = SetCookie.arguments.length;
            var expires = (argc > 2) ? argv[2] : null;
            var path = (argc > 3) ? argv[3] : null;
            var domain = (argc > 4) ? argv[4] : null;
            var secure = (argc > 5) ? argv[5] : false;
            document.cookie = name + "=" + escape (value) +
                ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
                ((path == null) ? "" : ("; path=" + path)) +
                ((domain == null) ? "" : ("; domain=" + domain)) +
                ((secure == true) ? "; secure" : "");
        }

        // Удаление куки
        function DeleteCookie(name) {
            var exp = new Date();
            FixCookieDate (exp);
            exp.setTime (exp.getTime() - 1);
            var cval = GetCookie (name);
            if (cval != null)
                document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
        }




        // Если это наш первый заход за сутки
        if (GetCookie('fitlent_visit') === null) {

            // устройство пользователя
            let userDevice = '';
            let userBrowser;
            const devices = new RegExp('Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini', "i");

            let detect = new MobileDetect(window.navigator.userAgent)

            if (devices.test(navigator.userAgent)) {
                let detect = new MobileDetect(window.navigator.userAgent)
                userDevice += "Mobile: " + detect.mobile();             // телефон или планшет
                userDevice += "Phone: " + detect.phone() + ' ';         // телефон
                userDevice += "Tablet: " + detect.tablet() + ' ';       // планшет
                userDevice += "OS: " + detect.os() + ' ';               // операционная система
                userDevice += "userAgent: " + detect.userAgent() + ' '; // userAgent

                userBrowser = navigator.userAgent;
            } else {
                userDevice = 'Компьютер';
                userBrowser = navigator.userAgent;
            }

            // Город пользователя
            window.onload = function () {
                //jQuery("#user-city").text(ymaps.geolocation.city);
                let city = ymaps.geolocation.city;
                //jQuery("#user-region").text(ymaps.geolocation.region);
                //jQuery("#user-country").text(ymaps.geolocation.country);

                // api пользователя
                let ip;
                $.ajax({
                    url:'https://ipapi.co/json/',
                    type:'get',
                    dataType:'json'
                }).done(function(data) {
                    ip = data.ip;

                    var formData = new FormData();
                    console.log(ip);
                    formData.append('ip', ip);
                    formData.append('city', city);
                    formData.append('device', userDevice);
                    formData.append('browser', userBrowser);

                    $.ajax({
                        type: "POST",
                        url: 'task3/getVisit.php',
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        dataType : 'json',
                        success: function(msg){
                            console.log(msg);
                            if (msg.error == '') {
                                var expdate = new Date();
                                expdate.setTime(expdate.getTime() + (24*60*60*1000));
                                SetCookie("fitlent_visit",msg.id,expdate);
                            } else {
                                console.log('error');
                            }
                        }
                    });


                });



            }

        }

    </script>


</head>
<body>


<br>
<a href="index.php">Задание 1</a>
<a href="task2.html">Задание 2</a>
<br>
<br>


<div id="user-city"></div> <div id="user-region"></div> <div id="user-
country"></div>

<a href="test3_admin.php">Войти в админку</a>

</body>
</html>