<div class="auth-wrap">
    <div class="auth-card">
        <div style="text-align:center;margin-bottom:1.75rem">
            <div class="logo-mark" style="margin:0 auto 0.75rem;width:40px;height:40px;font-size:1.2rem">🕊</div>
            <h1 style="font-size:1.5rem;margin-bottom:0.3rem">Create your account</h1>
            <p class="text-sm text-muted">Private by default. No email marketing.</p>
        </div>
        <form class="space-y-4" method="post" action="<?= e(base_url('/register')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="reg-name">Name</label>
                <input id="reg-name" class="form-input" name="name" value="<?= e(old('name')) ?>" required autocomplete="name" placeholder="First name or nickname">
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-email">Email</label>
                <input id="reg-email" class="form-input" name="email" type="email" value="<?= e(old('email')) ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-password">Password</label>
                <input id="reg-password" class="form-input" name="password" type="password" minlength="8" required autocomplete="new-password" placeholder="At least 8 characters">
            </div>
            <button class="btn btn-primary btn-full" type="submit" style="margin-top:0.5rem">Create account</button>
        </form>
        <p class="text-sm text-muted" style="text-align:center;margin-top:1.25rem">
            Already registered? <a href="<?= e(base_url('/login')) ?>" style="color:var(--green-text);font-weight:600">Log in</a>
        </p>
    </div>
</div>
