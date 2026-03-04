<?php

return [
    'project_id' => env('GOOGLE_CLOUD_PROJECT'),
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    'transport' => env('FIRESTORE_TRANSPORT', 'rest'),
    'api_endpoint' => env('FIRESTORE_API_ENDPOINT', 'firestore.googleapis.com:443'),
    'grpc_roots' => env('GRPC_DEFAULT_SSL_ROOTS_FILE_PATH'),
];
