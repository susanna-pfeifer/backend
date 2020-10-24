<?php

namespace C14r\Directus\Customers;

use Directus\Hook\Payload;
use C14r\Directus\Base;

class Customers extends Base
{
    public static function beforeCreate(Payload $payload)
    {
        $payload->set('number', static::generateNumber($payload->get('type', 'PK')));

        return $payload;
    }

    public static function generateNumber($type)
    {
        $itemService = static::getItemsService();
        $customers = $itemService->findAll('customers', [
            'filter' => [
                'type' => [
                    'eq' => $type
                ]
            ],
            'limit' => -1
        ]);

        $count = count($customers['data']) + 1;

        switch($type)
        {
            case 'GK':
                $count += 30000;
                break;
            case 'PK':
                $count += 200000;
                break;
            case 'EK':
                $count += 100000;
                break;
        }

        return sprintf('%s-%05d', $type, $count);
    }
}
