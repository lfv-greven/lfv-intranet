<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->dailyAt('06:00');
