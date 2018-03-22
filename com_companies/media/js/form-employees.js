/*
 * @package    Companies Component
 * @version    1.0.0
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
				employees_data = employees.find('input'),
				params = Joomla.getOptions(id, ''),
				company_id = params.company_id;

			$(employees_data).on('change', function () {
				var item = $(this).closest('[data-user]'),
					changeURL = params.changeURL,
					ajaxData = {};
				ajaxData.company_id = company_id;
				ajaxData.user_id = $(item).data('user');
				ajaxData.position = $(item).find('[name*="position"]').val();
				ajaxData.as_company = $(item).find('[name*="as_company"]').val();

				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: changeURL,
					data: ajaxData
				});
				console.log(ajaxData);
			});

		});
	});
})(jQuery);