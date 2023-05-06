$("#js-file").change(function(){
    if (window.FormData === undefined) {
        alert('В вашем браузере FormData не поддерживается')
    } else {
        var formData = new FormData();
        formData.append('file', $("#js-file")[0].files[0]);

        $.ajax({
            type: "POST",
            url: 'controllers/uploadFile.php',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            dataType : 'json',
            success: function(msg){
                if (msg.error == '') {
                    $('#result').html('<p style=\"color: green\">🟢Файл успешно загружен.</p>');
                    document.getElementById('fileText').style.display = 'block';
                } else {
                    $('#result').html('<p style="color: red">🔴' + msg.error + '</p>');
                }
            }
        });
    }
});





$("#btn").click(function(){
    var formData = new FormData();
    formData.append('symbol', document.getElementById('symbol').value);

    $.ajax({
        type: "POST",
        url: 'controllers/filterFile.php',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        dataType : 'json',
        success: function(msg){

            var block = document.getElementById('result2');
            block.innerHTML = '';

            msg.forEach(function(item, i, arr) {
                console.log(item);
                var z = document.createElement('p'); // is a node
                z.innerHTML = item['str'] + ' = ' + item['Ncount'];
                block.appendChild(z);

            });

        },
        error: function(response) { // Данные не отправлены
            //$('#result_form').html('Ошибка. Данные не отправлены.');
            console.log(response);
        }
    });
});


