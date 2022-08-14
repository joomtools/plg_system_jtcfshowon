<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jtcfshowon
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (version_compare(JVERSION, 4, 'ge'))
{
	\JLoader::registerAlias('FieldsHelper', '\\Joomla\\Component\\Fields\\Administrator\\Helper\\FieldsHelper');
}

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\ArrayHelper;


/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jttitlestripe
 *
 * @since   1.0.0
 */
class PlgSystemJtcfshowon extends CMSPlugin
{
	/**
	 * @var     CMSApplication
	 * @since   1.0.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     bool
	 * @since   1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var     array  Array of fields processed
	 * @since   1.0.0
	 */
	protected static $itemFields = [];

	/**
	 * @var     string[]  Array of fieldnames to hide
	 * @since   1.0.3
	 */
	protected static $unshownFields = [];

	/**
	 * Adds additional field showon to the (article|category)[ editing form.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		if (!in_array(
			$form->getName(),
			array(
				'com_fields.field.com_content.article',
				'com_fields.field.com_content.categories',
				'com_fields.field.com_users.user',
				),
			true))
		{
			return true;
		}

		$fieldParams = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/xml/showon.xml';
		$showonXml = new \SimpleXMLElement($fieldParams, 0, true);

		$form->setField($showonXml);

		return true;
	}

	/**
	 * Validates the showon value and disable the output of the field, if needed.
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   object  $item     The item object.
	 * @param   object  $field    The custom field object.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, &$field)
	{
		$isEditor = $this->app->input->getString('layout') === 'edit';

		if (!in_array(
			$context,
			array(
				'com_content.article',
				'com_content.categories',
				'com_users.user',
			),
			true)
			|| $isEditor
		)
		{
			return true;
		}

		if (empty($_showon = $field->fieldparams->get('showon', null)))
		{
			return true;
		}

		$showon       = [];
		$showon['or'] = explode('[OR]', $_showon);

		if (!empty($showon['or']))
		{
			foreach ($showon['or'] as $key => $value)
			{
				if (stripos($value, '[AND]') !== false)
				{
					list($or, $and) = explode('[AND]', $value, 2);

					$showon['and']      = explode('[AND]', $and);
					$showon['or'][$key] = $or;
				}
			}
		}

		$uniqueItemId = md5($item->id);

		if (empty(self::$itemFields[$uniqueItemId]))
		{
			self::$itemFields[$uniqueItemId] = ArrayHelper::pivot(FieldsHelper::getFields($context, $item), 'name');
		}

		$itemFields = self::$itemFields[$uniqueItemId];
		$showField  = true;

		if (!empty($showon['and']))
		{
			foreach ($showon['and'] as $value)
			{
				if ($showField === true)
				{
					list($fieldName, $fieldValue) = explode(':', $value);

					if (empty($itemFields[$fieldName]) || $itemFields[$fieldName]->rawvalue != $fieldValue)
					{
						$showField             = false;
						self::$unshownFields[] = $field->name;
					}
				}
			}
		}

		if ($showField === true)
		{
			foreach ($showon['or'] as $value)
			{
				list($fieldName, $fieldValue) = explode(':', $value);

				$showFieldOr[] = (!empty($itemFields[$fieldName]) && $itemFields[$fieldName]->rawvalue == $fieldValue);
			}

			if (!in_array(true, $showFieldOr))
			{
				$showField             = false;
				self::$unshownFields[] = $field->name;
			}
		}

		if ($showField === false)
		{
			$field->params->set('display', 0);

			if ($context == 'com_users.user'
				&& version_compare(JVERSION, '4', 'lt')
			)
			{
				$form = Form::getInstance('com_users.profile');

				$this->removeUnshownCustomFieldFromForm($form);
			}
		}

		return true;
	}

	/**
	 * Plugin that shows a custom field
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   object  &$item    The item object.
	 * @param   object  &$params  The item params
	 * @param   int     $page     The 'page' number
	 *
	 * @return  void
	 *
	 * @since  1.0.3
	 */
	public function onContentPrepare($context, &$item, &$params, $page = 0)
	{
		if (!in_array($context, array('com_users.profile', 'com_users.user')))
		{
			foreach ($item->jcfields as $key => $cfield)
			{
				if (in_array($cfield->name, self::$unshownFields))
				{
					$pattern = '@(<(\w+)[^>]*>)?{field ' . $cfield->id . '}(</\\2>|)@uU';

					if (!empty($item->fulltext))
					{
						$item->fulltext = preg_replace($pattern, '', $item->fulltext);
					}

					if (!empty($item->introtext))
					{
						$item->introtext = preg_replace($pattern, '', $item->introtext);
					}

					if (!empty($item->text))
					{
						$item->text = preg_replace($pattern, '', $item->text);
					}

					unset($item->jcfields[$key]);
				}
			}
		}
	}

	/**
	 * The display event.
	 *
	 * @param   object   $view           The item object.
	 * @param   string   $context        The context of the content being passed to the plugin.
	 * @param   string   $extensionName
	 * @param   string   $section
	 *
	 * @return  void
	 *
	 * @since   1.0.3
	 */
	public function onBeforeDisplay($view, $context, $extensionName, $section)
	{
		if ($context == 'com_users.profile')
		{
			$form = $view->getForm();

			$this->removeUnshownCustomFieldFromForm($form);
		}
	}

	/**
	 * Remove unshown custom field from form.
	 *
	 * @param   Form  $form  The form object.
	 *
	 * @return  void
	 *
	 * @since   1.0.3
	 */
	protected function removeUnshownCustomFieldFromForm(Form $form)
	{
		foreach (self::$unshownFields as $unshownField)
		{
			$form->removeField($unshownField, 'com_fields');
		}
	}
}
