<?php
/**
 * The DevUtils Layout
 */
namespace Sledgehammer;
?>
<div class="pageLayout container">
	<header class="titlebar">
		<a class="titlebar-title" href="<?php echo WEBPATH; ?>">DevUtils</a>
	</header>
	<nav class="menu">
		<?php render($navigation); ?>
	</nav>
	<?php render($breadcrumbs); ?>
	<section class="contents">
		<?php render($contents); ?>
	</section>
</div>
<?php
javascript_once(WEBROOT.'core/js/jquery.js', 'jquery');
javascript_once(WEBROOT.'js/devutils.js');
?>