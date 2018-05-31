<?php
/**
 * @package    Companies - Administrator Module
 * @version    1.0.9
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$user    = Factory::getUser();
$columns = 6;
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th style="min-width:100px" class="nowrap">
			<?php echo Text::_('COM_COMPANIES_COMPANY_NAME'); ?>
		</th>
		<th width="10%" class="nowrap hidden-phone center">
			<?php echo Text::_('COM_COMPANIES_COMPANY_LOGO'); ?>
		</th>
		<th width="10%" class="nowrap hidden-phone">
			<?php echo Text::_('JGRID_HEADING_REGION'); ?>
		</th>
		<th width="10%" class="nowrap hidden-phone">
			<?php echo Text::_('JGLOBAL_CREATED_DATE'); ?>
		</th>
		<th width="1%" class="nowrap hidden-phone">
			<?php echo Text::_('JGLOBAL_HITS'); ?>
		</th>
		<th width="1%" class="nowrap hidden-phone center">
			<?php echo Text::_('JGRID_HEADING_ID'); ?>
		</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="<?php echo $columns; ?>">
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php foreach ($items as $i => $item) :
		$canEdit = $user->authorise('core.edit', '#__companies.' . $item->id);
		$canChange = $user->authorise('core.edit.state', '#__companies' . $item->id);
		?>
		<tr item-id="<?php echo $item->id ?>">
			<td>
				<div>
					<?php if ($canEdit) : ?>
						<a class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>"
						   href="<?php echo Route::_('index.php?option=com_companies&task=company.edit&id=' . $item->id); ?>">
							<?php echo $item->name; ?>
						</a>
					<?php else : ?>
						<?php echo $item->name; ?>
					<?php endif; ?>
					<?php if ($item->in_work): ?>
						<sup class="label label-info">
							<?php echo Text::_('COM_COMPANIES_COMPANY_IN_WORK'); ?>
						</sup>
					<?php endif; ?>
				</div>
			</td>
			<td class="center">
				<?php if ($item->logo): ?>
					<img src="<?php echo $item->logo; ?>" alt="<?php echo $item->name; ?>" class="logo">
				<?php endif; ?>
			</td>
			<td class="small hidden-phone nowrap">
				<?php echo ($item->region !== '*') ? $item->region_name :
					Text::_('JGLOBAL_FIELD_REGIONS_ALL'); ?>
			</td>
			<td class="nowrap small hidden-phone">
				<?php echo $item->created > 0 ? HTMLHelper::_('date', $item->created,
					Text::_('DATE_FORMAT_LC2')) : '-' ?>
			</td>
			<td class="hidden-phone center">
				<span class="badge badge-info"><?php echo (int) $item->hits; ?></span>
			</td>
			<td class="hidden-phone center">
				<?php echo $item->id; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>