<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.ENTRY_API_MAP.AMP;
?>

<?php
$this->table->set_empty(lang(ENTRY_API_MAP.'_nodata'));
$this->table->set_template($cp_table_template);

$this->table->set_columns($table_headers);
$data = $this->table->datasource('_logs_data');
echo $data['table_html'];
echo $data['pagination_html'];
?>