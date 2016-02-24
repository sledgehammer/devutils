<?php
/**
 * The DevUtils Layout
 */

?>
<div class="page">
    <div class="page-column">
        <header class="titlebar">
            <a class="titlebar-title" href="<?php echo \Sledgehammer\WEBPATH; ?>">DevUtils</a>
        </header>
        <div class="page-body">
            <nav class="page-menu">
                <?php //render($navigation); ?>
            </nav>
            <div class="page-content">
                <?php //render($breadcrumbs); ?>
                <section class="contents">
                    <?php render($contents); ?>
                </section>
                <div class="statusbar-placeholder"></div>
            </div>
        </div>
    </div>
</div>
<?php
javascript_once(\Sledgehammer\WEBROOT.'core/js/jquery.js', 'jquery');
javascript_once(\Sledgehammer\WEBROOT.'js/devutils.js');