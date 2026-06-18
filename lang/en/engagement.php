<?php

return [
    'share' => [
        'label' => 'Share',
    ],
    'views' => ':count views',
    'reading_time' => ':minutes min read',
    'comment' => [
        'status' => [
            'visible' => 'Visible',
            'hidden' => 'Hidden',
        ],
        'title' => 'Comments',
        'placeholder' => 'Write a comment…',
        'reply_placeholder' => 'Reply…',
        'submit' => 'Submit',
        'reply' => 'Reply',
        'reply_to' => 'in reply to @:name',
        'show_replies' => 'Show replies (:count)',
        'load_more' => 'Load more comments',
        'load_more_replies' => 'Show more replies',
        'empty' => 'No comments yet.',
        'login_required' => 'Sign in to leave a comment.',
        'messages' => [
            'created' => 'Comment added.',
            'deleted' => 'Comment deleted.',
        ],
        'errors' => [
            'empty_body' => 'Comment cannot be empty.',
            'parent_not_found' => 'Parent comment not found.',
            'parent_mismatch' => 'Comment belongs to another post.',
            'max_depth' => 'Maximum reply depth is 2.',
        ],
    ],
    'reaction' => [
        'like' => 'Like',
        'dislike' => 'Dislike',
        'love' => 'Love',
        'laugh' => 'Laugh',
        'wow' => 'Wow',
        'sad' => 'Sad',
        'angry' => 'Angry',
        'errors' => [
            'invalid_type' => 'Invalid reaction type.',
        ],
    ],
];
