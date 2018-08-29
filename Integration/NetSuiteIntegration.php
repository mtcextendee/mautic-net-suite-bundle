<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticNetSuiteBundle\Integration;

use MauticPlugin\MauticCrmBundle\Integration\CrmAbstractIntegration;

/**
 * Class NetSuiteIntegration.
 */
class NetSuiteIntegration extends CrmAbstractIntegration
{
    private $authorzationError = '';
    private $apiHelper;

    /**
     * Returns the name of the social integration that must match the name of the file.
     *
     * @return string
     */
    public function getName()
    {
        return 'NetSuite';
    }

    /**
     * @return array
     */
    public function getSupportedFeatures()
    {
        return ['push_lead'];
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'NetSuite';
    }

    public function sortFieldsAlphabetically()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'email' => 'mautic.netsuite.form.email',
            'password' => 'mautic.netsuite.form.password',
            'account_id'   => 'mautic.netsuite.form.account.id',
        ];
    }


    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getSecretKeys()
    {
        return [
            'password',
        ];
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }


    /**
     * @return array|mixed
     */
    public function getAvailableLeadFields($settings = [])
    {

        $netSuiteFields      = [];
        $silenceExceptions = (isset($settings['silence_exceptions'])) ? $settings['silence_exceptions'] : true;

        if (isset($settings['feature_settings']['objects'])) {
            $newStuiteObjects = $settings['feature_settings']['objects'];
        } else {
            $settings      = $this->settings->getFeatureSettings();
            $newStuiteObjects = isset($settings['objects']) ? $settings['objects'] : ['contacts'];
        }

        try {
            if ($this->isAuthorized()) {
                if (!empty($newStuiteObjects) && is_array($newStuiteObjects)) {
                    foreach ($newStuiteObjects as $object) {
                        // The object key for contacts should be 0 for some BC reasons
                        if ($object == 'contacts') {
                            $object = 0;
                        }

                        // Create the array if it doesn't exist to prevent PHP notices
                        if (!isset($netSuiteFields[$object])) {
                            $netSuiteFields[$object] = [];
                        }

                        $netSuiteFields = $this->getApiHelper()->getLeadFields();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logIntegrationError($e);

            if (!$silenceExceptions) {
                throw $e;
            }
        }

        return $netSuiteFields;
    }

    /**
     * {@inheritdoc}
     *
     * @param $mappedData
     */
    public function amendLeadDataBeforePush(&$mappedData)
    {
        if (!empty($mappedData)) {
            //vtiger requires assigned_user_id so default to authenticated user
        }
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features') {
            $builder->add(
                'objects',
                'choice',
                [
                    'choices' => [
                        'contacts' => 'mautic.vtiger.object.contact',
                    ],
                    'expanded'    => true,
                    'multiple'    => true,
                    'label'       => 'mautic.vtiger.form.objects_to_pull_from',
                    'label_attr'  => ['class' => ''],
                    'empty_value' => false,
                    'required'    => false,
                ]
            );
        }
    }

    /**
     * Get available company fields for choices in the config UI.
     *
     * @param array $settings
     *
     * @return array
     */
    public function getFormCompanyFields($settings = [])
    {
        return parent::getAvailableLeadFields(['cache_suffix' => '.company']);
    }

    /**
     * Get the API helper.
     *
     * @return CrmApi
     */
    public function getApiHelper()
    {
        if (empty($this->apiHelper)) {
            $class        = '\\MauticPlugin\\MauticNetSuiteBundle\\Api\\'.$this->getName().'Api';
            $this->apiHelper = new $class($this);
        }

        return $this->apiHelper;
    }
}
