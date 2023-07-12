<!DOCTYPE html>
<html>
<head>
    <title>상품 입고 프로그램</title>
    <meta charset="utf-8" />
    <meta name="description" lang="ko" content="" />
    <script src="/assets/js/jquery-3.7.0.min.js"></script>
    <?php foreach ($container_css as $css): ?>
            <link rel="stylesheet" href="<?=$css?>" />
    <?php endforeach; ?>
    <?php foreach ($container_scripts as $script): ?>
            <script src="<?=$script?>"></script>
    <?php endforeach; ?>
</head>
<body>
    <main>
        <?php $this->load->view($container, $data ?? []); ?>
    </main>
</body>
</html>