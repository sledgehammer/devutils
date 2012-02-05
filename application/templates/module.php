<h1><?php echo $module->name; ?> <small>module</small></h1>
<?php
render($properties);
if (is_view($utils)) {
		echo '<h2>Utils</h2>';
		render($utils);
}
?>
<h2>Documentation</h2>
<?php render($documentation); ?>