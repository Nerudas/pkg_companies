/*
 * @package    Companies Component
 * @version    1.0.7
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('[data-input-invite]').each(function () {
			var field = $(this),
				id = field.attr('id'),
				send = field.find('.send'),
				params = Joomla.getOptions(id, '');

			$(send).on('click', function () {
				var popupUrl = params.inviteURL + '&' +
					$.param({
						'user_id': field.find('[name*="user_id"]').val(),
						'company_id': params.company_id,
						'to': 'user',
						'popup': 1
					});

				var popupWidth = $(window).width() / 2,
					popupHeight = $(window).height() / 2;
				if (popupWidth < 320) {
					popupWidth = 320;
				}
				if (popupHeight < 200) {
					popupHeight = 200;
				}

				var popupParams = 'height=' + popupHeight + ',width=' + popupWidth +
					',menubar=no,toolbar=no,location=no,directories=no,status=no,resizable=no,scrollbars=no';

				window.open(popupUrl, null, popupParams);
			});
		});
	});
})(jQuery);