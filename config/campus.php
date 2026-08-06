<?php

return [
    'network_restriction' => filter_var(env('CAMPUS_NETWORK_RESTRICTION', false), FILTER_VALIDATE_BOOLEAN),

    'allowed_ips' => array_filter(array_map('trim', explode(',', env(
        'CAMPUS_ALLOWED_IPS',
        ''
    )))),
];
