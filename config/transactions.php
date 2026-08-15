<?php

return [
    'register_routes' => env('TRANSACTIONS_REGISTER_ROUTES', true),
    'route_prefix' => env('TRANSACTIONS_ROUTE_PREFIX', 'api'),
    'route_middleware' => ['api', 'auth:sanctum'],
    'transactions_table' => env('TRANSACTIONS_TABLE', 'transactions'),
    'reference_prefix' => env('TRANSACTION_REFERENCE_PREFIX', 'TXN-'),
];
