<?php
/**
 * PHPDocumentor
 */

namespace Sledgehammer;
javascript_once(WEBROOT.'core/js/jquery.js', 'jquery');

if ($generate):
	?>
	<div id="phpdoc_progressbar">
	<div class="modal-backdrop"></div>
		<div  class="modal">
			<div class="modal-header"><h3>Generating API Documentation</h3></div>
			<div class="modal-body">
				<div class="progress progress-striped active" style="width: 300px; margin: 0 auto;">
					<div class="bar" style="width: 100%;"></div>
				</div>
			</div>
			<div class="modal-footer"><a onclick="$('#phpdoc_progressbar').hide();" class="btn">Show log</a></div>
		</div>
	</div>
<?php /* Fake progressbar
<script type="text/javascript">
	var anim = setInterval(function () {
		$('.bar').width('+=25');
		if ($('.bar').width() >= 300) {
			clearInterval(anim);
			$('.progress').addClass('progress-striped').addClass('active');
		}
	}, 1000);
</script> */ ?>
	<?php render($generate); ?>
<script type="text/javascript">
	if ($('.contents h1').text() === 'Operation Completed!!') {
		window.location='<?php echo $url; ?>';
	} else {
		$('#phpdoc_progressbar').hide();
	}
</script>
<?php else: ?>
	<div class="phpdoc_refresh">
		Generated <?php echo $age; ?> ago
		<button class="btn" onclick="window.location='<?php echo $url; ?>'"><?php echo HTML::icon('refresh'); ?> Refresh</button>
	</div>
	<iframe id="phpdoc" width="100%" frameborder="0" scrolling="auto" src="<?php echo $src; ?>"></iframe>
<?php endif; ?>
