<?php

use Directus\Hook\Payload;

use C14r\Directus\Invoices\Invoices;
use C14r\Directus\Invoices\Positions;
use C14r\Directus\Customers\Customers;

return [
    'actions' => [
        'application.boot' => function () {
            //Application::boot();
        }
    ],
    'filters' => [
        'item.read.invoices' => function (Payload $payload) {
            $arr = $payload->getData();

            foreach ($arr as $key => $invoice) {
                if(isset($invoice['status']) and $invoice['status'] == 'due' and isset($invoice['due']) and $invoice['due'] < date('Y-m-d'))
                {
                    $invoice['status'] = 'over_due';
                    $payload->set((int)$key, $invoice);
                }
            }

            return $payload;
        },
        'item.create.customers:before' => function (Payload $payload) {
            return Customers::beforeCreate($payload);
        },
        'item.update.invoices:before' => function (Payload $payload) {
            return Invoices::beforeUpdate($payload);
        },
        'item.update.invoice_positions:before' => function (Payload $payload) {
            return Positions::beforeUpdate($payload);
        }
    ]
];
