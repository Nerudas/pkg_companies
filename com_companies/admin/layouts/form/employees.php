<?php
/**
 * @package    Companies Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

HTMLHelper::_('jquery.framework');
HTMLHelper::_('stylesheet', 'media/com_companies/css/form-employees.min.css', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_companies/js/form-employees.min.js', array('version' => 'auto'));
?>
<div id="<?php echo $id; ?>" data-employees-input="<?php echo $id; ?>">
	<?php foreach ($employees as $employee): ?>
		<div class="item well <?php echo ($employee->confirm == 'user') ? 'wait-user' : ''; ?>">
			<div class="inner">
				<div class="avatar">
					<div class="image" style="background-image: url('<?php echo $employee->avatar; ?>')"></div>
				</div>
				<div class="content">
					<div class="name">
						<?php echo $employee->name; ?>
					</div>
					<div class="position">
						<input type="text" id="<?php echo $id; ?>_<?php echo $employee->id; ?>_position"
							   name="<?php echo $name; ?>[<?php echo $employee->id; ?>][position]"
							   value="<?php echo $employee->position; ?>"
							   placeholder="<?php echo Text::_('COM_COMPANIES_EMPLOYEES_POSITION'); ?>">
					</div>
				</div>
				<div class="actions">
					<a class="delete btn btn-mini btn-danger"><i class="icon-remove"></i></a>
					<?php if ($employee->confirm == 'company'): ?>
						<a class="confirm btn btn-mini btn-success">
							<?php echo Text::_('COM_COMPANIES_EMPLOYEES_CONFIRM_SUBMIT'); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ($employee->confirm !== 'confirm'): ?>
				<div class="confirm">
					<?php if ($employee->confirm == 'user'): ?>
						<div class="text-warning text-small">
							<?php echo Text::_('COM_COMPANIES_EMPLOYEES_CONFIRM_NEED_USER'); ?>
						</div>
					<?php elseif ($employee->confirm == 'company'): ?>
						<div class="text-warning text-small">
							<?php echo Text::_('COM_COMPANIES_EMPLOYEES_CONFIRM_NEED_COMPANY'); ?>
						</div>
					<?php elseif ($employee->confirm == 'error'): ?>
						<div class="text-error text-small">
							<?php echo Text::_('COM_COMPANIES_ERROR_EMPLOYEES_KEY'); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<input type="hidden" id="<?php echo $id; ?>_<?php echo $employee->id; ?>_user_id"
				   name="<?php echo $name; ?>[<?php echo $employee->id; ?>][user_id]"
				   value="<?php echo $employee->id; ?>" readonly>
			<input type="hidden" id="<?php echo $id; ?>_<?php echo $employee->id; ?>_company_id"
				   name="<?php echo $name; ?>[<?php echo $employee->id; ?>][company_id]"
				   value="<?php echo $company_id; ?>" readonly>
		</div>
	<?php endforeach; ?>
</div>