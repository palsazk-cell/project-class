<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('success.php');
}

$username = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'درخواست معتبر نیست. صفحه را تازه‌سازی کن و دوباره تلاش کن.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
            $error = 'نام کاربری باید ۳ تا ۳۰ نویسه انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.';
        } elseif (strlen($password) < 8) {
            $error = 'رمز عبور باید حداقل ۸ نویسه داشته باشد.';
        } elseif ($password !== $confirmPassword) {
            $error = 'تکرار رمز عبور با رمز عبور یکسان نیست.';
        } else {
            try {
                $statement = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
                $statement->execute([
                    ':username' => $username,
                    ':password' => $password,
                ]);
                set_flash('ثبت‌نام با موفقیت انجام شد. حالا وارد حساب شو.');
                redirect('login.php');
            } catch (PDOException $exception) {
                if (str_contains(strtolower($exception->getMessage()), 'unique')) {
                    $error = 'این نام کاربری قبلاً ثبت شده است.';
                } else {
                    $error = 'ثبت‌نام انجام نشد. دوباره تلاش کن.';
                }
            }
        }
    }
}
$flash = get_flash();
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ثبت‌نام | حساب کاربری</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="page-wrap">
    <section class="auth-card">
        <div class="brand-mark" aria-hidden="true">+</div>
        <p class="eyebrow">شروع کنید</p>
        <h1>ساخت حساب کاربری</h1>
        <p class="subtitle">اطلاعاتت را وارد کن تا حساب جدید بسازی.</p>

        <?php if ($flash): ?>
            <div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="username">نام کاربری</label>
            <input id="username" name="username" type="text" value="<?= e($username) ?>"
                   placeholder="مثلاً parsa123" minlength="3" maxlength="30"
                   pattern="[A-Za-z0-9_.-]{3,30}" autocomplete="username" required>

            <label for="password">رمز عبور</label>
            <input id="password" name="password" type="password" placeholder="حداقل ۸ نویسه"
                   minlength="8" autocomplete="new-password" required>

            <label for="confirm_password">تکرار رمز عبور</label>
            <input id="confirm_password" name="confirm_password" type="password"
                   placeholder="رمز عبور را دوباره وارد کن" minlength="8"
                   autocomplete="new-password" required>

            <button class="button" type="submit">ثبت‌نام</button>
        </form>
        <p class="switch-link">قبلاً حساب ساخته‌ای؟ <a href="login.php">ورود به حساب</a></p>
    </section>
    <p class="footer-note">پروژه آموزشی ثبت‌نام و ورود با PHP و SQLite</p>
</main>
</body>
</html>
