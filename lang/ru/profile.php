<?php

return [
    'title' => 'Профиль',
    'heading' => 'Настройки профиля',

    'fields' => [
        'name' => 'Имя',
        'email' => 'Email',
        'password' => 'Новый пароль',
        'password_confirmation' => 'Подтверждение пароля',
        'password_hint' => 'Оставьте пустым, если не меняете',
        'theme' => 'Тема оформления',
        'avatar' => 'Аватар',
        'avatar_hint' => 'JPEG, PNG, GIF или WebP до 30 МБ. Перед сохранением можно обрезать; на сервере изображение сжимается.',
    ],

    'avatar_crop' => [
        'title' => 'Обрезка аватара',
        'cancel' => 'Отмена',
        'save' => 'Сохранить',
    ],

    'themes' => [
        'default' => 'По умолчанию',
        'light' => 'Светлая',
        'dark' => 'Тёмная',
        'minimal' => 'Минимальная',
    ],

    'actions' => [
        'save' => 'Сохранить',
        'upload_avatar' => 'Загрузить аватар',
        'delete_avatar' => 'Удалить аватар',
    ],

    'messages' => [
        'save_disabled_hint' => 'Сначала внесите изменения в профиль',
        'updated' => 'Профиль обновлён.',
        'avatar_updated' => 'Аватар обновлён.',
        'avatar_deleted' => 'Аватар удалён.',
        'avatar_not_recognized' => 'Фото не распознано. Файл недоступен — загрузите изображение заново.',
    ],

    'validation' => [
        'name_required' => 'Укажите имя.',
        'theme_required' => 'Выберите тему.',
        'avatar_required' => 'Выберите файл аватара.',
        'avatar_image' => 'Файл должен быть изображением.',
        'avatar_mimes' => 'Допустимые форматы: JPEG, PNG, GIF, WebP.',
        'avatar_max' => 'Размер файла не должен превышать 30 МБ.',
        'avatar_uploaded' => 'Не удалось загрузить файл. Проверьте формат и размер (до 30 МБ).',
    ],
];
