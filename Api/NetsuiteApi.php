<?php

namespace MauticPlugin\MauticNetsuiteBundle\Api;

use Mautic\PluginBundle\Exception\ApiErrorException;
use NetSuite\Classes\Customer;
use NetSuite\NetSuiteService;

class NetsuiteApi extends CrmApi
{
    protected $element = 'Leads';

    protected function request($operation, $request)
    {

        $keys = $this->integration->getKeys();

        $config = array(
            // required -------------------------------------
            "endpoint" => $keys['endpoint'],
            "host"     => "https://webservices.netsuite.com",
            "email"    => $keys['email'],
            "password" => $keys['password'],
            "role"     => "3",
            "account"  => "MYACCT1",
            "app_id"   => $keys['api_id'],
        );

        $service = new NetSuiteService($config);

        $addResponse = $service->$operation($request);

        if (!$addResponse->writeResponse->status->isSuccess) {
            throw new ApiErrorException('error');
        } else {
            return $addResponse->writeResponse->baseRef;
        }
    }

    /**
     * List leads.
     *
     * @return mixed
     */
    public function getLeadFields($object)
    {
       /* if ($object === 'company') {
            $object = 'Accounts';
        } else {
            $object = $this->element;
        }*/

        $request = new AddRequest();

        return $this->request('get', $this->element);
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

        return $this->request('add', $this->element);
    }
}
