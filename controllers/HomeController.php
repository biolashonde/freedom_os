<?php
declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        view('home/index', [
            'title' => 'Private recovery support',
            'donation' => DonationSettings::get(),
        ]);
    }
}
