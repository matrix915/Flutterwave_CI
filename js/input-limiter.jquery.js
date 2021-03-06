/*===INPUT LIMITER===*/
/*
 * jQuery Input Limiter plugin 1.2.2
 * http://rustyjeans.com/jquery-plugins/input-limiter/
 *
 * Copyright (c) 2009 Russel Fones <russel@rustyjeans.com>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

(function ($) {
	$.fn.inputlimiter = function (options) {
		var opts = $.extend({}, $.fn.inputlimiter.defaults, options);
		if (opts.boxAttach && !$('#' + opts.boxId).length) {
			$('<div/>').appendTo("body").attr({id: opts.boxId, 'class': opts.boxClass}).css({'position': 'absolute'}).hide();
			// apply bgiframe if available
			if ($.fn.bgiframe) {
				$('#' + opts.boxId).bgiframe();
			}
		}

		var inputlimiterKeyup = function (e) {
			if (!opts.allowExceed && $(this).val().length > opts.limit) {
				$(this).val($(this).val().substring(0, opts.limit));
			}
			if (opts.boxAttach) {
				$('#' + opts.boxId).css({
					'width': $(this).outerWidth() - ($('#' + opts.boxId).outerWidth() - $('#' + opts.boxId).width()) + 'px',
					'left': $(this).offset().left + 'px',
					'top': ($(this).offset().top + $(this).outerHeight()) - 1 + 'px',
					'z-index': 2000
				});
			}
			var charsRemaining = opts.limit - $(this).val().length,
			    remText = opts.remTextFilter(opts, charsRemaining),
			    limitText = opts.limitTextFilter(opts);

			if (opts.limitTextShow) {
				$('#' + opts.boxId).html(remText + ' ' + limitText);
				// Check to see if the text is wrapping in the box
				// If it is lets break it between the remaining test and the limit test
				var textWidth = $("<span/>").appendTo("body").attr({id: '19cc9195583bfae1fad88e19d443be7a', 'class': opts.boxClass}).html(remText + ' ' + limitText).innerWidth();
				$("#19cc9195583bfae1fad88e19d443be7a").remove();
				if (textWidth > $('#' + opts.boxId).innerWidth()) {
					$('#' + opts.boxId).html(remText + '<br />' + limitText);
				}
				// Show the limiter box
				$('#' + opts.boxId).show();
			} else {
				$('#' + opts.boxId).html(remText).show();
			}
		}

		var inputlimiterKeypress = function (e) {
			if (!opts.allowExceed && (!e.keyCode || (e.keyCode > 46 && e.keyCode < 90) || e.keyCode === 13) && $(this).val().length >= opts.limit) {
				return false;
			}
		}

		var inputlimiterBlur = function () {
			if (opts.boxAttach) {
				$('#' + opts.boxId).fadeOut('fast');
			} else if (opts.remTextHideOnBlur) {
				var limitText = opts.limitText;
				limitText = limitText.replace(/\%n/g, opts.limit);
				limitText = limitText.replace(/\%s/g, (opts.limit === 1 ? '' : 's'));
				$('#' + opts.boxId).html(limitText);
			}
		}

		$(this).each(function (i) {
			$(this).bind('keyup', inputlimiterKeyup);
			$(this).bind('keypress', inputlimiterKeypress);
			$(this).bind('blur', inputlimiterBlur);
		});
	};


	$.fn.inputlimiter.remtextfilter = function (opts, charsRemaining) {
		var remText = opts.remText;
		if (charsRemaining === 0 && opts.remFullText !== null) {
			remText = opts.remFullText;
		}
		remText = remText.replace(/\%n/g, charsRemaining);
		remText = remText.replace(/\%s/g, (opts.zeroPlural ? (charsRemaining === 1 ? '' : 's') : (charsRemaining <= 1 ? '' : 's')));
		return remText;
	};

	$.fn.inputlimiter.limittextfilter = function (opts) {
		var limitText = opts.limitText;
		limitText = limitText.replace(/\%n/g, opts.limit);
		limitText = limitText.replace(/\%s/g, (opts.limit <= 1 ? '' : 's'));
		return limitText;
	};

	$.fn.inputlimiter.defaults = {
		limit: 255,
		boxAttach: true,
		boxId: 'limiterBox',
		boxClass: 'limiterBox',
		remText: '%n character%s remaining.',
		remTextFilter: $.fn.inputlimiter.remtextfilter,
		remTextHideOnBlur: true,
		remFullText: null,
		limitTextShow: true,
		limitText: 'Field limited to %n character%s.',
		limitTextFilter: $.fn.inputlimiter.limittextfilter,
		zeroPlural: true,
		allowExceed: false
	};

})(jQuery);
