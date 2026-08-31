<?php

return [
    'enabled' => filter_var($_ENV['ENABLE_RECAPTCHA'] ?? getenv('ENABLE_RECAPTCHA') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'site_key' => (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY') ?: '6LeyN9QsAAAAAMNFUnWk1ub-Z750XgwbSNN-wwFx'),
    'secret_key' => (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? getenv('RECAPTCHA_SECRET_KEY') ?: '6LeyN9QsAAAAADZOKI5gwIO6a9WSw2niok2l2gDF'),
];
