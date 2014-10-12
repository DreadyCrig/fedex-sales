<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP;
?>

<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP.'method=delete_member')?>

	<input type="hidden" name="confirm" value="ok"/>
	<input type="hidden" name="entry_api_id" value="<?=$entry_api_id?>"/>

	<p><strong><?=lang('entry_api_delete_check')?></strong></p>
	<p class="notice"><?=lang('entry_api_delete_check_notice')?></p>

	<input type="submit" class="submit" value="<?=lang('delete')?>" name="submit">
	</p>
</form>
