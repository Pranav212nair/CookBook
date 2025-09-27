<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Signup</title>
  <link rel="stylesheet" href="./mystyle.css" />
</head>
<body class="login-body">
  <main class="auth-wrap">
    <section class="auth-card">
      <div class="auth-header">
        <div class="auth-logo" aria-hidden="true">OC</div>
        <div>
          <h1>Create your account</h1>
          <p>Join Online Cookbook to save and share your recipes</p>
        </div>
      </div>

      <form action="add_user.php" method="POST" class="auth-form" novalidate>
        <!-- Name -->
        <label class="auth-label" for="name">Full Name</label>
        <input class="auth-input" type="text" id="name" name="name" placeholder="Your name" required />

        <!-- Email -->
        <label class="auth-label" for="email">Email</label>
        <input class="auth-input" type="email" id="email" name="email" placeholder="you@example.com" required />

        <!-- Password -->
        <div class="auth-row">
          <label class="auth-label" for="password">Password</label>
          <small class="hint">(min 6 chars)</small>
        </div>
        <div class="password-field">
          <input class="auth-input" type="password" id="password" name="password" placeholder="Enter password" minlength="6" required />
          <button type="button" class="toggle-pass" aria-controls="password" aria-label="Show password">Show</button>
        </div>

        <!-- Confirm Password -->
        <div class="auth-row">
          <label class="auth-label" for="confirm_password">Confirm Password</label>
        </div>
        <div class="password-field">
          <input class="auth-input" type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" minlength="6" required />
          <button type="button" class="toggle-pass" aria-controls="confirm_password" aria-label="Show password">Show</button>
        </div>

        <!-- Dietary Preference -->
        <label class="auth-label" for="dietary_preference">Dietary Preference</label>
        <input class="auth-input" type="text" id="dietary_preference" name="dietary_preference" placeholder="Vegetarian, Vegan, etc." />

        <!-- Actions -->
        <div class="auth-actions">
          <button type="submit" class="btn-primary">Sign up</button>
          <button type="reset" class="btn-ghost">Clear</button>
        </div>
      </form>

      <div class="auth-footer">
        <p>Already have an account?
          <a class="link" href="login.php">Login here</a>
        </p>
        <p class="terms">
          By signing up you agree to our terms and privacy policy.
        </p>
      </div>
    </section>
  </main>

  <script>
    // Password confirmation check
    const form = document.querySelector("form");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");

    form.addEventListener("submit", function(e) {
      if (password.value !== confirmPassword.value) {
        e.preventDefault();
        alert("❌ Passwords do not match!");
      }
    });

    // Toggle show/hide password
    document.querySelectorAll(".toggle-pass").forEach(btn => {
      btn.addEventListener("click", () => {
        const input = document.getElementById(btn.getAttribute("aria-controls"));
        if (input.type === "password") {
          input.type = "text";
          btn.textContent = "Hide";
        } else {
          input.type = "password";
          btn.textContent = "Show";
        }
      });
    });
  </script>
</body>
</html>
