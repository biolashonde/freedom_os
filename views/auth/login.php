<div class="auth-wrap">
    <div class="auth-card">
        <div style="text-align:center;margin-bottom:1.75rem">
            <div class="logo-mark" style="margin:0 auto 0.75rem;width:40px;height:40px;font-size:1.2rem">🕊</div>
            <h1 style="font-size:1.5rem;margin-bottom:0.3rem">Welcome back</h1>
            <p class="text-sm text-muted">Continue where you left off.</p>
        </div>
        <form class="space-y-4" method="post" action="<?= e(base_url('/login')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="login-email">Email</label>
                <input id="login-email" class="form-input" name="email" type="email" value="<?= e(old('email')) ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="login-password">Password</label>
                <input id="login-password" class="form-input" name="password" type="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary btn-full" type="submit" style="margin-top:0.5rem">Log in</button>
        </form>
        <p class="text-sm text-muted" style="text-align:center;margin-top:1.25rem">
            New here? <a href="<?= e(base_url('/register')) ?>" style="color:var(--green-text);font-weight:600">Create your account</a>
        </p>
    </div>
</div>
