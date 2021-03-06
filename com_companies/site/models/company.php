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

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class CompaniesModelCompany extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 *
	 * @since 1.0.0
	 */
	protected $_context = 'com_companies.company';

	/**
	 * Comments
	 *
	 * @var    object
	 * @since  1.0.10
	 */
	protected $_comments = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('company.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		$user = Factory::getUser();

		// Published state
		if ((!$user->authorise('core.manage', 'com_companies')))
		{
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1));
		}

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

	}

	/**
	 * Method to get type data for the current type
	 *
	 * @param   integer $pk The id of the type.
	 *
	 * @return  mixed object|false
	 *
	 * @since 1.0.0
	 */
	public function getItem($pk = null)
	{
		$app = Factory::getApplication();
		$pk  = (!empty($pk)) ? $pk : (int) $this->getState('company.id');

		if (!isset($this->_item[$pk]))
		{
			$errorRedirect = Route::_(CompaniesHelperRoute::getListRoute());
			$errorMsg      = Text::_('COM_COMPANIES_ERROR_COMPANY_NOT_FOUND');
			try
			{
				$db   = $this->getDbo();
				$user = Factory::getUser();

				$query = $db->getQuery(true)
					->select('c.*')
					->from('#__companies AS c')
					->where('c.id = ' . (int) $pk);

				// Join over the regions.
				$query->select(array('r.id as region_id', 'r.name as region_name'))
					->join('LEFT', '#__location_regions AS r ON r.id = c.region');

				// Join over the discussions.
				$query->select('(CASE WHEN dt.id IS NOT NULL THEN dt.id ELSE 0 END) as discussions_topic_id')
					->join('LEFT', '#__discussions_topics AS dt ON dt.item_id = c.id AND ' .
						$db->quoteName('dt.context') . ' = ' . $db->quote('com_companies.company'));


				// Filter by published state.
				$published = $this->getState('filter.published');
				if (!empty($published))
				{
					if (is_numeric($published))
					{
						$query->where('( c.state = ' . (int) $published .
							' OR ( c.created_by = ' . $user->id . ' AND c.state IN (0,1)))');
					}
					elseif (is_array($published))
					{
						$query->where('c.state IN (' . implode(',', $published) . ')');
					}
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					$app->redirect($url = $errorRedirect, $msg = $errorMsg, $msgType = 'error', $moved = true);

					return false;
				}

				// Link
				$data->link     = Route::_(CompaniesHelperRoute::getCompanyRoute($data->id));
				$data->editLink = false;
				if (!$user->guest)
				{
					$userId = $user->id;
					$asset  = 'com_companies.company.' . $data->id;

					$editLink = Route::_(CompaniesHelperRoute::getFormRoute($data->id));
					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->editLink = $editLink;
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');

						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->editLink = $editLink;
						}

						// Check for a valid user and that they are the employee.
						elseif (CompaniesHelperEmployees::canEditItem($data->id, $userId, $asset))
						{
							$data->editLink = $editLink;
						}
					}
				}

				// Convert the contacts field from json.
				$data->contacts = new Registry($data->contacts);
				if ($phones = $data->contacts->get('phones'))
				{
					$phones = ArrayHelper::fromObject($phones, false);
					$data->contacts->set('phones', $phones);
				}

				// Convert the requisites field from json.
				$data->requisites = new Registry($data->requisites);

				$imagesHelper = new FieldTypesFilesHelper();
				$imagesFolder = 'images/companies/' . $data->id;
				$data->logo   = $imagesHelper->getImage('logo', $imagesFolder, false, false);
				$data->header = $imagesHelper->getImage('header', $imagesFolder, 'media/com_companies/images/no-header.jpg', false);

				// Convert the images field to an array.
				$registry        = new Registry($data->portfolio);
				$data->portfolio = $registry->toArray();
				$data->portfolio = $imagesHelper->getImages('portfolio', $imagesFolder, $data->portfolio,
					array('text' => true, 'for_field' => false));

				// Convert the metadata field
				$data->metadata = new Registry($data->metadata);
				$data->metadata->set('image', $imagesHelper->getImage('meta', $imagesFolder, false, false));

				// Get Tags
				$data->tags = new TagsHelper;
				$data->tags->getItemTags('com_companies.company', $data->id);

				// Convert parameter fields to objects.
				$registry     = new Registry($data->attribs);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				// If no access, the layout takes some responsibility for display of limited information.
				$data->params->set('access-view', in_array($data->access, $user->getAuthorisedViewLevels()));

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					$app->redirect($url = $errorRedirect, $msg = $errorMsg, $msgType = 'error', $moved = true);
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the article.
	 *
	 * @param   integer $pk Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since 1.0.0
	 */
	public function hit($pk = 0)
	{
		$app      = Factory::getApplication();
		$hitcount = $app->input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('company.id');

			$table = Table::getInstance('Companies', 'CompaniesTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}

	/**
	 * Method to get company employees
	 *
	 * @param int $pk Company ID
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function getEmployees($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('company.id');

		$model = BaseDatabaseModel::getInstance('Employees', 'CompaniesModel', array('ignore_request' => true));
		$model->setState('company.id', $pk);

		return $model->getItems();
	}

	/**
	 * Method to get Related items
	 *
	 * @param int $pk Item ID
	 *
	 * @return  object
	 *
	 * @since 1.0.0
	 */
	public function getComments($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');
		if (!isset($this->_comments[$pk]))
		{
			$item = $this->getItem($pk);

			JLoader::register('DiscussionsHelperTopic', JPATH_SITE . '/components/com_discussions/helpers/topic.php');
			$data             = array();
			$data['context']  = 'com_companies.company';
			$data['item_id']  = $item->id;
			$data['topic_id'] = $item->discussions_topic_id;

			$data['create_topic'] = array(
				'context'    => 'com_companies.company',
				'item_id'    => $item->id,
				'title'      => $item->name,
				'text'       => '{company id="' . $item->id . '" layout="discussions"}',
				'state'      => $item->state,
				'access'     => $item->access,
				'created_by' => $item->created_by,
				'region'     => $item->region,
				'tags'       => (!empty($item->tags->itemTags)) ?
					implode(',', ArrayHelper::getColumn($item->tags->itemTags, 'tag_id')) : ''
			);

			$comments             = DiscussionsHelperTopic::getIntegration($data);
			$this->_comments[$pk] = $comments;
		}

		return $this->_comments[$pk];
	}
}