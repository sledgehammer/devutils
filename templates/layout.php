<?php
/**
 * The DevUtils Layout
 */

?>
<header class="titlebar">
    <a class="titlebar-title" href="<?php echo \Sledgehammer\WEBPATH; ?>">DevUtils</a>
</header>
<?php render($breadcrumbs); ?>
<section class="contents">
    <?php render($contents); ?>
</section>
<div class="statusbar-placeholder"></div>
<?php
javascript_once('https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js', 'jquery');
javascript_once(\Sledgehammer\WEBROOT.'js/devutils.js');