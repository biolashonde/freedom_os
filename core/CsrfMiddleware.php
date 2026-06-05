<?php
declare(strict_types=1);

class CsrfMiddleware
{
    public function handle(): void
    {
        csrf_check();
    }
}
