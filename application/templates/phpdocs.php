<div class="phpdoc_refresh">
	Generated <?php echo $age; ?> ago
	<button class="btn" onclick="window.location='<?php echo $regenerateUrl; ?>'"><?php echo SledgeHammer\HTML::icon('refresh'); ?> Refresh</button>
</div>
<iframe id="phpdoc" width="100%" frameborder="0" scrolling="auto" src="<?php echo $src; ?>"></iframe>