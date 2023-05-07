




//document.querySelectorAll("[id^='input_']")

function changeType(){

    var type = document.getElementsByName('type_val')[0].value;

    document.querySelectorAll("[name^='input_']").forEach(function(item, i, arr) {
        if('input_' + type !== item.getAttribute('name')){
            item.style.display = 'none';
        }else{
            item.style.display = 'block';
        }
    });

    document.querySelectorAll("[name^='button_']").forEach(function(item, i, arr) {
        if('button_' + type !== item.getAttribute('name')){
            item.style.display = 'none';
        }else{
            item.style.display = 'block';
        }
    });

}


