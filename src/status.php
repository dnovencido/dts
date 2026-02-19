<?php

    // Get current URL path
    $path = strtolower(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    // Detect document type from URL
    if (strpos($path, '/incoming/') !== false) {
        $type = 'incoming';
    } elseif (strpos($path, '/outgoing/') !== false) {
        $type = 'outgoing';
    } else {
        $type = '';
    }

    // Status configuration
    $status_config = [
        'incoming' => [
            'options' => [
                'pending'  => 'Pending',
                'received' => 'Received',
            ]
        ],
        'outgoing' => [
            'options' => [
                'pending'  => 'Pending',
                'released' => 'Released',
                'received' => 'Received',
            ]
        ]
    ];
?>