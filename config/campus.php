<?php

return [
    'network_restriction' => filter_var(env('CAMPUS_NETWORK_RESTRICTION', false), FILTER_VALIDATE_BOOLEAN),

    'allowed_ips' => array_filter(array_map('trim', explode(',', env(
        'CAMPUS_ALLOWED_IPS',
        '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16'
    )))),
];
