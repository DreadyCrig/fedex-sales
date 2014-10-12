var Lamplighter = {
	// Initialize
	init: function() {
		console.log('Initializing Lamplighter.');
		this.toggleNotes();
		this.refresh();
		this.hideAddon();
		this.unhideAddons();
		console.log('Initialization complete.');
	},

	// Toggle notes rows
	toggleNotes: function() {
		$('#lamplighter a.toggle').live('click', function(e) {
			e.preventDefault();

			$(this).toggleClass('open-toggle');
			$(this).parent().parent().next('tr').toggleClass('open-notes');
		});
	},

	// Refresh add-on list
	refresh: function() {
		var obj = this;

		$('#lamplighter a.refresh').live('click', function(e) {
			e.preventDefault();

			var url = $(this).attr('href');
			$('.monitor-loading').css('visibility', 'visible');
			$.get(url, function(data) {
				var html = $('div.border', data);
				obj.updateView(html);
				$('.monitor-loading').css('visibility', 'hidden');
			}, 'html');
		});
	},

	// Hide an add-on
	hideAddon: function() {
		var obj = this;

		$('#lamplighter .addon-name a').live('click', function(e) {
			e.preventDefault();

			var $link = $(this),
				url = $(this).attr('href');

			$.get(url, function(data) {
				var html = $('div.border', data);
				obj.updateView(html);
			});
		});
	},

	// Un-hide all add-ons
	unhideAddons: function() {
		var obj = this;

		$('#lamplighter a.show-hidden-addons').live('click', function(e) {
			e.preventDefault();

			var $link = $(this),
				url = $(this).attr('href');

			$.get(url, function(data) {
				var html = $('div.border', data);
				obj.updateView(html);
			});
		});
	},

	// Update the accessory view
	updateView: function(html) {
		var scrollTop = $(document).scrollTop();
		$('#accessoriesDiv #lamplighter div.border:first').html(html).hide().fadeIn();
		$(document).scrollTop(scrollTop);
	}
};

$(document).ready(function() {
	Lamplighter.init();
});