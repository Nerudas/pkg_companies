<?php
/**
 * @package    Companies Component
 * @version    1.2.2
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

$app = Factory::getApplication();
$doc = Factory::getDocument();

HTMLHelper::stylesheet('media/com_companies/css/admin-company.min.css', array('version' => 'auto'));

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "company.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Route::_('index.php?option=com_companies&view=company&id=' . $this->item->id); ?>"
	  method="post"
	  name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'information')); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'information',
			Text::_('COM_COMPANIES_COMPANY_INFORMATION')); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('information'); ?>
				</fieldset>
			</div>
			<div class="span3">
				<fieldset class="form-vertical">
					<?php echo $this->form->renderFieldset('notes'); ?>
					<?php echo $this->form->renderFieldset('global'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'about', Text::_('COM_COMPANIES_COMPANY_ABOUT')); ?>
		<?php echo $this->form->getInput('about'); ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'images', Text::_('COM_COMPANIES_COMPANY_IMAGES'));
		echo $this->form->renderFieldset('images');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'contacts', Text::_('COM_COMPANIES_COMPANY_CONTACTS'));
		echo $this->form->renderFieldset('contacts');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'requisites', Text::_('COM_COMPANIES_COMPANY_REQUISITES'));
		echo $this->form->renderFieldset('requisites');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'tags', Text::_('JTAG'));
		echo $this->form->getInput('tags');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'portfolio', Text::_('COM_COMPANIES_COMPANY_PORTFOLIO'));
		echo $this->form->getInput('portfolio');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php
		if (!empty($this->item->id))
		{
			echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'employees', Text::_('COM_COMPANIES_EMPLOYEES'));
			echo $this->form->renderField('invite');
			echo $this->form->getInput('employees');

			echo HTMLHelper::_('bootstrap.endTab');
		}
		?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo $this->form->renderFieldset('publishingdata'); ?>
			</div>
			<div class="span6">
				<?php echo $this->form->renderFieldset('metadata'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'attribs', Text::_('JGLOBAL_FIELDSET_OPTIONS'));
		echo $this->form->renderFieldset('attribs');
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $app->input->getCmd('return'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>