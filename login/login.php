<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('success.php');
}

$username = '';
$error = '';
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'درخواست معتبر نیست. صفحه را تازه‌سازی کن و دوباره تلاش کن.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $statement = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
        $statement->execute([':username' => $username]);
        $user = $statement->fetch();

        if ($user && $password === $user['password']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            redirect('success.php');
        }
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود | حساب کاربری</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="page-wrap">
    <section class="auth-card">
        <div class="brand-mark" aria-hidden="true">↗</div>
        <p class="eyebrow">خوش برگشتی</p>
        <h1>ورود به حساب</h1>
        <p class="subtitle">نام کاربری و رمز عبورت را وارد کن.</p>

        <?php if ($flash): ?>
            <div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="username">نام کاربری</label>
            <input id="username" name="username" type="text" value="<?= e($username) ?>"
                   placeholder="نام کاربری" autocomplete="username" required>

            <label for="password">رمز عبور</label>
            <input id="password" name="password" type="password" placeholder="رمز عبور"
                   autocomplete="current-password" required>

            <button class="button" type="submit">ورود</button>
        </form>
        <p class="switch-link">حساب کاربری نداری؟ <a href="signup.php">ثبت‌نام کن</a></p>
    </section>
    <p class="footer-note">پروژه آموزشی ثبت‌نام و ورود با PHP و SQLite</p>
</main>
</body>
</html>
