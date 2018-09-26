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

$app = Factory::getApplication();
$doc = Factory::getDocument();

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
<form action="<?php echo Route::_(CompaniesHelperRoute::getFormRoute($this->item->id)); ?>" method="post"
	  name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<?php echo $this->form->renderField('name'); ?>
	<?php echo $this->form->renderField('alias'); ?>
	<?php echo $this->form->renderField('about'); ?>
	<?php echo $this->form->renderField('tags'); ?>
	<?php echo $this->form->renderFieldSet('images'); ?>
	<?php echo $this->form->renderFieldSet('contacts'); ?>
	<?php echo $this->form->renderFieldSet('requisites'); ?>
	<?php echo $this->form->renderField('employees'); ?>
	<?php echo $this->form->renderField('portfolio'); ?>
	<?php echo $this->form->renderFieldSet('hidden'); ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo $app->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>

	<button onclick="Joomla.submitbutton('company.save');"><?php echo Text::_('JAPPLY'); ?></button>
	<button onclick="Joomla.submitbutton('company.cancel');"><?php echo Text::_('JCANCEL'); ?></button>
</form>