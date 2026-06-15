<?php

return [

    'default_driver' => env('SEARCH_DRIVER', 'database'),

    'backends' => [
        'elasticsearch' => [
            'enabled' => (bool) env('SEARCH_ELASTICSEARCH_ENABLED', false),
            'host' => env('SEARCH_ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
            'index' => env('SEARCH_ELASTICSEARCH_INDEX', 'posts'),
        ],
        'sphinx' => [
            'enabled' => (bool) env('SEARCH_SPHINX_ENABLED', false),
            'host' => env('SEARCH_SPHINX_HOST', 'sphinx'),
            'port' => (int) env('SEARCH_SPHINX_PORT', 9312),
        ],
        'solr' => [
            'enabled' => (bool) env('SEARCH_SOLR_ENABLED', false),
            'host' => env('SEARCH_SOLR_HOST', 'http://solr:8983'),
            'core' => env('SEARCH_SOLR_CORE', 'posts'),
        ],
        'ai' => [
            'enabled' => (bool) env('SEARCH_AI_ENABLED', false),
        ],
    ],

];
