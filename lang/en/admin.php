<?php

return [
    'navigation' => [
        'content' => 'Content',
        'access' => 'Access',
        'system' => 'System',
    ],

    'posts' => [
        'navigation' => 'Posts',
        'model' => 'post',
        'plural' => 'Posts',
        'tabs' => [
            'all' => 'All',
            'pending' => 'Pending moderation',
        ],
        'actions' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
            'rejection_reason' => 'Rejection reason',
            'preview' => 'Preview',
        ],
        'fields' => [
            'title' => 'Title',
            'slug' => 'Slug',
            'excerpt' => 'Excerpt',
            'body' => 'Body',
            'status' => 'Status',
            'visibility' => 'Visibility',
            'author' => 'Author',
            'rejection_reason' => 'Rejection reason',
            'published_at' => 'Published at',
            'required_permission' => 'Required permission',
            'featured_image' => 'Featured image',
            'background_image' => 'Background image',
            'theme_primary_color' => 'Primary color',
            'theme_accent_color' => 'Accent color',
            'content_opacity' => 'Content opacity',
            'editor_mode' => 'Editor mode',
        ],
        'filters' => [
            'status' => 'Status',
            'visibility' => 'Visibility',
            'author' => 'Author',
        ],
        'moderation_log' => [
            'title' => 'Moderation log',
            'created_at' => 'Date',
            'action' => 'Action',
            'actor' => 'Actor',
            'reason' => 'Reason',
            'actions' => [
                'submitted' => 'Submitted',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'updated' => 'Updated',
            ],
        ],
        'seo' => [
            'title' => 'SEO',
            'description' => 'Meta tags and Open Graph for search engines and social sharing.',
            'fields' => [
                'meta_title' => 'Meta title',
                'meta_description' => 'Meta description',
                'og_image' => 'OG image',
                'canonical_url' => 'Canonical URL',
                'robots' => 'Robots',
                'robots_auto' => 'Auto (from status and visibility)',
            ],
        ],
    ],

    'users' => [
        'navigation' => 'Users',
        'model' => 'user',
        'plural' => 'Users',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'theme' => 'Theme',
            'avatar' => 'Avatar',
            'roles' => 'Roles',
            'email_verified' => 'Email verified',
        ],
        'filters' => [
            'email_verified' => 'Email verified',
            'email_verified_yes' => 'Verified',
            'email_verified_no' => 'Not verified',
        ],
        'actions' => [
            'activate' => 'Activate',
            'deactivate' => 'Deactivate',
        ],
        'notifications' => [
            'activated' => 'User activated.',
            'deactivated' => 'User deactivated.',
        ],
    ],

    'mail' => [
        'navigation' => 'Mail',
        'title' => 'Mail settings',
        'sections' => [
            'general' => 'General',
            'transport' => 'Delivery',
        ],
        'fields' => [
            'require_email_verification' => 'Require email verification on registration',
            'from_address' => 'From address',
            'from_name' => 'From name',
            'mode' => 'Mode',
            'preset' => 'Provider',
            'host' => 'SMTP host',
            'port' => 'SMTP port',
            'scheme' => 'Encryption',
            'username' => 'SMTP username',
            'password' => 'SMTP password',
        ],
        'modes' => [
            'preset' => 'Preset',
            'smtp' => 'Custom SMTP',
        ],
        'schemes' => [
            'none' => 'No encryption',
        ],
        'actions' => [
            'save' => 'Save',
            'send_test' => 'Send test',
        ],
        'notifications' => [
            'saved' => 'Mail settings saved.',
            'test_sent' => 'Test email sent to :email.',
        ],
    ],

    'search' => [
        'navigation' => 'Search',
        'title' => 'Search settings',
        'sections' => [
            'driver' => 'Search driver',
            'driver_help' => 'External engines are enabled via Docker Compose profiles and SEARCH_*_ENABLED variables.',
        ],
        'fields' => [
            'driver' => 'Driver',
        ],
        'drivers' => [
            'database' => 'Database (PostgreSQL full-text)',
            'elasticsearch' => 'Elasticsearch',
            'sphinx' => 'Sphinx',
            'solr' => 'Apache Solr',
            'ai' => 'AI (Python service)',
        ],
        'hints' => [
            'database_not_recommended' => 'Basic mode, not recommended for production.',
        ],
        'actions' => [
            'save' => 'Save',
        ],
        'notifications' => [
            'saved' => 'Search settings saved.',
        ],
    ],

    'roles' => [
        'navigation' => 'Roles',
    ],

    'site_templates' => [
        'navigation' => 'Site templates',
        'model' => 'template',
        'plural' => 'Site templates',
        'fields' => [
            'slug' => 'Slug',
            'name' => 'Name',
            'view_prefix' => 'View prefix',
            'view_prefix_help' => 'Example: themes.default',
            'is_active' => 'Active',
            'is_default' => 'Default',
            'themes_count' => 'Themes',
        ],
        'actions' => [
            'activate' => 'Set active',
        ],
        'notifications' => [
            'activated' => 'Template activated.',
        ],
        'themes' => [
            'title' => 'Themes',
            'fields' => [
                'slug' => 'Slug',
                'name' => 'Name',
                'bootstrap_theme' => 'Bootstrap theme',
                'body_class' => 'Body CSS class',
                'css_entry' => 'CSS entry (Vite)',
                'is_default' => 'Default',
            ],
            'notifications' => [
                'deleted' => 'Theme deleted.',
            ],
        ],
    ],
];
