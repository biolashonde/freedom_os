<?php
declare(strict_types=1);

class DonationController
{
    public function show(): void
    {
        view('donations/show', [
            'title' => 'Donate',
            'donation' => DonationSettings::get(),
        ]);
    }
}
