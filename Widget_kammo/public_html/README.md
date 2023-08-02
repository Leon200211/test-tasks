
Тестовое заданиена вакансию PHP-разработчика.
Написать приватный виджет amoCRM, который будет добавлять кнопку в карточке сделки
в правой панели (она предназначена для виджетов). По нажатию на кнопку по API v4
будут выниматься названия и количество всех товаров этой сделки и выводиться в 
модальное окно в удобном виде. Для формирования кнопки и модального окна должен 
использоваться нативный вид элементов amoCRM (для этого есть шаблоны генерации
кнопок и модальных окон https://storybook.amocrm.ru/ ). Получение информации о 
товарах должно выполняться скриптом на backend'е по API v4 amoCRM, т.е. виджет
должен делать запрос на backend и получать данные оттуда. Авторизация в API - oAuth.
Для написания и демонстрации рекомендуется использовать международный проект 
amoCRM - https://www.kommo.com/ (потому что там можно легко создавать приватные
виджеты, а в версии https://www.amocrm.ru/ требуются дополнительные манипуляции
нужно отправлять паспортные данные). Необходимо создать тестовый аккаунт на 
https://www.kommo.com/ Для реализации функции товаров на https://www.kommo.com/
использовать сущность Списков - Lists (добавить свой Список или использовать 
создаваемый автоматически Products).
Документация на английском - https://www.kommo.com/developers/ , 
на русском - https://www.amocrm.ru/developers/