<?php
/**
 * @package    Companies Component
 * @version    1.2.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$userFiledData             = $displayData;
$userFiledData['name']     = $displayData['name'] . '[user_id]';
$userFiledData['id']       = $displayData['name'] . '_user_id';
$userFiledData['userName'] = '';

extract($displayData);

HTMLHelper::_('jquery.framework');
HTMLHelper::_('stylesheet', 'media/com_companies/css/form-invite.min.css', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_companies/js/form-invite.min.js', array('version' => 'auto'));
?>
<div id="<?php echo $id; ?>" data-input-invite="<?php echo $id; ?>">
	<div class="user-field">
		<?php echo LayoutHelper::render('joomla.form.field.user', $userFiledData); ?>
	</div>
	<a class="btn btn-success send"><?php echo Text::_('COM_COMPANIES_EMPLOYEES_INVITE_SUBMIT'); ?></a>
</div>