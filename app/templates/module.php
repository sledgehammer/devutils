<h1><?php echo $module->name; ?> <small>module</small></h1>
<div class="row">
	<div class="span7">
		<?php
		render($properties);
		echo '<h3>Documentation</h3>';
		render($documentation);
		if (is_view($utilities)) {
			echo '<h3>Utilities</h3>';
			render($utilities);
		}?>
	</div>
	<div class="span5">
		<h3>Unittests</h3>
		<?php render($unittests); ?>
	</div>
</div>
