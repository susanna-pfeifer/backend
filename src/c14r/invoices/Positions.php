<?php

namespace C14r\Directus\Invoices;

use C14r\Directus\Base;
use Directus\Hook\Payload;

class Positions extends Base
{
    public static function beforeUpdate(Payload $payload)
    {
        $id = $payload->get('id');

        $old = static::findByIds('invoice_positions', $id);
        $new = static::mergePayloadAndOld($payload, $old);

        /*file_put_contents(__DIR__ . '/invoice_positions_' . $old->get('id') . '.json', json_encode([
            'old' => $old->toArray(),
            'new' => $new->toArray(),
            'payload' => $payload->toArray()
        ], JSON_PRETTY_PRINT));*/

        $payload->set('total', $new->get('quantity') * $new->get('price'));

        return $payload;
    }
}
