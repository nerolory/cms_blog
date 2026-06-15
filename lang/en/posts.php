<?php

return [
    'web' => [
        'title' => 'Posts',
        'default_page_title' => 'Posts',
        'create_title' => 'New post',
        'create_heading' => 'Create post',
        'edit_title' => 'Edit',
        'edit_heading' => 'Edit post',
        'back_to_list' => '← Back to list',
        'back_to_post' => '← Back to post',
        'empty' => 'No posts yet.',
        'author' => 'Author: :name',
        'slug' => 'Slug: :slug',
        'save' => 'Save',
        'update' => 'Update',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'open_in_admin' => 'Open in admin',
    ],

    'preview' => [
        'action' => 'Preview',
        'banner' => 'Preview mode — this page is not published and will not be indexed.',
        'back_to_edit' => 'Back to editing',
    ],

    'versions' => [
        'title' => 'Version history',
        'hint' => 'The last 2 versions are kept before each update. Restore from the admin panel.',
    ],

    'fields' => [
        'title' => 'Title',
        'slug_optional' => 'Slug (optional)',
        'slug_hint' => 'Leave blank to generate a slug from the title automatically. No spaces: Latin letters, digits, hyphen (-), and underscore (_) only. From 3 to 255 characters, same as the title.',
        'slug_forbidden_char' => 'This character is not allowed',
        'excerpt' => 'Excerpt',
        'body' => 'Body',
        'author' => 'Author',
        'author_not_selected' => '— not selected —',
        'is_published' => 'Published',
        'featured_image' => 'Featured image',
        'background_image' => 'Background image',
        'remove_image' => 'Remove image',
        'theme_section' => 'Article theme',
        'use_article_theme' => 'Customize article theme',
        'theme_primary_color' => 'Primary color',
        'theme_accent_color' => 'Accent color',
        'content_opacity' => 'Content opacity',
        'content_opacity_hint' => 'Minimum 50% — content cannot be more than half transparent.',
        'theme_preview_title' => 'Sample heading',
        'theme_preview_text' => 'This is how the content panel will look over the background.',
        'editor_mode' => 'Editor mode',
        'editor_mode_hint' => 'Simple — visual editor: paragraphs, images and formatting as on the page. Pro — raw HTML source code, like in a code editor.',
    ],

    'editor_mode' => [
        'simple' => 'Simple',
        'pro' => 'Pro',
    ],

    'legacy' => [
        'published' => 'Published',
        'draft' => 'Draft',
    ],

    'messages' => [
        'submitted_for_moderation' => 'Post submitted for moderation.',
        'updated' => 'Post updated.',
        'update_failed' => 'Could not update the post. Please try again later.',
        'update_disabled_hint' => 'Change form fields to enable saving.',
        'too_many_updates' => 'Too many saves. Please wait a minute and try again.',
        'deleted' => 'Post deleted.',
        'delete_failed' => 'Could not delete the post.',
    ],

    'validation' => [
        'title_required' => 'Please enter a title.',
        'body_required' => 'Please enter the post body.',
        'excerpt_required' => 'Please enter the post excerpt.',
        'slug_unique' => 'This slug is already taken.',
        'slug_min' => 'The slug must be at least 3 characters.',
        'slug_max' => 'The slug may not be greater than 255 characters.',
        'slug_format' => 'The slug may only contain Latin letters, digits, hyphens, and underscores.',
        'title_min' => 'The title must be at least 3 characters.',
        'title_max' => 'The title may not be greater than 255 characters.',
        'user_id_exists' => 'The selected author was not found.',
        'featured_image_image' => 'The featured image must be an image file.',
        'featured_image_mimes' => 'Allowed featured image formats: JPEG, PNG, GIF, WebP.',
        'featured_image_max' => 'The featured image may not be greater than 15 MB.',
        'background_image_image' => 'The background must be an image file.',
        'background_image_mimes' => 'Allowed background formats: JPEG, PNG, GIF, WebP.',
        'background_image_max' => 'The background image may not be greater than 15 MB.',
        'theme_color_format' => 'The color must be in #RRGGBB format.',
        'content_opacity_min' => 'Content opacity cannot be less than 50%.',
        'content_opacity_max' => 'Content opacity cannot be greater than 100%.',
        'editor_mode_required' => 'Please select an editor mode.',
        'content_image_required' => 'Please choose an image to upload.',
        'content_image_image' => 'The file must be an image.',
        'content_image_mimes' => 'Allowed formats: JPEG, PNG, GIF, WebP.',
        'content_image_max' => 'The image may not be greater than 15 MB.',
    ],

    'exceptions' => [
        'empty_title' => 'Post title cannot be empty.',
        'empty_slug' => 'Post slug cannot be empty.',
        'empty_body' => 'Post body cannot be empty.',
        'invalid_visibility' => 'Invalid post visibility: :visibility.',
        'missing_permission' => 'A permission is required when visibility is set to permission.',
        'unexpected_permission' => 'A permission may only be set when visibility is permission.',
    ],

    'auth' => [
        'moderation_denied' => 'Insufficient permissions for moderation.',
    ],

    'status' => [
        'draft' => 'Draft',
        'pending_moderation' => 'Pending moderation',
        'published' => 'Published',
        'rejected' => 'Rejected',
    ],

    'visibility' => [
        'guest' => 'Guests',
        'authenticated' => 'Authenticated users',
        'admin' => 'Administrators',
        'permission' => 'By permission',
    ],
];
