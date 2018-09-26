<?php
/**
 * @package    Companies Component
 * @version    1.3.0
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
<div id="<?php echo $id; ?>" data-input-employees="<?php echo $id; ?>">
	<div class="list clearfix">
		<?php foreach ($employees as $employee): ?>
			<div class="item well <?php echo ($employee->confirm == 'user') ? 'wait-user' : ''; ?>"
				 data-user="<?php echo $employee->id; ?>">
				<div class="inner">
					<a class="avatar" href="<?php echo $employee->link; ?>" target="_blank">
						<div class="image" style="background-image: url('<?php echo $employee->avatar; ?>')"></div>
					</a>
					<div class="content span12">
						<div class="name">
							<a href="<?php echo $employee->link; ?>" target="_blank">
								<?php echo $employee->name; ?>
							</a>
						</div>
						<div class="position">
							<input type="text" id="<?php echo $id; ?>_<?php echo $employee->id; ?>_position"
								   name="<?php echo $name; ?>[<?php echo $employee->id; ?>][position]"
								   value="<?php echo $employee->position; ?>"
								   placeholder="<?php echo Text::_('COM_COMPANIES_EMPLOYEES_POSITION'); ?>"
								   class="span12" <?php echo ($employee->confirm !== 'confirm') ? ' readonly' : ''; ?>>
						</div>
						<div class="as_company">
							<label for="<?php echo $id; ?>_<?php echo $employee->id; ?>_as_company" class="checkbox">
								<input type="checkbox"
									   value="1"<?php echo ($employee->as_company) ? ' checked ' : ' '; ?>
									   id="<?php echo $id; ?>_<?php echo $employee->id; ?>_as_company"
									   name="<?php echo $name; ?>[<?php echo $employee->id; ?>][as_company]"
									<?php echo ($employee->confirm !== 'confirm') ? ' disabled="disabled" ' : ''; ?>>
								<?php echo Text::_('COM_COMPANIES_EMPLOYEES_AS_COMPANY'); ?>
							</label>
						</div>
					</div>
					<div class="actions">
						<a class="delete btn btn-mini btn-danger"
						   title="<?php echo Text::sprintf('COM_COMPANIES_EMPLOYEES_DELETE_LABEL', $employee->name); ?>">
							<i class="icon-remove"></i>
						</a>
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
			</div>
		<?php endforeach; ?>
	</div>
</div>