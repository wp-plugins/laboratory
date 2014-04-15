(function($) {

	$(function() {
		try {
			$('div.cf7com-links').insertAfter($('div.wrap h2:first'));

			$.extend($.tgPanes, _colabs7.tagGenerators);
			$('#taggenerator').tagGenerator(_colabs7.generateTag, {
				dropdownIconUrl: _colabs7.pluginUrl + '/admin/images/dropdown.gif',
				fadebuttImageUrl: _colabs7.pluginUrl + '/admin/images/fade-butt.png' });

			$('input#colabs7-title:disabled').css({cursor: 'default'});

			$('input#colabs7-title').mouseover(function() {
				$(this).not('.focus').addClass('mouseover');
			});

			$('input#colabs7-title').mouseout(function() {
				$(this).removeClass('mouseover');
			});

			$('input#colabs7-title').focus(function() {
				$(this).addClass('focus').removeClass('mouseover');
			});

			$('input#colabs7-title').blur(function() {
				$(this).removeClass('focus');
			});

			$('input#colabs7-title').change(function() {
				updateTag();
			});

			updateTag();

			$('.check-if-these-fields-are-active').each(function(index) {
				if (! $(this).is(':checked'))
					$(this).parent().siblings('.mail-fields').hide();

				$(this).click(function() {
					if ($(this).parent().siblings('.mail-fields').is(':hidden')
					&& $(this).is(':checked')) {
						$(this).parent().siblings('.mail-fields').slideDown('fast');
					} else if ($(this).parent().siblings('.mail-fields').is(':visible')
					&& $(this).not(':checked')) {
						$(this).parent().siblings('.mail-fields').slideUp('fast');
					}
				});
			});

			postboxes.add_postbox_toggles(_colabs7.screenId);

		} catch (e) {
		}
	});

	function updateTag() {
		var title = $('input#colabs7-title').val();

		if (title)
			title = title.replace(/["'\[\]]/g, '');

		$('input#colabs7-title').val(title);
		var postId = $('input#post_ID').val();
		var tag = '[colabs-contact-form id="' + postId + '" title="' + title + '"]';
		$('input#contact-form-anchor-text').val(tag);

		var oldId = $('input#colabs7-id').val();

		if (0 != oldId) {
			var tagOld = '[contact-form ' + oldId + ' "' + title + '"]';
			$('input#contact-form-anchor-text-old').val(tagOld).parent('p.tagcode').show();
		}
	}

})(jQuery);