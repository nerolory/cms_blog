<?php

return [
    'navigation' => [
        'content' => 'Контент',
        'access' => 'Доступ',
        'system' => 'Система',
    ],

    'posts' => [
        'navigation' => 'Посты',
        'model' => 'пост',
        'plural' => 'Посты',
        'tabs' => [
            'all' => 'Все',
            'pending' => 'На модерации',
        ],
        'actions' => [
            'approve' => 'Одобрить',
            'reject' => 'Отклонить',
            'rejection_reason' => 'Причина отклонения',
            'preview' => 'Предпросмотр',
        ],
        'fields' => [
            'title' => 'Заголовок',
            'slug' => 'Slug',
            'excerpt' => 'Превью',
            'body' => 'Текст',
            'status' => 'Статус',
            'visibility' => 'Видимость',
            'required_permission' => 'Требуемое право',
            'author' => 'Автор',
            'rejection_reason' => 'Причина отклонения',
            'published_at' => 'Дата публикации',
            'featured_image' => 'Превью',
            'background_image' => 'Фон',
            'theme_primary_color' => 'Основной цвет',
            'theme_accent_color' => 'Акцентный цвет',
            'content_opacity' => 'Непрозрачность контента',
            'editor_mode' => 'Режим редактора',
        ],
        'filters' => [
            'status' => 'Статус',
            'visibility' => 'Видимость',
            'author' => 'Автор',
        ],
        'moderation_log' => [
            'title' => 'История модерации',
            'created_at' => 'Дата',
            'action' => 'Действие',
            'actor' => 'Кто',
            'reason' => 'Причина',
            'actions' => [
                'submitted' => 'Отправлено',
                'approved' => 'Одобрено',
                'rejected' => 'Отклонено',
                'updated' => 'Обновлено',
                'restored' => 'Восстановлено',
            ],
        ],
        'seo' => [
            'title' => 'SEO',
            'description' => 'Мета-теги и Open Graph для поисковых систем и соцсетей.',
            'fields' => [
                'meta_title' => 'Meta title',
                'meta_description' => 'Meta description',
                'og_image' => 'OG image',
                'canonical_url' => 'Canonical URL',
                'robots' => 'Robots',
                'robots_auto' => 'Авто (по статусу и видимости)',
            ],
        ],
        'versions' => [
            'title' => 'Версии',
            'number' => '№',
            'author' => 'Автор',
            'created_at' => 'Создано',
            'restore' => 'Восстановить',
            'restored' => 'Версия восстановлена.',
        ],
    ],

    'users' => [
        'navigation' => 'Пользователи',
        'model' => 'пользователь',
        'plural' => 'Пользователи',
        'fields' => [
            'name' => 'Имя',
            'email' => 'Email',
            'password' => 'Пароль',
            'theme' => 'Тема',
            'avatar' => 'Аватар',
            'roles' => 'Роли',
            'email_verified' => 'Email подтверждён',
            'account_status' => 'Статус аккаунта',
            'token_balance' => 'Токены',
            'grant_amount' => 'Количество токенов',
            'grant_note' => 'Комментарий',
        ],
        'filters' => [
            'email_verified' => 'Email подтверждён',
            'email_verified_yes' => 'Подтверждён',
            'email_verified_no' => 'Не подтверждён',
            'account_status' => 'Статус аккаунта',
        ],
        'actions' => [
            'activate' => 'Активировать',
            'deactivate' => 'Деактивировать',
            'suspend' => 'Заблокировать',
            'grant_tokens' => 'Начислить токены',
        ],
        'notifications' => [
            'activated' => 'Пользователь активирован.',
            'deactivated' => 'Активация пользователя снята.',
            'suspended' => 'Пользователь заблокирован.',
            'tokens_granted' => 'Токены начислены.',
        ],
        'account_status' => [
            'pending' => 'Ожидает',
            'active' => 'Активен',
            'suspended' => 'Заблокирован',
        ],
    ],

    'mail' => [
        'navigation' => 'Почта',
        'title' => 'Настройки почты',
        'sections' => [
            'general' => 'Общие',
            'transport' => 'Доставка',
        ],
        'fields' => [
            'require_email_verification' => 'Требовать подтверждение email при регистрации',
            'from_address' => 'Адрес отправителя',
            'from_name' => 'Имя отправителя',
            'mode' => 'Режим',
            'preset' => 'Провайдер',
            'host' => 'SMTP-сервер',
            'port' => 'SMTP-порт',
            'scheme' => 'Шифрование',
            'username' => 'Логин SMTP',
            'password' => 'Пароль SMTP',
        ],
        'modes' => [
            'preset' => 'Предустановка',
            'smtp' => 'Свой SMTP',
        ],
        'schemes' => [
            'none' => 'Без шифрования',
        ],
        'actions' => [
            'save' => 'Сохранить',
            'send_test' => 'Отправить тест',
        ],
        'notifications' => [
            'saved' => 'Настройки почты сохранены.',
            'test_sent' => 'Тестовое письмо отправлено на :email.',
        ],
    ],

    'site' => [
        'navigation' => 'Сайт',
        'title' => 'Настройки сайта',
        'sections' => [
            'branding' => 'Брендинг',
            'branding_help' => 'Название отображается в шапке публичного сайта и в логотипе админки.',
        ],
        'fields' => [
            'site_name' => 'Название сайта',
            'site_name_help' => 'Кэшируется на год; при сохранении кэш сбрасывается автоматически.',
        ],
        'actions' => [
            'save' => 'Сохранить',
        ],
        'notifications' => [
            'saved' => 'Настройки сайта сохранены.',
        ],
    ],

    'search' => [
        'navigation' => 'Поиск',
        'title' => 'Настройки поиска',
        'sections' => [
            'driver' => 'Движок поиска',
            'driver_help' => 'Внешние движки включаются через Docker Compose profile и переменные SEARCH_*_ENABLED.',
        ],
        'fields' => [
            'driver' => 'Драйвер',
        ],
        'drivers' => [
            'database' => 'База данных (PostgreSQL full-text)',
            'elasticsearch' => 'Elasticsearch',
            'sphinx' => 'Sphinx',
            'solr' => 'Apache Solr',
            'ai' => 'AI (Python service)',
        ],
        'hints' => [
            'database_not_recommended' => 'Базовый режим, не рекомендуется для production.',
        ],
        'actions' => [
            'save' => 'Сохранить',
        ],
        'notifications' => [
            'saved' => 'Настройки поиска сохранены.',
        ],
    ],

    'roles' => [
        'navigation' => 'Роли',
    ],

    'categories' => [
        'navigation' => 'Категории',
    ],

    'tags' => [
        'navigation' => 'Теги',
    ],

    'comments' => [
        'navigation' => 'Комментарии',
        'actions' => [
            'hide' => 'Скрыть',
        ],
    ],

    'site_health' => [
        'navigation' => 'Состояние сайта',
        'title' => 'Диагностика сайта',
        'latest_report' => 'Последний отчёт',
        'completed_at' => 'Завершено: :date',
        'context' => 'Контекст',
        'empty' => 'Отчётов пока нет. Запустите проверку.',
        'stats' => [
            'critical' => 'Критичные',
            'warning' => 'Предупреждения',
            'passed' => 'Успешно',
        ],
        'actions' => [
            'run' => 'Запустить проверку',
        ],
        'notifications' => [
            'queued' => 'Проверка поставлена в очередь.',
        ],
    ],

    'site_templates' => [
        'navigation' => 'Шаблоны сайта',
        'model' => 'шаблон',
        'plural' => 'Шаблоны сайта',
        'fields' => [
            'slug' => 'Slug',
            'name' => 'Название',
            'view_prefix' => 'Префикс view',
            'view_prefix_help' => 'Например: themes.default',
            'is_active' => 'Активен',
            'is_default' => 'По умолчанию',
            'themes_count' => 'Тем',
        ],
        'actions' => [
            'activate' => 'Сделать активным',
        ],
        'notifications' => [
            'activated' => 'Шаблон активирован.',
        ],
        'themes' => [
            'title' => 'Темы',
            'fields' => [
                'slug' => 'Slug',
                'name' => 'Название',
                'bootstrap_theme' => 'Bootstrap theme',
                'body_class' => 'CSS-класс body',
                'css_entry' => 'CSS entry (Vite)',
                'is_default' => 'По умолчанию',
            ],
            'notifications' => [
                'deleted' => 'Тема удалена.',
            ],
        ],
    ],

    'moderation_sla' => [
        'pending' => 'На модерации',
        'pending_description' => 'Посты, ожидающие решения',
        'oldest' => 'Старейший в очереди',
        'sla_limit' => 'SLA: :minutes мин',
    ],

    'ai_orders' => [
        'navigation' => 'Заказы AI-анализа',
        'fields' => [
            'user' => 'Пользователь',
            'comment_count' => 'Комментариев',
            'tokens_required' => 'Токенов',
            'admin_note' => 'Заметка администратора',
        ],
        'status' => [
            'pending' => 'Ожидает',
            'approved' => 'Одобрен',
            'rejected' => 'Отклонён',
            'executing' => 'Выполняется',
            'completed' => 'Завершён',
            'failed' => 'Ошибка',
        ],
        'actions' => [
            'approve' => 'Одобрить',
            'reject' => 'Отклонить',
            'execute' => 'Выполнить',
            'execute_confirm' => 'Списать токены и записать демо-результат в кэш?',
            'auto_calculation' => 'Автоматический подсчёт',
            'auto_calculation_tooltip' => 'Функция вне демо-версии, свяжитесь с автором.',
        ],
        'notifications' => [
            'approved' => 'Заказ одобрен.',
            'rejected' => 'Заказ отклонён.',
            'executed' => 'Анализ выполнен, результат сохранён в кэш.',
        ],
    ],

    'token_packages' => [
        'navigation' => 'Пакеты токенов',
        'fields' => [
            'name' => 'Название',
            'token_amount' => 'Количество токенов',
            'price_cents' => 'Цена (копейки)',
            'currency' => 'Валюта',
            'sort_order' => 'Порядок',
            'is_active' => 'Активен',
        ],
    ],
];
