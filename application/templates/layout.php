
<div class="pageLayout container">
	<div class="titlebar">
		<a class="title" href="<?php echo WEBPATH; ?>">DevUtils</a>
	</div>
	<nav class="menu">
		<?php render($navigation); ?>
	</nav>
	<?php render($breadcrumbs); ?>
	<section class="contents">
		<?php
		if (is_view($properties)) {
//			render($properties);
		}
		?>
		<?php render($contents); ?>
	</section>
</div>
<?php
javascript_once(WEBROOT.'core/js/jquery.js', 'jquery');
javascript_once(WEBROOT.'js/devutils.js');
?>
