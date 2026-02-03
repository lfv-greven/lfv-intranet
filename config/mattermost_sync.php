<?php

$defaultStatuses = [
    'fördernd',
    'aktiv',
    'Modellflug - nicht aktiv',
    'passiv',
    'Modellflieger',
    'Ehrenmitglied',
];

$statusEnv = env('MATTERMOST_SYNC_STATUSES');
$statusSource = is_string($statusEnv) && trim($statusEnv) !== '' ? $statusEnv : implode(',', $defaultStatuses);

return [
    'allowed_statuses' => array_values(array_filter(
        array_map('trim', explode(',', (string) $statusSource)),
        fn ($value) => $value !== ''
    )),

    'password_length' => (int) env('MATTERMOST_SYNC_PASSWORD_LENGTH', 8),

    'bcc_email' => env('MATTERMOST_SYNC_BCC_EMAIL'),

    'per_page' => (int) env('MATTERMOST_SYNC_PER_PAGE', 200),
];
