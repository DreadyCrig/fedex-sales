$(function() {
	
	var editButton = $('button[data-toggle=edit]');
	var replaceButton = $('button[data-toggle=replace]');
	var cancelButton = $('button[data-toggle=cancel]');

	editButton.click(function() {
		$('.winners--thumb, .winners--meta, .winners--extra').hide();
		$('.edit').fadeIn('fast');
	});

	replaceButton.click(function() {
		$('.winners--thumb, .winners--meta, .winners--extra').hide();
		$('.replace').fadeIn('fast');
	});

	cancelButton.click(function() {
		$('.winners--thumb, .winners--meta, .winners--extra').fadeIn('fast');
		$('.edit, .replace').hide();
	});

});