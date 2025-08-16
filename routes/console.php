<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendamento da newsletter semanal - todas as segundas-feiras às 9h
Schedule::command('newsletter:enviar-semanal')
    ->weeklyOn(1, '09:00')  // 1 = Segunda-feira, 09:00 = 9h da manhã
    ->withoutOverlapping()
    ->runInBackground()
    ->emailOutputOnFailure(config('mail.admin_email', env('MAIL_ADMIN_EMAIL', 'lucas.rodrigues@team.inovcorp.com')));
