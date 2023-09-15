<?php

use AvegaCms\Entities\Seo\{MetaEntity, BreadCrumbsEntity};
use CodeIgniter\Pager\Pager;

/**
 * @var MetaEntity|null $meta
 * @var BreadCrumbsEntity[] $breadcrumbs
 * @var Pager|null $pager
 */

?>

<!doctype html>
<html class="no-js" lang="<?php echo $meta->lang ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo $meta->title ?></title>
    <meta name="keywords" content="<?php echo $meta->keywords ?>">
    <meta name="description" content="<?php echo $meta->description ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:site_name" content="<?php echo $meta->openGraph->siteName ?>">
    <meta property="og:locale" content="<?php echo $meta->openGraph->locale ?>">
    <meta property="og:title" content="<?php echo $meta->openGraph->title ?>">
    <meta property="og:type" content="<?php echo $meta->openGraph->type ?>">
    <meta property="og:url" content="<?php echo $meta->openGraph->url ?>">
    <meta property="og:image" content="<?php echo $meta->openGraph->image ?>">

    <?php if ($meta->useMultiLocales) : foreach ($meta->alternate as $item): ?>
        <link rel="alternate" hreflang="<?php echo $item['hreflang'] ?>" href="<?php echo $item['href'] ?>">
    <?php endforeach; endif; ?>

    <link rel="canonical" href="<?php echo $meta->canonical ?>">

    <meta name="robots" content="<?php echo $meta->robots ?>">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="icon.png">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/style.css">

    <link rel="manifest" href="site.webmanifest">
    <meta name="theme-color" content="#fafafa">
</head>

<body>

<!-- Add your site or application content here -->
<p>Hello world! This is HTML5 Boilerplate.</p>
<script src="js/app.js"></script>

</body>

<nav aria-label="breadcrumb">
    <ul class="breadcrumb my-3">
        <?php foreach ($breadcrumbs as $item): ?>
            <li class="<?php echo 'breadcrumb-item' . ($item->active ? ' active' : '') ?>" <?php echo ($item->active) ? 'aria-current="page"' : '' ?>>
                <?php echo ($item->active) ? $item->title : anchor($item->url, $item->title,
                    ['class' => 'breadcrumb-link']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>


</html>