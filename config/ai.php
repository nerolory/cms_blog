<?php

return [
    'cooldown_days' => (int) env('AI_COOLDOWN_DAYS', 7),
    'auto_analysis_comment_threshold' => 1000,
    'tokens' => [
        'minimum' => 10,
        'per_comment' => 1,
    ],
];
