# Обёртка для взаимодействия с Api AmoCRM (через токены)

### Алгоритм работы:
1. CRM -> Настройки -> Интеграции -> "Создать интеграцию"
2. Вводим адрес хука,
3. Выдаём все доступы, вводим название и описание. Сохраняем.
4. Открываем интеграцию, переходим на вкладку "Ключи и доступы".
5. В коде хука указываем следующее
```php
require 'autoload.php';

$authData = array(
    'client_id'     => 'ID интеграции',
    'client_secret' => 'Секретный ключ',
    'redirect_uri'  => 'Адрес хука из п.2', 
    'domain'        => 'Домен Amo'
);

try {
    $amo = new AmoCRM($authData['domain'], new Token($authData));
    $lead = new Lead(32128767);
    
    //Кастомные поля
    $lead
        ->setCustomFieldValue('roistat', 12345)
        ->setCustomFieldValue('fbclid', 'fbclid')
        ->setCustomFieldValue('from', 'from')
        ->setCustomFieldValue('openstat_source', 'openstat_source')
        ->setName('Test roistat Lead')
    ;
    $res = $lead->save();
    debug($res);

    //Поиск контакта 
    $search = $amo->searchContacts('79999999999');
    debug($search);

} catch (Exception $e) {
    echo "Ошибка в получении токена: {$e->getMessage()}";
}
```
Запускаем хук в бразуере. Жмем на кнопку "Установить интеграцию".
Открывается новое окно в котором выбираем нужный проект АмоСрм и жмем "Разрешить".

##### Перегенерацие ключа
* Если есть необходимость сгенерировать ключ заново, то просто удаляем файл token_file.json из папки libs
* Далее просто запускаем хук в браузере

##### Подготовака файла
* Проверяем есть ли доступы на запись файла **libs/**, в идеале самим поставить 770