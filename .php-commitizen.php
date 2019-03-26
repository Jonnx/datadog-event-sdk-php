<?php

return [
    'type' => [
        'lengthMin' => 1,
        'lengthMax' => 5,
        'acceptExtra' => false,
        'values' => ['feat', 'fix', 'docs', 'test', 'style'],
    ],
    'description' => [
        'lengthMin' => 1,
        'lengthMax' => 44,
    ],
    'subject' => [
        'lengthMin' => 1,
        'lengthMax' => 50,
    ],
    'body' => [
        'wrap' => 72,
    ],
    'footer' => [
        'wrap' => 72,
    ],
];