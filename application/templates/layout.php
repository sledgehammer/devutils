
<div class="pageLayout container">
	<div class="titlebar">
		<span class="title">DevUtils</span>
	</div>
	<?php render($breadcrumbs); ?>
	<div class="pageContents">
		<div class="menu">
			<?php render($application); ?>
			<?php render($modules); ?>
		</div>
		<div class="contents">
			<?php
			if (is_view($properties)) {
//			render($properties);
			}
			?>
			<?php render($contents); ?>
		</div>
	</div>
</div>
<?php
javascript_once(WEBROOT.'core/js/jquery.js', 'jquery');
javascript_once(WEBROOT.'js/devutils.js');
?>
