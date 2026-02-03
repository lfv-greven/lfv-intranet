<?php

use App\Console\Commands\Mattermost\SyncVereinsfliegerUsers;
use App\Console\Commands\Vf\CheckMotortimes;
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->dailyAt('06:00');

Schedule::command(CheckMotortimes::class)
    ->between('08:00', '22:00')
    ->onOneServer()
    ->hourly();

Schedule::command(SyncVereinsfliegerUsers::class)
    ->onOneServer()
    ->twiceDaily();
