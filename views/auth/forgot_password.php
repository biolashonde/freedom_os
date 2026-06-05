<section class="auth-page">
    <div class="auth-card">
        <div class="auth-card__header">
            <p class="auth-eyebrow">Account recovery</p>
            <h1>Reset your password</h1>
            <p>Enter your email and FreedomOS will send a secure reset link if the account exists.</p>
        </div>

        <form method="post" action="<?= e(base_url('/forgot-password')) ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="form-label" for="forgot-email">Email</label>
                <input id="forgot-email" class="form-input" name="email" type="email" required value="<?= e(old('email')) ?>">
            </div>
            <button class="btn btn-primary btn-full" type="submit">Send reset link</button>
        </form>

        <p class="auth-switch"><a href="<?= e(base_url('/login')) ?>">Back to login</a></p>
    </div>
</section>
