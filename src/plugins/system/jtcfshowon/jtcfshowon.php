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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jttitlestripe
 *
 * @since   1.0.0
 */
class PlgSystemJtcfshowon extends CMSPlugin
{
	/**
	 * @var     array  Array of article fields
	 * @since   1.0.0
	 */
	protected static $itemFields = [];

	/**
	 * @var     CMSApplication
	 * @since   1.0.0
	 */
	protected $app = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds additional field showon to the (article|category)[ editing form.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return   boolean
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
				),
			true))
		{
			return true;
		}

		$fieldParams = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/xml/showon.xml';
		$showonXml = new SimpleXMLElement($fieldParams, 0, true);

		$form->setField($showonXml);

		return true;
	}

	/**
	 * Validates the showon value and disable the output of the field, if needed.
	 *
	 * @param   string  $context
	 * @param   object  $item
	 * @param   object  $field
	 *
	 * @return   bool
	 *
	 * @since   1.0.0
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, &$field)
	{
		if (!in_array(
			$context,
			array(
				'com_content.article',
				'com_content.categories',
			),
			true))
		{
			return true;
		}

		if (empty($_showon = $field->fieldparams->get('showon', null)))
		{
			return true;
		}

		$showon = [];
		$showon['or'] = explode('[OR]', $_showon);

		if (!empty($showon['or']))
		{
			foreach ($showon['or'] as $key => $value)
			{
				if (stripos($value, '[AND]') !== false)
				{
					list($or, $and) = explode('[AND]', $value, 2);
					$showon['and'] = explode('[AND]', $and);
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
						$showField = false;
						$field->params->set('display', 0);
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
				$field->params->set('display', 0);
			}
		}

		return true;
	}
}
