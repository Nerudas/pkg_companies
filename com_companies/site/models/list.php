<?php
/**
 * @package    Companies Component
 * @version    1.2.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

class CompaniesModelList extends ListModel
{
	/**
	 * This tag
	 *
	 * @var    object
	 * @since  1.0.0
	 */
	protected $_tag = null;

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'c.id', 'id',
				'c.name', 'name',
				'c.alias', 'alias',
				'c.about', 'about',
				'c.contacts', 'contacts',
				'c.requisites', 'requisites',
				'c.logo', 'logo',
				'c.header', 'header',
				'c.portfolio', 'portfolio',
				'c.state', 'state',
				'c.created', 'created',
				'c.created_by', 'created_by',
				'c.modified', 'modified',
				'c.attribs', 'attribs',
				'c.metakey', 'metakey',
				'c.metadesc', 'metadesc',
				'c.access', 'access',
				'c.hits', 'hits',
				'c.region', 'region', 'region_name',
				'c.metadata', 'metadata',
				'c.tags_search', 'tags_search',
				'c.tags_map', 'tags_map',
			);
		}
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Set id state
		$pk = $app->input->getInt('id', 1);
		$this->setState('tag.id', $pk);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params     = $app->getParams();
		$menuParams = new Registry;
		$menu       = $app->getMenu()->getActive();
		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}
		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->setState('params', $mergedParams);

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

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState($ordering, $direction);

		// Set limit & limitstart for query.
		$this->setState('list.limit', $params->get('companies_limit', 10, 'uint'));
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// Set ordering for query.
		$ordering  = empty($ordering) ? 'c.created' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . serialize($this->getState('filter.item_id'));
		$id .= ':' . $this->getState('filter.item_id.include');

		return parent::getStoreId($id);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     data
	 * @param   boolean $loadData load current data
	 *
	 * @return  Form|boolean  The Form object or false on error
	 *
	 * @since 1.0.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		if ($form = parent::getFilterForm())
		{
			$params = $this->getState('params');
			if ($params->get('search_placeholder', ''))
			{
				$form->setFieldAttribute('search', 'hint', $params->get('search_placeholder'), 'filter');
			}

			$form->setValue('tag', 'filter', $this->getState('tag.id', 1));
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since 1.0.0
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from($db->quoteName('#__companies', 'c'));

		// Join over the regions.
		$query->select(array('r.id as region_id', 'r.name as region_name', 'r.icon as region_icon'))
			->join('LEFT', '#__location_regions AS r ON r.id = c.region');

		// Join over the discussions.
		$query->select('(CASE WHEN dt.id IS NOT NULL THEN dt.id ELSE 0 END) as discussions_topic_id')
			->join('LEFT', '#__discussions_topics AS dt ON dt.item_id = c.id AND ' .
				$db->quoteName('dt.context') . ' = ' . $db->quote('com_companies.company'));

		// Filter by access level
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('c.access IN (' . $groups . ')');
		}

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

		// Filter by tag.
		$tag = (int) $this->getState('tag.id');
		if ($tag > 1)
		{
			$query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('c.id')
				. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_companies.company'))
				->where($db->quoteName('tagmap.tag_id') . ' = ' . $tag);
		}

		// Filter by a single or group of items.
		$itemId = $this->getState('filter.item_id');
		if (is_numeric($itemId))
		{
			$type = $this->getState('filter.item_id.include', true) ? '= ' : '<> ';
			$query->where('с.id ' . $type . (int) $itemId);
		}
		elseif (is_array($itemId))
		{
			$itemId = ArrayHelper::toInteger($itemId);
			$itemId = implode(',', $itemId);
			$type   = $this->getState('filter.item_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('c.id ' . $type . ' (' . $itemId . ')');
		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('c.id = ' . (int) substr($search, 3));
			}
			else
			{
				$text_columns = array('c.name', 'c.about', 'c.contacts', 'c.requisites', 'c.tags_search', 'r.name');

				$sql = array();
				foreach ($text_columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}
				$number = $this->clearPhoneNumber($search);
				$code   = '+7';
				if (!empty($number))
				{
					$phone         = $code . $number;
					$phone_columns = array('c.contacts');
					foreach ($phone_columns as $column)
					{
						$sql[] = $column . ' LIKE ' . $db->quote('%' . $phone . '%');
					}
				}
				$query->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		// Group by
		$query->group(array('c.id'));

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'c.created');
		$direction = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string  $query      The query.
	 * @param   integer $limitstart Offset.
	 * @param   integer $limit      The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since 1.0.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDbo()->setQuery($query, $limitstart, $limit);

		return $this->getDbo()->loadObjectList('id');
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		if (!empty($items))
		{
			$user = Factory::getUser();
			JLoader::register('CompaniesHelperEmployees', JPATH_SITE . '/components/com_companies/helpers/employees.php');
			JLoader::register('DiscussionsHelperTopic', JPATH_SITE . '/components/com_discussions/helpers/topic.php');

			$mainTags = ComponentHelper::getParams('com_companies')->get('tags', array());

			foreach ($items as &$item)
			{
				$item->logo = (!empty($item->logo) && JFile::exists(JPATH_ROOT . '/' . $item->logo)) ?
					Uri::root(true) . '/' . $item->logo : false;

				$item->link     = Route::_(CompaniesHelperRoute::getCompanyRoute($item->id));
				$item->editLink = false;
				if (!$user->guest)
				{
					$userId = $user->id;
					$asset  = 'com_companies.company.' . $item->id;

					$editLink = Route::_(CompaniesHelperRoute::getFormRoute($item->id));

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$item->editLink = $editLink;
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $item->created_by)
						{
							$item->editLink = $editLink;
						}

						// Check for a valid user and that they are the employee.
						elseif (CompaniesHelperEmployees::canEditItem($item->id, $userId, $asset))
						{
							$item->editLink = $editLink;
						}
					}
				}

				// Convert the contacts field from json.
				$item->contacts = new Registry($item->contacts);
				if ($phones = $item->contacts->get('phones'))
				{
					$phones = ArrayHelper::fromObject($phones, false);
					$item->contacts->set('phones', $phones);
				}

				// Convert the portfolio field from json.
				$registry        = new Registry($item->portfolio);
				$item->portfolio = $registry->toArray();

				// Get Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_companies.company', $item->id);
				if (!empty($item->tags->itemTags))
				{
					foreach ($item->tags->itemTags as &$tag)
					{
						$tag->main = (in_array($tag->id, $mainTags));
					}
					$item->tags->itemTags = ArrayHelper::sortObjects($item->tags->itemTags, 'main', -1);
				}
				
				// Get region
				$item->region_icon = (!empty($item->region_icon) && JFile::exists(JPATH_ROOT . '/' . $item->region_icon)) ?
					Uri::root(true) . $item->region_icon : false;
				if ($item->region == '*')
				{
					$item->region_icon = false;
					$item->region_name = Text::_('JGLOBAL_FIELD_REGIONS_ALL');
				}

				// Discussions posts count
				$item->commentsCount = DiscussionsHelperTopic::getPostsTotal($item->discussions_topic_id);
			}
		}

		return $items;
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in \JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string  $key       The key of the user state variable.
	 * @param   string  $request   The name of the variable passed in a request.
	 * @param   string  $default   The default value for the variable if not found. Optional.
	 * @param   string  $type      Filter for the variable, for valid values see {@link \JFilterInput::clean()}. Optional.
	 * @param   boolean $resetPage If true, the limitstart in request is set to zero
	 *
	 * @return  mixed  The request user state.
	 *
	 * @since 1.0.0
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app       = Factory::getApplication();
		$set_state = $app->input->get($request, null, $type);
		$new_state = parent::getUserStateFromRequest($key, $request, $default, $type, $resetPage);
		if ($new_state == $set_state)
		{
			return $new_state;
		}
		$app->setUserState($key, $set_state);

		return $set_state;
	}

	/**
	 * Clear phone number
	 *
	 * @param  string $number Phone number
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function clearPhoneNumber($number)
	{
		if (mb_strlen($number) > 10)
		{
			$number = str_replace(array('+7'), '', $number);
			$number = preg_replace('/\D/', '', $number);

		}
		if (mb_strlen($number) > 10 && mb_substr($number, 0, 1) == 8)
		{
			$number = mb_substr($number, 1);
		}
		$number = (int) $number;

		return $number;
	}

	/**
	 * Get the current tag
	 *
	 * @param null $pk
	 *
	 * @return object|false
	 *
	 * @since 1.1.0
	 */
	public function getTag($pk = null)
	{
		if (!is_object($this->_tag))
		{
			$app    = Factory::getApplication();
			$pk     = (!empty($pk)) ? (int) $pk : (int) $this->getState('tag.id', $app->input->get('id', 1));
			$tag_id = $pk;

			$root            = new stdClass();
			$root->title     = Text::_('JGLOBAL_ROOT');
			$root->id        = 1;
			$root->parent_id = 0;
			$root->link      = Route::_(CompaniesHelperRoute::getListRoute(1));

			if ($tag_id > 1)
			{
				$errorRedirect = Route::_(CompaniesHelperRoute::getListRoute(1));
				$errorMsg      = Text::_('COM_COMPANIES_ERROR_TAG_NOT_FOUND');
				try
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true)
						->select(array('t.id', 't.parent_id', 't.title', 'pt.title as parent_title'))
						->from('#__tags AS t')
						->where('t.id = ' . (int) $tag_id)
						->join('LEFT', '#__tags AS pt ON pt.id = t.parent_id');

					$user = Factory::getUser();
					if (!$user->authorise('core.admin'))
					{
						$query->where('t.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
					}
					if (!$user->authorise('core.manage', 'com_tags'))
					{
						$query->where('t.published =  1');
					}

					$db->setQuery($query);
					$data = $db->loadObject();

					if (empty($data))
					{
						$app->redirect($url = $errorRedirect, $msg = $errorMsg, $msgType = 'error', $moved = true);

						return false;
					}

					$data->link = Route::_(CompaniesHelperRoute::getListRoute($data->id));

					$this->_tag = $data;
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
						$this->_tag = false;
					}
				}
			}
			else
			{
				$this->_tag = $root;
			}
		}

		return $this->_tag;
	}
}