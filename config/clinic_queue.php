<?php

return [
    'call_grace_minutes' => (int) env('QUEUE_CALL_GRACE_MINUTES', 5),
    'max_recalls' => (int) env('QUEUE_MAX_RECALLS', 2),
    'nearly_next_threshold' => (int) env('QUEUE_NEARLY_NEXT_THRESHOLD', 1),
    'patient_poll_seconds' => (int) env('QUEUE_PATIENT_POLL_SECONDS', 20),
    'staff_poll_seconds' => (int) env('QUEUE_STAFF_POLL_SECONDS', 30),
];
