<div class="border">
	<div class="border">
		<p class="error-message">Loading&hellip;</p>
		<?php echo $link ?>

		<div id="lamplighter-footer">
			<p>
				<small>
					Lamplighter is proudly powered by
					<a href="<?php echo $cp->masked_url('https://lamplighter.io/?utm_source=lamplighter_addon&utm_content=text_link&utm_campaign=expressionengine&utm_medium=addon'); ?>" target="_blank">Lamplighter.io</a>
				</small>
			</p>
		</div>
	</div><!-- /.border -->
</div><!-- /.border -->

<script type="text/javascript">
	$(document).ready(function() {
		console.log('starting add-on request.');
		$.ajax({
			cache: false,
			data: {},
			dataType: 'html',
			success: function(data) {
				console.log('add-on request successful!');
				$('#lamplighter .accessorySection').html(data);
			},
			type: 'GET',
			url: '<?php echo $link ?>'
		});
	});
</script>
