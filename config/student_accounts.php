<?php

return [
    // Domain and separator can be changed here when the school chooses another format.
    'email_domain' => env('STUDENT_EMAIL_DOMAIN', 'gmail.com'),
    'email_separator' => env('STUDENT_EMAIL_SEPARATOR', ''),
    'password_date_format' => 'Y-m-d',
];
