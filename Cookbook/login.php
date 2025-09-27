<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="./mystyle.css" />
</head>
<body class="login-body">
  <main class="auth-wrap">
    <section class="auth-card">
      <div class="auth-header">
        <div class="auth-logo" aria-hidden="true">OC</div>
        <div>
          <h1>Welcome back</h1>
          <p>Sign in to continue to your account</p>
        </div>
      </div>

      <form action="check_login.php" method="POST" class="auth-form" novalidate>
        <label class="auth-label" for="email">Email</label>
        <input class="auth-input" type="email" id="email" name="email" placeholder="you@example.com" required />

        <div class="auth-row">
          <label class="auth-label" for="password">Password</label>
          <small class="hint">(min 6 chars)</small>
        </div>
        <div class="password-field">
          <input class="auth-input" type="password" id="password" name="password" placeholder="Enter your password" minlength="6" required />
          <button type="button" class="toggle-pass" aria-controls="password" aria-label="Show password">Show</button>
        </div>

        <div class="auth-meta">
          <label class="remember">
            <input type="checkbox" name="remember" value="1" />
            Remember me
          </label>
          <a class="link-small" href="#">Forgot?</a>
        </div>

        <div class="auth-actions">
          <button type="submit" class="btn-primary">Sign in</button>
          <button type="button" class="btn-ghost" id="demo-fill">Demo</button>
        </div>
      </form>

      <div class="auth-footer">
        <p>Don't have an account?
          <a class="link" href="signup.php">Create account</a>
        </p>
        <p class="terms">
          By signing in you agree to our terms and privacy policy.
        </p>
      </div>
    </section>
  </main>

  <script src="../assets/js/login.js"></script>
</body>
</html>