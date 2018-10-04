<?php

namespace MauticPlugin\MauticNetSuiteBundle\Api;

use Mautic\PluginBundle\Exception\ApiErrorException;
use MauticPlugin\MauticCrmBundle\Api\CrmApi;
use NetSuite\Classes\AddRequest;
use NetSuite\Classes\Customer;
use NetSuite\Classes\GetRequest;
use NetSuite\Classes\RecordRef;
use NetSuite\NetSuiteService;

class NetSuiteApi extends CrmApi
{
    const ALLOWED_FIELDS_TYPE = ['string', 'boolean','integer', 'float', 'dateTime'];
    const SKIPPED_FIELDS = ['password', 'password2'];
    const REQUIRED_FIELDS = ['email', 'companyName'];

    protected function request($operation, $request)
    {
        $keys = $this->integration->getKeys();
        $config = array(
            // required -------------------------------------
            "email"    => $keys['email'],
            "password" => $keys['password'],
            "account"  => $keys['account_id'],
            "role"     => "",
            "endpoint" => '2017_1',
            "host"     => "https://webservices.netsuite.com",
            "app_id"   => '4AD027CA-88B3-46EC-9D3E-41C6E6A325E2',
        );

        $service = new NetSuiteService($config);
        $addResponse = $service->$operation($request);
        if (!$addResponse || !$addResponse->writeResponse->status->isSuccess) {
            if ($addResponse->writeResponse->status->statusDetail[0]) {
                throw new ApiErrorException($addResponse->writeResponse->status->statusDetail[0]->message);
            } else {
                throw new ApiErrorException('error');
            }
        } else {
            return $addResponse->writeResponse->baseRef;
        }
    }

    /**
     * List leads.
     *
     * @return mixed
     */
    public function getLeadFields()
    {
        $keys = Customer::$paramtypesmap;
        $fields = [];
        foreach ($keys as $key=>$type) {
            if (in_array($type, self::ALLOWED_FIELDS_TYPE) && !in_array($key, self::SKIPPED_FIELDS)) {
                $fields[$key] = [
                    'type'  => 'string',
                    'label' => $key,
                ];
                if (in_array($key, self::REQUIRED_FIELDS)) {
                    $fields[$key]['required'] = true;
                }
            }
        }
        return $fields;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function createLead(array $data)
    {
        $customer = new Customer();
        setFields($customer, $data);

        $request = new AddRequest();
        $request->record = $customer;

        return $this->request('add', $request);
    }
}
