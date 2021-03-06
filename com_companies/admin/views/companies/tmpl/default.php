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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null,
	array('placeholder_text_multiple' => Text::_('COM_COMPANIES_COMPANY_TAGS')));
HTMLHelper::_('formbehavior.chosen', 'select');

HTMLHelper::stylesheet('media/com_companies/css/admin-companies.min.css', array('version' => 'auto'));

$app       = Factory::getApplication();
$doc       = Factory::getDocument();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$columns = 12;

?>

<form action="<?php echo Route::_('index.php?option=com_companies&view=companies'); ?>" method="post" name="adminForm"
	  id="adminForm">
	<?php echo LayoutHelper::render('joomla.searchtools.default',
		array('view' => $this, 'options' => array('filtersHidden' => false))); ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table id="companiesList" class="table table-striped">
			<thead>
			<tr>
				<th width="1%" class="center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<th width="2%" style="min-width:100px" class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
				</th>
				<th style="min-width:100px" class="nowrap">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_COMPANIES_COMPANY_NAME', 'c.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo Text::_('COM_COMPANIES_COMPANY_NOTES_NOTE'); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone center">
					<?php echo Text::_('COM_COMPANIES_COMPANY_LOGO'); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'c.access', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_COMPANIES_COMPANY_CREATED_BY', 'c.created_by', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_REGION', 'region_name', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_CREATED_DATE', 'c.created', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'c.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone center">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo $columns; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>

			<?php foreach ($this->items as $i => $item) :
				$canEdit = $user->authorise('core.edit', '#__companies.' . $item->id);
				$canChange = $user->authorise('core.edit.state', '#__companies' . $item->id);
				?>
				<tr item-id="<?php echo $item->id ?>">
					<td class="center">
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<a class="btn btn-micro hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>"
							   href="<?php echo Route::_('index.php?option=com_companies&task=company.edit&id=' . $item->id); ?>">
								<span class="icon-apply icon-white"></span>
							</a>
							<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'companies.', $canChange, 'cb'); ?>
							<?php
							if ($canChange)
							{
								HTMLHelper::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'companies');
								echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->name));
							}
							?>
						</div>
					</td>
					<td>
						<div>
							<?php if ($canEdit) : ?>
								<a class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>"
								   href="<?php echo Route::_('index.php?option=com_companies&task=company.edit&id=' . $item->id); ?>">
									<?php echo $this->escape($item->name); ?>
								</a>
							<?php else : ?>
								<?php echo $this->escape($item->name); ?>
							<?php endif; ?>
							<?php if ($item->in_work): ?>
								<sup class="label label-info">
									<?php echo Text::_('COM_COMPANIES_COMPANY_IN_WORK'); ?>
								</sup>
							<?php endif; ?>
						</div>
					</td>
					<td>
						<div><?php echo $item->note; ?></div>
					</td>
					<td class="center">
						<?php if ($item->logo): ?>
							<img src="<?php echo $item->logo; ?>" alt="<?php echo $item->name; ?>" class="logo">
						<?php endif; ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="small hidden-phone">
						<?php if ((int) $item->created_by != 0) : ?>
							<a class="hasTooltip"
							   href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>"
							   title="<?php echo Text::_('COM_COMPANIES_COMPANY_CREATED_BY'); ?>">
								<?php echo $this->escape($item->owner_name); ?></a>
						<?php endif; ?>
					</td>
					<td class="small hidden-phone nowrap">
						<?php echo $this->escape($item->region_name); ?>
					</td>
					<td class="nowrap small hidden-phone">
						<?php echo $item->created > 0 ? HTMLHelper::_('date', $item->created,
							Text::_('DATE_FORMAT_LC2')) : '-' ?>
					</td>
					<td class="hidden-phone center">
						<span class="badge badge-info">
							<?php echo (int) $item->hits; ?>
						</span>
					</td>
					<td class="hidden-phone center">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>