<?php

return [
    'fields' => [
        'login'     => 'Логин',
        'token'     => 'Токен',
        'email'     => 'Email',
        'phone'     => 'Телефон',
        'password'  => 'Пароль',
        'code'      => 'Код',
        'condition' => 'Условие'
    ],
    'errors' => [
        'isNotUnique'        => 'Не является уникальным значением',
        'unknownUser'        => 'Пользователь не найден в системе',
        'wrongPassword'      => 'Неправильный пароль',
        'wrongToken'         => 'Неправильный токен',
        'codeExpired'        => 'Время действия кода истекло',
        'wrongCode'          => 'Неверный код',
        'createToken'        => 'Ошибка создания токена',
        'expiresToken'       => 'Время действия токена истекло',
        'tokenNotFound'      => 'Токен не обнаружен',
        'failedSendAuthCode' => 'Не удалось отправить код авторизации',
    ]
];

