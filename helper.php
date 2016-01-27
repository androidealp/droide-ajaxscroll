<?php
/**
 * @package     droideajaxscroll
 * @subpackage  droideajaxscroll
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$com_path = JPATH_SITE . '/components/com_content/';
require_once $com_path . 'helpers/route.php';

JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');

/**
 * Helper for mod_articles_category
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @since       1.6
 */
abstract class BlogHelper
{
 
	public $ordem_direcao = 'ASC';
	/**
	 * Get a list of articles from a specific category
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 *
	 * @since  1.6
	 */
	public static function getList($start, $limit,$catid , $menuparans,$menu_limit)
	{
		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();
		$appParams = $app->getParams();

		$articles->setState('params', $appParams);


		// Set the filters based on the module params
		$articles->setState('list.start',(int) $start);
		$articles->setState('list.limit', (int) $limit);
		$articles->setState('filter.published', 1);

		// Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$articles->setState('filter.access', $access);

		$catids = $catid;
		
		$items = 'nao';
		// Category filter
		if ($catids)
		{


		if (($menuparans['show_subcategory_content'] > 0 || $menuparans['show_subcategory_content'] == '-1') )
		{	
			$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$categories->setState('params', $appParams);
			$levels = ($menuparans['show_subcategory_content']!='-1')?$menuparans['show_subcategory_content']:99999;
			$categories->setState('filter.subcategories', $levels);
			$categories->setState('filter.max_category_levels', true);
			//$categories->setState('filter.get_children', $levels);
			$categories->setState('filter.published', 1);
			$categories->setState('filter.access', $access);
			$additional_catids = array();
			$categories->setState('filter.parentId', $catids);
			$recursive = true;
			$items     = $categories->getItems($recursive);
			$testes = array();

			if ($items)
			{
				foreach ($items as $category)
				{

					///$additional_catids[] = $category->id;
					$condition = (($category->level - $categories->getParent()->level) <= $levels);
					//$testes[] = $category->level.' - '.$categories->getParent()->level.'<= '.$levels;
					if ($condition)
					{
						$additional_catids[] = $category->id;
					}
				}
			}

			$catids = array($catids);
			$catids = array_unique(array_merge($catids, $additional_catids));

			$items  =$catids;
		}

		$articles->setState('filter.category_id', $catids);
			
		}

		$items = $articles->getItems();
		
		$testes['menuparans'] = array('orderby_sec'=>$menuparans['orderby_sec'], 'order_date'=>$menuparans['order_date'], 'orderby_pri'=>$menuparans['orderby_pri']);

		$testes['buildContentOrderBy'] = BlogHelper::_buildContentOrderBy($menuparans);
		// Ordering
		$articles->setState('list.ordering', BlogHelper::_buildContentOrderBy($menuparans));



		$listOrder = $app->getUserStateFromRequest('com_content.category.list.' . $catid. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$articles->setState('list.direction', $listOrder);

		

		$items = $articles->getItems();

	// Find current Article ID if on an article page
		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if ($option === 'com_content' && $view === 'article')
		{
			$active_article_id = $app->input->getInt('id');
		}
		else
		{
			$active_article_id = 0;
		}

		// Prepare data for display using display options
		foreach ($items as &$item)
		{
			$item->slug    = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$app       = JFactory::getApplication();
				$menu      = $app->getMenu();
				$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuitems[0]))
				{
					$Itemid = $menuitems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$Itemid = $app->input->getInt('Itemid');
				}

				$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
			}

			// Used for styling the active article
			$item->active      = $item->id == $active_article_id ? 'active' : '';
			$item->displayDate = '';

			

			if ($item->catid)
			{
				$item->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
				//$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				//$item->displayCategoryTitle = $show_category ? $item->category_title : '';
			}

		}

		
		return $items;
		
	}


	public static function _buildContentOrderBy($atribmenu)
	{
		$app		= JFactory::getApplication('site');
		$db			= JFactory::getDbo();
		$params		= $atribmenu;
		$itemid		= $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol	= $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderDirn	= $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby	= ' ';

		/*if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = null;
		}*/

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', '')))
		{
			$orderDirn = 'ASC';
		}

		if ($orderCol && $orderDirn)
		{
			$orderby .= $db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ';
		}

		$articleOrderby		= ($atribmenu['orderby_sec'])?$atribmenu['orderby_sec']:'rdate';
		$articleOrderDate	= $atribmenu['order_date'];
		$categoryOrderby	= $atribmenu['orderby_pri'];

		require_once JPATH_SITE.'/components/com_content/helpers/query.php';

		$secondary			= ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
		$primary			= ContentHelperQuery::orderbyPrimary($categoryOrderby);

		$orderby .= $primary . ' ' . $secondary . ' a.created ';

		return $orderby;
	}




	/**
	 * Strips unnecessary tags from the introtext
	 *
	 * @param   string  $introtext  introtext to sanitize
	 *
	 * @return mixed|string
	 *
	 * @since  1.6
	 */
	public static function _cleanIntrotext($introtext)
	{
		$introtext = str_replace('<p>', ' ', $introtext);
		$introtext = str_replace('</p>', ' ', $introtext);
		$introtext = strip_tags($introtext, '<a><em><strong>');
		$introtext = trim($introtext);

		return $introtext;
	}

	/**
	 * Method to truncate introtext
	 *
	 * The goal is to get the proper length plain text string with as much of
	 * the html intact as possible with all tags properly closed.
	 *
	 * @param   string   $html       The content of the introtext to be truncated
	 * @param   integer  $maxLength  The maximum number of charactes to render
	 *
	 * @return  string  The truncated string
	 *
	 * @since   1.6
	 */
	public static function truncate($html, $maxLength = 0)
	{
		$baseLength = strlen($html);

		// First get the plain text string. This is the rendered text we want to end up with.
		$ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

		for ($maxLength; $maxLength < $baseLength;)
		{
			// Now get the string if we allow html.
			$htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

			// Now get the plain text from the html string.
			$htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

			// If the new plain text string matches the original plain text string we are done.
			if ($ptString == $htmlStringToPtString)
			{
				return $htmlString;
			}

			// Get the number of html tag characters in the first $maxlength characters
			$diffLength = strlen($ptString) - strlen($htmlStringToPtString);

			// Set new $maxlength that adjusts for the html tags
			$maxLength += $diffLength;

			if ($baseLength <= $maxLength || $diffLength <= 0)
			{
				return $htmlString;
			}
		}

		return $html;
	}

	/**
	 * Groups items by field
	 *
	 * @param   array   $list                        list of items
	 * @param   string  $fieldName                   name of field that is used for grouping
	 * @param   string  $article_grouping_direction  ordering direction
	 * @param   null    $fieldNameToKeep             field name to keep
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupBy($list, $fieldName, $article_grouping_direction, $fieldNameToKeep = null)
	{
		$grouped = array();

		if (!is_array($list))
		{
			if ($list == '')
			{
				return $grouped;
			}

			$list = array($list);
		}

		foreach ($list as $key => $item)
		{
			if (!isset($grouped[$item->$fieldName]))
			{
				$grouped[$item->$fieldName] = array();
			}

			if (is_null($fieldNameToKeep))
			{
				$grouped[$item->$fieldName][$key] = $item;
			}
			else
			{
				$grouped[$item->$fieldName][$key] = $item->$fieldNameToKeep;
			}

			unset($list[$key]);
		}

		$article_grouping_direction($grouped);

		return $grouped;
	}

	/**
	 * Groups items by date
	 *
	 * @param   array   $list                        list of items
	 * @param   string  $type                        type of grouping
	 * @param   string  $article_grouping_direction  ordering direction
	 * @param   string  $month_year_format           date format to use
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupByDate($list, $type = 'year', $article_grouping_direction, $month_year_format = 'F Y')
	{
		$grouped = array();

		if (!is_array($list))
		{
			if ($list == '')
			{
				return $grouped;
			}

			$list = array($list);
		}

		foreach ($list as $key => $item)
		{
			switch ($type)
			{
				case 'month_year' :
					$month_year = JString::substr($item->created, 0, 7);

					if (!isset($grouped[$month_year]))
					{
						$grouped[$month_year] = array();
					}

					$grouped[$month_year][$key] = $item;
					break;

				case 'year' :
				default:
					$year = JString::substr($item->created, 0, 4);

					if (!isset($grouped[$year]))
					{
						$grouped[$year] = array();
					}

					$grouped[$year][$key] = $item;
					break;
			}

			unset($list[$key]);
		}

		$article_grouping_direction($grouped);

		if ($type === 'month_year')
		{
			foreach ($grouped as $group => $items)
			{
				$date                      = new JDate($group);
				$formatted_group           = $date->format($month_year_format);
				$grouped[$formatted_group] = $items;

				unset($grouped[$group]);
			}
		}

		return $grouped;
	}
}