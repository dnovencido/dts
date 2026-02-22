<?php

    // Get current URL path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    /* Normalize: remove multiple slashes */
    $path = preg_replace('#/+#','/', $path);

    /* Add trailing slash if missing */
    if (substr($path, -1) !== '/') {
        $path .= '/';
    }

    /* Detect type */
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