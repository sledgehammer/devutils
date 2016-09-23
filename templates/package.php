<h1><?php echo $module->name; ?> <small>package</small></h1>
<div class="row">
	<div class="col-md-5">
		<h3>Unittests</h3>
		<?php render($unittests); ?>
	</div>
	<div class="col-md-5">
		<?php
		render($properties);
		if (is_component($utilities)) {
			echo '<h3>Utilities</h3>';
			render($utilities);
		}?>
	</div>
</div>
