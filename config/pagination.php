<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Settings
    |--------------------------------------------------------------------------
    |
    | These values are used as defaults for API pagination throughout
    | the application.
    |
    */

    'per_page' => env('PAGINATION_PER_PAGE', 15),

    'max_per_page' => env('PAGINATION_MAX_PER_PAGE', 100),

];
