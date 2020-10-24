<?php

namespace C14r\Directus\Invoices;

use Directus\Hook\Payload;
use C14r\Directus\Base;

class Invoices extends Base
{
    public static function beforeUpdate(Payload $payload)
    {
        $id = $payload->get('id');

        $old = static::findByIds('invoices', $id);
        $new = static::mergePayloadAndOld($payload, $old);

        /*file_put_contents(__DIR__ . '/invoice.json', json_encode([
            'old' => $old->toArray(),
            'new' => $new->toArray(),
            'payload' => $payload->toArray()
        ], JSON_PRETTY_PRINT));*/

        if($new['status'] != 'draft' and empty($new['number']))
        {
            $payload->set('number', static::generateNumber($new));
        }

        return $payload;
    }

    public static function generateNumber($invoice)
    {
        $itemService = static::getItemsService();
        $invoices = $itemService->findAll('invoices', [
            'filter' => [
                'date' => [
                    'eq' => $invoice['date']
                ],
                'status' => [
                    'neq' => 'draft'
                ]
            ],
            'limit' => -1
        ]);

        return sprintf('RE-%s-%03d', date('Ymd', strtotime($invoice['date'])), count($invoices['data']) + 1);
    }
}
