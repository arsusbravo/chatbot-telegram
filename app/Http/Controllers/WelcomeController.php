<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'wallets' => collect(config('payment.wallets'))->filter(fn ($w) => !empty($w['address']))->values(),
            'packages' => config('payment.packages'),
        ]);
    }
}