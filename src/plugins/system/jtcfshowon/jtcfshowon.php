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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Utilities\ArrayHelper;

if (version_compare(JVERSION, '4', 'lt')) {
    \JLoader::registerAlias('\\Joomla\\Component\\Fields\\Administrator\\Helper\\FieldsHelper', 'FieldsHelper');
    \JLoader::applyAliasFor('FieldsHelper');
}

/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jttitlestripe
 *
 * @since  1.0.0
 */
class PlgSystemJtcfshowon extends CMSPlugin
{
    /**
     * @var    CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    bool
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @var    array  Array of fields processed
     * @since  1.0.0
     */
    protected static $itemFields = [];

    /**
     * @var    string[]  Array of fieldnames to hide
     * @since  1.0.3
     */
    protected static $unshownFields = [];

    /**
     * Adds additional field showon to the (article|category)[ editing form.
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  bool
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
            true)
        ) {
            return true;
        }

        $fieldParams = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/xml/showon.xml';
        $showonXml   = new \SimpleXMLElement($fieldParams, 0, true);

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
        ) {
            return true;
        }

        if (empty($showOn = $field->fieldparams->get('showon', null))) {
            return true;
        }

        $uniqueItemId = md5($item->id);

        if (empty(self::$itemFields[$uniqueItemId])) {
            self::$itemFields[$uniqueItemId] = ArrayHelper::pivot(FieldsHelper::getFields($context, $item), 'name');
        }

        $showField = $this->fieldIsShownValidation($showOn, $uniqueItemId);

        if ($showField === false) {
            $field->params->set('display', 0);

            self::$unshownFields[] = $field->name;

            if ($context == 'com_users.user'
                && version_compare(JVERSION, '4', 'lt')
            ) {
                $form = Form::getInstance('com_users.profile');

                $this->removeUnshownCustomFieldFromForm($form);
            }
        }

        return true;
    }

    /**
     * Evaluate showon values
     *
     * @param   string  $showOn        The value of the show on attribute.
     * @param   string  $uniqueItemId  The unique item id.
     *
     * @return  bool  Return true if the field is shown.
     * @since   1.0.5
     */
    private function fieldIsShownValidation($showOn, $uniqueItemId)
    {
        $regex = array(
            'search'  => array(
                '[AND]',
                '[OR]',
            ),
            'replace' => array(
                ' [AND]',
                ' [OR]',
            ),
        );

        $showOn                = str_replace($regex['search'], $regex['replace'], $showOn);
        $showOnValidationRules = (array) explode(' ', $showOn);

        if (empty($showOnValidationRules)) {
            return true;
        }

        $valuesSum      = count($showOnValidationRules) - 1;
        $conditionValid = array();
        $isShown        = true;

        foreach ($showOnValidationRules as $key => $rule) {
            $not       = false;
            $glue      = '';
            $separator = ':';

            if (strpos($rule, '[OR]') !== false) {
                $glue = 'or';
                $rule = strtr($rule, array('[OR]' => ''));
            }

            if (strpos($rule, '[AND]') !== false) {
                $glue = 'and';
                $rule = strtr($rule, array('[AND]' => ''));
            }

            if (strpos($rule, '!') !== false) {
                $not       = true;
                $separator = '!:';
            }

            list($fieldName, $expectedValue) = explode($separator, $rule);

            $fieldValue = array();

            if (isset(self::$itemFields[$uniqueItemId][$fieldName])) {
                $fieldValue = (array) self::$itemFields[$uniqueItemId][$fieldName]->rawvalue;
            }

            $valueValidation = (($not === false && in_array($expectedValue, $fieldValue))
                || ($not === true && !in_array($expectedValue, $fieldValue)));

            if ($glue === '') {
                if ((int) $key === (int) $valuesSum) {
                    return $valueValidation;
                }

                $conditionValid[$key] = $valueValidation;
            }

            if ($glue == 'and') {
                $isShown              = $conditionValid[$key - 1] && $valueValidation;
                $conditionValid[$key] = $isShown;
            }

            if ($glue == 'or') {
                $isShown              = $conditionValid[$key - 1] || $valueValidation;
                $conditionValid[$key] = $isShown;
            }
        }

        return $isShown;
    }

    /**
     * Plugin that shows a custom field
     *
     * @param   string   $context  The context of the content being passed to the plugin.
     * @param   object  &$item     The item object.
     * @param   object  &$params   The item params
     * @param   int      $page     The 'page' number
     *
     * @return  void
     * @since  1.0.3
     */
    public function onContentPrepare($context, &$item, &$params, $page = 0)
    {
        if (empty($item->jcfields)) {
            return;
        }

        if (!in_array($context, array('com_users.profile', 'com_users.user'))) {
            foreach ($item->jcfields as $key => $cfield) {
                if (in_array($cfield->name, self::$unshownFields)) {
                    $pattern = '@(<(\w+)[^>]*>)?{field ' . $cfield->id . '}(</\\2>|)@uU';

                    if (!empty($item->fulltext)) {
                        $item->fulltext = preg_replace($pattern, '', $item->fulltext);
                    }

                    if (!empty($item->introtext)) {
                        $item->introtext = preg_replace($pattern, '', $item->introtext);
                    }

                    if (!empty($item->text)) {
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
     * @param   object  $view     The item object.
     * @param   string  $context  The context of the content being passed to the plugin.
     *
     * @return  void
     * @since   1.0.3
     */
    public function onBeforeDisplay($view, $context)
    {
        if ($context == 'com_users.profile') {
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
     * @since   1.0.3
     */
    protected function removeUnshownCustomFieldFromForm(Form $form)
    {
        foreach (self::$unshownFields as $unshownField) {
            $form->removeField($unshownField, 'com_fields');
        }
    }
}
