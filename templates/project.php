<h1><?php echo $project ?> <small>project</small></h1>

<div class="row">
	<div class="col-md-5">
		<h3>Unittests</h3>
		<?php render($unittests); ?>
	</div>
	<div class="col-md-5">
        <h3>Packages</h3>
		<?php
		render($packages);
		if (is_component($utilities)) {
			echo '<h3>Utilities</h3>';
			render($utilities);
		}?>
	</div>
</div>


