<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Djaya Roasters</title>
    <!-- Required CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="base-url" content="<?= BASE_URL ?>">
    
    <!-- Custom CSS -->
    <?php if (isset($custom_css)): ?>
        <?= $custom_css ?>
    <?php endif; ?>
</head>
<body>
    
    <!-- Include the navbar -->
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/includes/navbar.php'; ?>
