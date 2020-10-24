<?php

use C14r\Directus\Invoices\Endpoints\PDF;
use C14r\Directus\Invoices\Endpoints\Import;

return [
    '/pdf/{id}' => [
        'method' => 'GET',
        'handler' => PDF::class
    ],
    '/import' => [
        'method' => 'GET',
        'handler' => Import::class
    ],
];
