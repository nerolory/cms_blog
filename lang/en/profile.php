<?php

return [
    'title' => 'Profile',
    'heading' => 'Profile settings',

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'New password',
        'password_confirmation' => 'Confirm password',
        'password_hint' => 'Leave blank to keep current password',
        'theme' => 'Theme',
        'avatar' => 'Avatar',
        'avatar_hint' => 'JPEG, PNG, GIF, or WebP up to 30 MB. Crop before save; the server compresses the image.',
    ],

    'avatar_crop' => [
        'title' => 'Crop avatar',
        'cancel' => 'Cancel',
        'save' => 'Save',
    ],

    'themes' => [
        'default' => 'Default',
        'light' => 'Light',
        'dark' => 'Dark',
        'minimal' => 'Minimal',
    ],

    'actions' => [
        'save' => 'Save',
        'upload_avatar' => 'Upload avatar',
        'delete_avatar' => 'Remove avatar',
    ],

    'messages' => [
        'save_disabled_hint' => 'Make changes to your profile first',
        'updated' => 'Profile updated.',
        'avatar_updated' => 'Avatar updated.',
        'avatar_deleted' => 'Avatar removed.',
        'avatar_not_recognized' => 'Photo not recognized. The file is unavailable — please upload again.',
    ],

    'validation' => [
        'name_required' => 'Please enter your name.',
        'theme_required' => 'Please select a theme.',
        'avatar_required' => 'Please choose an avatar file.',
        'avatar_image' => 'The file must be an image.',
        'avatar_mimes' => 'Allowed formats: JPEG, PNG, GIF, WebP.',
        'avatar_max' => 'The file size must not exceed 30 MB.',
        'avatar_uploaded' => 'The file could not be uploaded. Check the format and size (up to 30 MB).',
    ],
];
