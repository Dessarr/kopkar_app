<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register password conversion command
Artisan::command('passwords:convert', function () {
    $this->call('passwords:convert');
})->purpose('Konversi password dari CodeIgniter hash ke Laravel bcrypt');
