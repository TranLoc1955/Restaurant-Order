<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Lỗi máy chủ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/components.css') ?>">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
    <div style="max-width: 500px;">
        <div style="font-size: 5rem; line-height: 1; margin-bottom: 1rem;">⚠️</div>
        <h1 style="font-size: 2.25rem; color: var(--danger-color); margin-bottom: 0.5rem;">500 Server Error</h1>
        <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Đã xảy ra sự cố nội bộ</h2>
        <p class="text-muted" style="margin-bottom: 2rem;">
            <?= htmlspecialchars($message ?: 'Hệ thống đang gặp sự cố gián đoạn. Đội ngũ kỹ thuật đã được thông báo để xử lý.') ?>
        </p>
        <div class="d-flex justify-center gap-1">
            <a href="<?= Response::baseUrl('') ?>" class="btn btn-primary">
                🏠 Về trang chủ
            </a>
            <a href="<?= Response::baseUrl('login') ?>" class="btn btn-outline">
                Đăng nhập lại
            </a>
        </div>
    </div>
</body>
</html>
