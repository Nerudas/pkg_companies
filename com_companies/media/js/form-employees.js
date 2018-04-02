/*
 * @package    Companies Component
 * @version    1.0.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('[data-input-employees]').each(function () {
			var field = $(this),
				id = field.attr('id'),
				employees = field.find('[data-user]'),
				employee_data = employees.find('input'),
				employee_actions = employees.find('.actions'),
				employee_confirm = employee_actions.find('.confirm'),
				employee_delete = employee_actions.find('.delete'),
				params = Joomla.getOptions(id, ''),
				company_id = params.company_id;

			// Confirm employee
			$(employee_confirm).on('click', function () {
				var popupURL = params.confirmURL + '&' +
					$.param({
						'user_id': $(this).closest('[data-user]').data('user'),
						'company_id': company_id,
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

				window.open(popupURL, null, popupParams);

			});

			// Delete employee
			$(employee_delete).on('click', function () {
				if (confirm($(this).attr('title') + '?')) {
					var popupURL = params.deleteURL + '&' +
						$.param({
							'user_id': $(this).closest('[data-user]').data('user'),
							'company_id': company_id,
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

					window.open(popupURL, null, popupParams);
				}
			});

			// Change data
			$(employee_data).on('change', function () {
				var item = $(this).closest('[data-user]'),
					changeURL = params.changeURL,
					ajaxData = {};
				ajaxData.company_id = company_id;
				ajaxData.user_id = $(item).data('user');
				ajaxData.position = $(item).find('[name*="position"]').val();
				ajaxData.as_company = 0;
				if ($(item).find('[name*="as_company"]').prop('checked')) {
					ajaxData.as_company = 1;
				}
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: changeURL,
					data: ajaxData
				});
			});

		});
	});
})(jQuery);