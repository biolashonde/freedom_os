<?php
declare(strict_types=1);

class InstallController
{
    public function index(): void
    {
        view('install/index', [
            'title' => 'Install',
            'locked' => Installer::isLocked(),
            'requirements' => Installer::requirements(),
            'canInstall' => Installer::canInstall(),
            'result' => null,
        ]);
    }

    public function run(): void
    {
        try {
            $result = Installer::run($_POST);
            view('install/index', [
                'title' => 'Install complete',
                'locked' => true,
                'requirements' => Installer::requirements(),
                'canInstall' => false,
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            view('install/index', [
                'title' => 'Install',
                'locked' => Installer::isLocked(),
                'requirements' => Installer::requirements(),
                'canInstall' => Installer::canInstall(),
                'result' => null,
            ]);
        }
    }
}
