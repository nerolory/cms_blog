<?php

return [
    'web' => [
        'title' => 'Посты',
        'default_page_title' => 'Посты',
        'create_title' => 'Новый пост',
        'create_heading' => 'Создать пост',
        'edit_title' => 'Редактирование',
        'edit_heading' => 'Редактировать пост',
        'back_to_list' => '← К списку',
        'back_to_post' => '← К посту',
        'empty' => 'Пока нет записей.',
        'author' => 'Автор: :name',
        'slug' => 'Slug: :slug',
        'save' => 'Сохранить',
        'update' => 'Обновить',
        'cancel' => 'Отмена',
        'edit' => 'Редактировать',
        'open_in_admin' => 'В админке',
    ],

    'preview' => [
        'action' => 'Предпросмотр',
        'banner' => 'Режим предпросмотра — эта страница не опубликована и не индексируется.',
        'back_to_edit' => 'Вернуться к редактированию',
    ],

    'versions' => [
        'title' => 'История версий',
        'hint' => 'Сохраняются последние 2 версии перед каждым обновлением. Восстановление — в админ-панели.',
    ],

    'fields' => [
        'title' => 'Заголовок',
        'slug_optional' => 'Slug (необязательно)',
        'slug_hint' => 'Оставьте пустым — slug сформируется из заголовка автоматически. Без пробелов: только латинские буквы, цифры, дефис (-) и нижнее подчёркивание (_). От 3 до 255 символов, как у заголовка.',
        'slug_forbidden_char' => 'Такой символ запрещён',
        'excerpt' => 'Краткое описание',
        'body' => 'Текст',
        'author' => 'Автор',
        'author_not_selected' => '— не выбран —',
        'visibility' => 'Видимость',
        'is_published' => 'Опубликован',
        'featured_image' => 'Превью-изображение',
        'background_image' => 'Фоновое изображение',
        'remove_image' => 'Удалить изображение',
        'theme_section' => 'Тема статьи',
        'use_article_theme' => 'Настроить тему статьи',
        'theme_primary_color' => 'Основной цвет',
        'theme_accent_color' => 'Акцентный цвет',
        'content_opacity' => 'Непрозрачность контента',
        'content_opacity_hint' => 'Минимум 50% — контент не может быть прозрачнее половины.',
        'theme_preview_title' => 'Пример заголовка',
        'theme_preview_text' => 'Так будет выглядеть панель с текстом поверх фона.',
        'editor_mode' => 'Режим редактора',
        'editor_mode_hint' => 'Простой — визуальный редактор: абзацы, картинки и форматирование как на странице. Про — исходный HTML-код, как в редакторе кода.',
    ],

    'editor_mode' => [
        'simple' => 'Простой',
        'pro' => 'Про',
    ],

    'legacy' => [
        'published' => 'Опубликован',
        'draft' => 'Черновик',
    ],

    'messages' => [
        'submitted_for_moderation' => 'Пост отправлен на модерацию.',
        'updated' => 'Пост обновлён.',
        'update_failed' => 'Не удалось обновить пост. Попробуйте позже.',
        'update_disabled_hint' => 'Измените поля формы, чтобы сохранить.',
        'too_many_updates' => 'Слишком много сохранений. Подождите минуту и попробуйте снова.',
        'deleted' => 'Пост удалён.',
        'delete_failed' => 'Не удалось удалить пост.',
    ],

    'validation' => [
        'title_required' => 'Укажите заголовок.',
        'body_required' => 'Укажите текст поста.',
        'excerpt_required' => 'Укажите превью поста.',
        'slug_unique' => 'Такой slug уже занят.',
        'slug_min' => 'Slug должен содержать не менее 3 символов.',
        'slug_max' => 'Slug не может быть длиннее 255 символов.',
        'slug_format' => 'Slug может содержать только латинские буквы, цифры, дефис и нижнее подчёркивание.',
        'title_min' => 'Заголовок должен содержать не менее 3 символов.',
        'title_max' => 'Заголовок не может быть длиннее 255 символов.',
        'user_id_exists' => 'Выбранный автор не найден.',
        'visibility_required' => 'Выберите видимость поста.',
        'visibility_invalid' => 'Недопустимая видимость поста.',
        'featured_image_image' => 'Превью должно быть изображением.',
        'featured_image_mimes' => 'Допустимые форматы превью: JPEG, PNG, GIF, WebP.',
        'featured_image_max' => 'Превью не должно превышать 15 МБ.',
        'background_image_image' => 'Фон должен быть изображением.',
        'background_image_mimes' => 'Допустимые форматы фона: JPEG, PNG, GIF, WebP.',
        'background_image_max' => 'Фоновое изображение не должно превышать 15 МБ.',
        'theme_color_format' => 'Цвет должен быть в формате #RRGGBB.',
        'content_opacity_min' => 'Непрозрачность контента не может быть меньше 50%.',
        'content_opacity_max' => 'Непрозрачность контента не может быть больше 100%.',
        'editor_mode_required' => 'Выберите режим редактора.',
        'content_image_required' => 'Выберите изображение для вставки.',
        'content_image_image' => 'Файл должен быть изображением.',
        'content_image_mimes' => 'Допустимые форматы: JPEG, PNG, GIF, WebP.',
        'content_image_max' => 'Изображение не должно превышать 15 МБ.',
    ],

    'exceptions' => [
        'empty_title' => 'Заголовок поста не может быть пустым.',
        'empty_slug' => 'Slug поста не может быть пустым.',
        'empty_body' => 'Текст поста не может быть пустым.',
        'invalid_visibility' => 'Недопустимая видимость поста: :visibility.',
        'missing_permission' => 'Для visibility permission укажите permission.',
        'unexpected_permission' => 'Permission указан для visibility без permission.',
        'missing_category' => 'Выберите категорию.',
    ],

    'toc' => [
        'title' => 'Содержание',
    ],

    'auth' => [
        'moderation_denied' => 'Недостаточно прав для модерации.',
    ],

    'status' => [
        'draft' => 'Черновик',
        'pending_moderation' => 'На модерации',
        'published' => 'Опубликована',
        'rejected' => 'Отклонена',
    ],

    'visibility' => [
        'guest' => 'Гости',
        'authenticated' => 'Авторизованные',
        'admin' => 'Администраторы',
        'permission' => 'По permission',
    ],
];
