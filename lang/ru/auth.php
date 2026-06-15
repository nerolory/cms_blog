<?php

return [
    'failed' => 'Неверный email или пароль.',
    'registered' => 'Регистрация прошла успешно.',
    'registered_verify' => 'Регистрация прошла успешно. Подтвердите email.',
    'registered_pending' => 'Регистрация прошла успешно. Ожидайте активации администратором.',

    'login' => [
        'title' => 'Вход',
        'heading' => 'Войти',
        'email' => 'Email',
        'password' => 'Пароль',
        'remember' => 'Запомнить меня',
        'submit' => 'Войти',
        'no_account' => 'Нет аккаунта?',
        'register_link' => 'Зарегистрироваться',
    ],

    'register' => [
        'title' => 'Регистрация',
        'heading' => 'Создать аккаунт',
        'name' => 'Имя',
        'email' => 'Email',
        'password' => 'Пароль',
        'password_confirmation' => 'Подтверждение пароля',
        'submit' => 'Зарегистрироваться',
        'has_account' => 'Уже есть аккаунт?',
        'login_link' => 'Войти',
    ],

    'validation' => [
        'name_required' => 'Укажите имя.',
        'email_required' => 'Укажите email.',
        'email_taken' => 'Этот email уже занят.',
        'password_required' => 'Укажите пароль.',
        'password_confirmed' => 'Пароли не совпадают.',
    ],

    'verification' => [
        'title' => 'Подтверждение email',
        'heading' => 'Подтвердите email',
        'intro' => 'Мы отправили ссылку на ваш email. Перейдите по ней, чтобы активировать аккаунт.',
        'resend' => 'Отправить письмо повторно',
        'sent' => 'Новая ссылка отправлена на ваш email.',
        'verified' => 'Email успешно подтверждён.',
        'required' => 'Подтвердите email для доступа к этому разделу.',
    ],

    'pending' => [
        'title' => 'Ожидание активации',
        'heading' => 'Аккаунт ожидает активации',
        'intro' => 'Администратор должен активировать ваш аккаунт. Попробуйте войти позже.',
        'required' => 'Аккаунт не активирован администратором.',
    ],

    'account' => [
        'suspended' => 'Ваш аккаунт заблокирован. Обратитесь к администратору.',
    ],

    'password' => [
        'forgot_title' => 'Восстановление пароля',
        'forgot_heading' => 'Забыли пароль?',
        'reset_title' => 'Новый пароль',
        'reset_heading' => 'Задайте новый пароль',
        'email' => 'Email',
        'new_password' => 'Новый пароль',
        'confirm_password' => 'Подтверждение пароля',
        'submit' => 'Отправить',
        'back_to_login' => '← К входу',
    ],
];
