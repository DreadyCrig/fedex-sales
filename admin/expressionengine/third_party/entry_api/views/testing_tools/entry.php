<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP;
	$_base_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<p>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools" class="less_important_bttn">Back</a></span>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools_entry_create" class="less_important_bttn">Create Entry</a></span>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools_entry_read" class="less_important_bttn">Read Entry</a></span>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools_entry_update" class="less_important_bttn">Update Entry</a></span>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools_entry_delete" class="less_important_bttn">Delete Entry</a></span>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools_entry_search" class="less_important_bttn">Search Entry</a></span>
	<div class="clear"></div>
</p>

<h1><?=$action_method?></h1>

<div id="accordion">
	<div>
		<?php if($response != ''):?>
		<h3 class="accordion">Respone</h3>
		<div>
			<p><pre><?=print_r($response)?></pre></p>
		</div>
		<?php endif;?>
		<h3 class="accordion">XMLRPC</h3>
		<div>
			<?=form_open_multipart($_base_url.'method='.$action_method,
		  		array('id'=>'XMLRPC'), array('type' => 'xmlrpc', 'method' => $method))?>
			<?php
				ee()->table->set_template($cp_pad_table_template);
				//ee()->table->template['thead_open'] = '<thead class="visualEscapism">';
				ee()->table->set_heading(
					array('data' => lang('entry_api_preference'), 'style' => 'width:50%;'),
					lang('setting')
			  	);
			  	
				foreach ($fields['data'] as $key => $val)
				{
					if(
						$key == 'rest_http_auth'
						|| $key == 'path_soap'
						|| $key == 'path_rest'
						|| $key == 'path_custom'
					) {continue;}

					//subtext
					$subtext = '';
					if(is_array($val))
					{
						$subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
						$val = $val[0];
					}
				    ee()->table->add_row(lang($key, $key).$subtext, $val);
				}
				echo ee()->table->generate();
				// Clear out of the next one
				ee()->table->clear();
			?>
			<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
			<?=form_close()?>
		</div>
		<h3 class="accordion">SOAP</h3>
		<div>
			<?=form_open_multipart($_base_url.'method='.$action_method,
		  		array('id'=>'XMLRPC'), array('type' => 'soap', 'method' => $method))?>
			<?php
				ee()->table->set_template($cp_pad_table_template);
				//ee()->table->template['thead_open'] = '<thead class="visualEscapism">';
				ee()->table->set_heading(
					array('data' => lang('entry_api_preference'), 'style' => 'width:50%;'),
					lang('setting')
			  	);
			  	
				foreach ($fields['data'] as $key => $val)
				{
					if(
						$key == 'rest_http_auth'
						|| $key == 'path_xmlrpc'
						|| $key == 'path_rest'
						|| $key == 'path_custom'
					) {continue;}

					//subtext
					$subtext = '';
					if(is_array($val))
					{
						$subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
						$val = $val[0];
					}
				    ee()->table->add_row(lang($key, $key).$subtext, $val);
				}
				echo ee()->table->generate();
				// Clear out of the next one
				ee()->table->clear();
			?>
			<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
			<?=form_close()?>
		</div>
		<h3 class="accordion">REST</h3>
		<div>
			<?=form_open_multipart($_base_url.'method='.$action_method,
		  		array('id'=>'XMLRPC'), array('type' => 'rest', 'method' => $method))?>
			<?php
				ee()->table->set_template($cp_pad_table_template);
				//ee()->table->template['thead_open'] = '<thead class="visualEscapism">';
				ee()->table->set_heading(
					array('data' => lang('entry_api_preference'), 'style' => 'width:50%;'),
					lang('setting')
			  	);
				foreach ($fields['data'] as $key => $val)
				{
					if(
						$key == 'path_xmlrpc'
						|| $key == 'path_soap'
						|| $key == 'path_custom'
					) {continue;}

					//subtext
					$subtext = '';
					if(is_array($val))
					{
						$subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
						$val = $val[0];
					}
				    ee()->table->add_row(lang($key, $key).$subtext, $val);
				}
				echo ee()->table->generate();
				// Clear out of the next one
				ee()->table->clear();
			?>
			<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
			<?=form_close()?>
		</div>
		<h3 class="accordion">Custom</h3>
		<div>
			<?=form_open_multipart($_base_url.'method='.$action_method,
		  		array('id'=>'custom'), array('type' => 'custom', 'method' => $method))?>
			<?php
				ee()->table->set_template($cp_pad_table_template);
				//ee()->table->template['thead_open'] = '<thead class="visualEscapism">';
				ee()->table->set_heading(
					array('data' => lang('entry_api_preference'), 'style' => 'width:50%;'),
					lang('setting')
			  	);
				foreach ($fields['data'] as $key => $val)
				{
					if(
						$key == 'path_rest'
						|| $key == 'path_soap'
						|| $key == 'path_xmlrpc'
					) {continue;}

					//subtext
					$subtext = '';
					if(is_array($val))
					{
						$subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
						$val = $val[0];
					}
				    ee()->table->add_row(lang($key, $key).$subtext, $val);
				}
				echo ee()->table->generate();
				// Clear out of the next one
				ee()->table->clear();
			?>
			<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
			<?=form_close()?>
		</div>
	</div>
</div>
