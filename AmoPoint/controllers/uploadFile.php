<?php


// Разрешенные расширения файлов.
$allow = array(
    'txt'
);


// Директория куда будут загружаться файлы.
$path = $_SERVER['DOCUMENT_ROOT'] . '/AmoPoint/file/';


$error = $success = '';
if (!isset($_FILES['file'])) {
    $error = 'Файл не загружен.';
} else {
    $file = $_FILES['file'];

    // Проверим на ошибки загрузки.
    if (!empty($file['error']) || empty($file['tmp_name'])) {
        $error = 'Не удалось загрузить файл.';
    } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
        $error = 'Не удалось загрузить файл.';
    } else {
        // Оставляем в имени файла только буквы, цифры и некоторые символы.
        $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
        $name = mb_eregi_replace($pattern, '-', $file['name']);
        $name = mb_ereg_replace('[-]+', '-', $name);
        $parts = pathinfo($name);

        if (empty($name) || empty($parts['extension'])) {
            $error = 'Недопустимый тип файла';
        } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
            $error = 'Недопустимый тип файла';
        } else {
            // Перемещаем файл в директорию.
            if (move_uploaded_file($file['tmp_name'], $path . 'test.txt')) {
                // Далее можно сохранить название файла в БД и т.п.
            } else {
                $error = 'Не удалось загрузить файл.';
            }
        }
    }
}

// Вывод сообщения о результате загрузки.


$data = array(
    'error'   => $error,
);

header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();