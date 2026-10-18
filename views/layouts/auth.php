<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Tài khoản - Nhà Hàng Trần Lộc') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/auth.css') ?>">
</head>
<body>
    <main>
        <?= $content ?>
    </main>

    <!-- JavaScript Core -->
    <script src="<?= Response::baseUrl('assets/js/app.js') ?>"></script>

    <!-- Kích hoạt Flash Message dạng Toast nếu có -->
    <?php
    $successFlash = Session::getFlash('success');
    $errorFlash = Session::getFlash('error');
    $warningFlash = Session::getFlash('warning');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($successFlash)): ?>
                Toast.show('success', <?= json_encode($successFlash) ?>);
            <?php endif; ?>
            <?php if (!empty($errorFlash)): ?>
                Toast.show('error', <?= json_encode($errorFlash) ?>);
            <?php endif; ?>
            <?php if (!empty($warningFlash)): ?>
                Toast.show('warning', <?= json_encode($warningFlash) ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
