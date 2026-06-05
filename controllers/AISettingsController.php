<?php
declare(strict_types=1);

class AISettingsController
{
    public function edit(): void
    {
        view('settings/ai', [
            'title' => 'AI Keys',
            'settings' => UserAISettings::get(Auth::id()),
        ]);
    }

    public function update(): void
    {
        UserAISettings::save(Auth::id(), $_POST);
        flash('success', 'AI settings saved. Blank key fields were kept unchanged.');
        redirect('/settings/ai');
    }
}
