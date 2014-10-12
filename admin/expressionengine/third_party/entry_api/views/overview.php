<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<p>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=add_member" class="less_important_bttn">Add API user</a></span>
	<div class="clear"></div>
</p>

<?php
$this->table->set_empty(lang('entry_id_nodata'));
$this->table->set_template($cp_table_template);
$this->table->set_heading(
		lang('entry_api_member').'/'.lang('entry_api_membergroup'),
		lang('entry_api_services'),
		lang('entry_api_apis'),
		lang('entry_api_free_apis'),
		lang('entry_api_active'),
		''
);

if(!empty($members))
{
	foreach($members as $key=>$val)
	{
		$this->table->add_row(
			$val['username'].$val['group_title'],
			str_replace('|', ', ', $val['services']),
			str_replace('|', ', ', $val['apis']),
			str_replace('|', ', ', $val['free_apis']),
			$val['active'],
			'<a href="'.$base_url.'method=show_member'.AMP.'entry_api_id='.$val['entry_api_id'].'">'.lang('entry_id_show_channel').'</a> / <a href="'.$base_url.'method=delete_member'.AMP.'entry_api_id='.$val['entry_api_id'].'">'.lang('entry_api_delete_channel').'</a>'
		);
	}
}
else 
{
	$this->table->add_row('','','','', '');
	
}
?>
<?=$this->table->generate();?>