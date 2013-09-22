<h1><?php echo $project ?> <small>project</small></h1>
<div class="row">
	<div class="col-md-5">
		<?php render($properties); ?>
		<h3>Utilities</h3>
		<?php render($utilities); ?>
	</div>
	<div class="col-md-5">
		<h3>Unittests</h3>
		<?php render($unittests); ?>
	</div>
</div>
