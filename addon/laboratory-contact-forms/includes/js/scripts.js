(function($) {

	$(function() {
		try {
			if (typeof _colabs7 == 'undefined' || _colabs7 === null)
				_colabs7 = {};

			_colabs7 = $.extend({ cached: 0 }, _colabs7);

			_colabs7.supportHtml5 = $.colabs7SupportHtml5();

			$('div.colabs7 > form').ajaxForm({
				beforeSubmit: function(formData, jqForm, options) {
					jqForm.colabs7ClearResponseOutput();
					jqForm.find('img.ajax-loader').css({ visibility: 'visible' });
					return true;
				},
				beforeSerialize: function(jqForm, options) {
					jqForm.find('[placeholder].placeheld').each(function(i, n) {
						$(n).val('');
					});
					return true;
				},
				data: { '_colabs7_is_ajax_call': 1 },
				dataType: 'json',
				success: function(data) {
					if (! $.isPlainObject(data) || $.isEmptyObject(data))
						return;

					var ro = $(data.into).find('div.colabs7-response-output');
					$(data.into).colabs7ClearResponseOutput();

					$(data.into).find('.colabs7-form-control').removeClass('colabs7-not-valid');
					$(data.into).find('form.colabs7-form').removeClass('invalid spam sent failed');

					if (data.captcha)
						$(data.into).colabs7RefillCaptcha(data.captcha);

					if (data.quiz)
						$(data.into).colabs7RefillQuiz(data.quiz);

					if (data.invalids) {
						$.each(data.invalids, function(i, n) {
							$(data.into).find(n.into).colabs7NotValidTip(n.message);
							$(data.into).find(n.into).find('.colabs7-form-control').addClass('colabs7-not-valid');
						});

						ro.addClass('colabs7-validation-errors');
						$(data.into).find('form.colabs7-form').addClass('invalid');

						$(data.into).trigger('invalid.colabs7');

					} else if (1 == data.spam) {
						ro.addClass('colabs7-spam-blocked');
						$(data.into).find('form.colabs7-form').addClass('spam');

						$(data.into).trigger('spam.colabs7');

					} else if (1 == data.mailSent) {
						ro.addClass('colabs7-mail-sent-ok');
						$(data.into).find('form.colabs7-form').addClass('sent');

						if (data.onSentOk)
							$.each(data.onSentOk, function(i, n) { eval(n) });

						$(data.into).trigger('mailsent.colabs7');

					} else {
						ro.addClass('colabs7-mail-sent-ng');
						$(data.into).find('form.colabs7-form').addClass('failed');

						$(data.into).trigger('mailfailed.colabs7');
					}

					if (data.onSubmit)
						$.each(data.onSubmit, function(i, n) { eval(n) });

					$(data.into).trigger('submit.colabs7');

					if (1 == data.mailSent)
						$(data.into).find('form').resetForm().clearForm();

					$(data.into).find('[placeholder].placeheld').each(function(i, n) {
						$(n).val($(n).attr('placeholder'));
					});

					$(data.into).colabs7FillResponseOutput(data.message);
				},
				error: function(xhr, status, error, $form) {
					var e = $('<div class="ajax-error"></div>').text(error.message);
					$form.after(e);
				}
			});

			$('div.colabs7 > form').colabs7InitForm();

		} catch (e) {
		}
	});

	$.fn.colabs7InitForm = function() {
		return this.each(function(i, n) {
			var $f = $(n);

			if (_colabs7.cached)
				$f.colabs7OnloadRefill();

			$f.colabs7ToggleSubmit();

			$f.find('.colabs7-submit').colabs7AjaxLoader();

			$f.find('.colabs7-acceptance').click(function() {
				$f.colabs7ToggleSubmit();
			});

			$f.find('.colabs7-exclusive-checkbox').colabs7ExclusiveCheckbox();

			$f.find('[placeholder]').colabs7Placeholder();

			if (! _colabs7.supportHtml5.date) {
				$f.find('input.colabs7-date[type="date"]').each(function() {
					$(this).datepicker({
						dateFormat: 'yy-mm-dd',
						minDate: new Date($(this).attr('min')),
						maxDate: new Date($(this).attr('max'))
					});
				});
			}

			if (_colabs7.jqueryUi && ! _colabs7.supportHtml5.number) {
				$f.find('input.colabs7-number[type="number"]').each(function() {
					$(this).spinner({
						min: $(this).attr('min'),
						max: $(this).attr('max'),
						step: $(this).attr('step')
					});
				});
			}
		});
	};

	$.fn.colabs7ExclusiveCheckbox = function() {
		return this.find('input:checkbox').click(function() {
			$(this).closest('.colabs7-checkbox').find('input:checkbox').not(this).removeAttr('checked');
		});
	};

	$.fn.colabs7Placeholder = function() {
		if (_colabs7.supportHtml5.placeholder)
			return this;

		return this.each(function() {
			$(this).val($(this).attr('placeholder'));
			$(this).addClass('placeheld');

			$(this).focus(function() {
				if ($(this).hasClass('placeheld'))
					$(this).val('').removeClass('placeheld');
			});

			$(this).blur(function() {
				if ('' == $(this).val()) {
					$(this).val($(this).attr('placeholder'));
					$(this).addClass('placeheld');
				}
			});
		});
	};

	$.fn.colabs7AjaxLoader = function() {
		return this.each(function() {
			var loader = $('<img class="ajax-loader" />')
				.attr({ src: _colabs7.loaderUrl, alt: _colabs7.sending })
				.css('visibility', 'hidden');

			$(this).after(loader);
		});
	};

	$.fn.colabs7ToggleSubmit = function() {
		return this.each(function() {
			var form = $(this);
			if (this.tagName.toLowerCase() != 'form')
				form = $(this).find('form').first();

			if (form.hasClass('colabs7-acceptance-as-validation'))
				return;

			var submit = form.find('input:submit');
			if (! submit.length) return;

			var acceptances = form.find('input:checkbox.colabs7-acceptance');
			if (! acceptances.length) return;

			submit.removeAttr('disabled');
			acceptances.each(function(i, n) {
				n = $(n);
				if (n.hasClass('colabs7-invert') && n.is(':checked')
				|| ! n.hasClass('colabs7-invert') && ! n.is(':checked'))
					submit.attr('disabled', 'disabled');
			});
		});
	};

	$.fn.colabs7NotValidTip = function(message) {
		return this.each(function() {
			var into = $(this);
			into.append('<span class="colabs7-not-valid-tip">' + message + '</span>');
			$('span.colabs7-not-valid-tip').mouseover(function() {
				$(this).fadeOut('fast');
			});
			into.find(':input').mouseover(function() {
				into.find('.colabs7-not-valid-tip').not(':hidden').fadeOut('fast');
			});
			into.find(':input').focus(function() {
				into.find('.colabs7-not-valid-tip').not(':hidden').fadeOut('fast');
			});
		});
	};

	$.fn.colabs7OnloadRefill = function() {
		return this.each(function() {
			var url = $(this).attr('action');
			if (0 < url.indexOf('#'))
				url = url.substr(0, url.indexOf('#'));

			var id = $(this).find('input[name="_colabs7"]').val();
			var unitTag = $(this).find('input[name="_colabs7_unit_tag"]').val();

			$.getJSON(url,
				{ _colabs7_is_ajax_call: 1, _colabs7: id, _colabs7_request_ver: $.now() },
				function(data) {
					if (data && data.captcha)
						$('#' + unitTag).colabs7RefillCaptcha(data.captcha);

					if (data && data.quiz)
						$('#' + unitTag).colabs7RefillQuiz(data.quiz);
				}
			);
		});
	};

	$.fn.colabs7RefillCaptcha = function(captcha) {
		return this.each(function() {
			var form = $(this);

			$.each(captcha, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find('img.colabs7-captcha-' + i).attr('src', n);
				var match = /([0-9]+)\.(png|gif|jpeg)$/.exec(n);
				form.find('input:hidden[name="_colabs7_captcha_challenge_' + i + '"]').attr('value', match[1]);
			});
		});
	};

	$.fn.colabs7RefillQuiz = function(quiz) {
		return this.each(function() {
			var form = $(this);

			$.each(quiz, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find(':input[name="' + i + '"]').siblings('span.colabs7-quiz-label').text(n[0]);
				form.find('input:hidden[name="_colabs7_quiz_answer_' + i + '"]').attr('value', n[1]);
			});
		});
	};

	$.fn.colabs7ClearResponseOutput = function() {
		return this.each(function() {
			$(this).find('div.colabs7-response-output').hide().empty().removeClass('colabs7-mail-sent-ok colabs7-mail-sent-ng colabs7-validation-errors colabs7-spam-blocked');
			$(this).find('span.colabs7-not-valid-tip').remove();
			$(this).find('img.ajax-loader').css({ visibility: 'hidden' });
		});
	};

	$.fn.colabs7FillResponseOutput = function(message) {
		return this.each(function() {
			$(this).find('div.colabs7-response-output').append(message).slideDown('fast');
		});
	};

	$.colabs7SupportHtml5 = function() {
		var features = {};
		var input = document.createElement('input');

		features.placeholder = 'placeholder' in input;

		var inputTypes = ['email', 'url', 'tel', 'number', 'range', 'date'];

		$.each(inputTypes, function(index, value) {
			input.setAttribute('type', value);
			features[value] = input.type !== 'text';
		});

		return features;
	};

})(jQuery);