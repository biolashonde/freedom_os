<section class="auth-page">
    <div class="auth-card">
        <div class="auth-card__header">
            <p class="auth-eyebrow">Account recovery</p>
            <h1>Choose a new password</h1>
            <p>Use a strong password you do not use anywhere else.</p>
        </div>

        <form method="post" action="<?= e(base_url('/reset-password/' . $token)) ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="form-label" for="reset-password">New password</label>
                <input id="reset-password" class="form-input" name="password" type="password" minlength="8" required autocomplete="new-password">
            </div>
            <div>
                <label class="form-label" for="reset-confirm">Confirm password</label>
                <input id="reset-confirm" class="form-input" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
            </div>
            <button class="btn btn-primary btn-full" type="submit">Update password</button>
        </form>
    </div>
</section>
