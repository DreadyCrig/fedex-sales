<h2>Lamplighter Settings</h2>
<br />
<p>
	<a href="<?php echo $cp->masked_url('https://lamplighter.io/?utm_source=lamplighter_addon&utm_content=text_link&utm_campaign=expressionengine&utm_medium=addon'); ?>">Lamplighter</a> is a service that securely monitors your sites.  Once an API key is entered on this page, the site will be able to send add-on and site information to Lamplighter.
</p>
<? if (!$curl_enabled) { ?>
	<p><a href="http://php.net/curl">cURL</a> must be enabled on your server for Lamplighter to work correctly.</p>
<? } else if ($api_key) { ?>
	<p>The Lamplighter add-on has been successfully installed on this site.</p>
	<table class="mainTable">
		<thead>
			<tr>
				<th style="width: 30%;">Setting</th>
				<th style="width: 70%;">Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="api_key">API Key</label>
					<p>This is your API Key from Lamplighter.</p>
				</td>
				<td>
					<strong><?php echo $api_key; ?></strong>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="<?php echo $base_url.'&amp;method=remove_key'; ?>">
						<input type="button" value="Remove API Key" class="submit" />
					</a>
				</td>
			</tr>
		</tbody>
	</table>
<? } else { ?>
	<p>
		If you're having trouble finding your API Key, or if you are having trouble installing, please visit our <a href="<?php echo $cp->masked_url('http://support.lamplighter.io/?utm_source=lamplighter_addon&utm_content=text_link&utm_campaign=expressionengine&utm_medium=addon'); ?>">FAQ</a>.
	</p>
	<form id="default_form" method="POST" action="<?php echo $base_url.'&amp;method=save_key'; ?>">
	<input type="hidden" name="XID" value="<?php echo XID_SECURE_HASH ?>" />
	<table class="mainTable">
		<thead>
			<tr>
				<th style="width: 30%;">Setting</th>
				<th style="width: 70%;">Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="api_key">API Key</label>
					<p>Please paste your API Key from Lamplighter here.</p>
				</td>
				<td>
					<input name="api_key" id="api_key" type="text">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" value="Save" class="submit" />
				</td>
			</tr>
		</tbody>
	</table>
	</form>
<? } ?>

