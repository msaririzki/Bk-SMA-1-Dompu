<?php

return [
    'disk' => env('BACKUP_DISK', 's3'),
    'password' => env('BACKUP_PASSWORD'),
    'retention' => [
        'daily' => (int) env('BACKUP_RETENTION_DAILY', 7),
        'weekly' => (int) env('BACKUP_RETENTION_WEEKLY', 4),
        'monthly' => (int) env('BACKUP_RETENTION_MONTHLY', 6),
    ],
];
