<?php

namespace C14r\Directus\Invoices\Endpoints;

use Zend\Db\TableGateway\TableGateway;

use C14r\Directus\Base;
use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

class Import extends Base
{
    protected $gatewayCustomers;
    protected $gatewayInvoices;
    protected $gatewayPositions;

    public function __invoke(Request $request, Response $response)
    {
        print_r(\Directus\Config\Schema\Schema::get());
        //$this->import();
    }

    public function import()
    {
        $this->gatewayCustomers = new TableGateway('customers', $this->getDB());
        $this->gatewayInvoices = new TableGateway('invoices', $this->getDB());
        $this->gatewayPositions = new TableGateway('invoice_positions', $this->getDB());

        $this->truncate($this->gatewayCustomers);
        $this->truncate($this->gatewayInvoices);
        $this->truncate($this->gatewayPositions);

        $customers = $this->getFileContent();

        foreach ($customers as $customer) {
            $cID = $this->insertCustomer($customer);

            foreach ($customer->invoices as $invoice) {
                $iID = $this->insertInvoice($cID, $invoice);

                foreach ($invoice->positions as $position) {
                    $pID = $this->insertPosition($iID, $position);
                }
            }
        }

        die();
    }

    public function insertCustomer($customer)
    {
        $set = [
            'status'   => (count($customer->invoices) > 0) ? 'active' : 'inactive',
            'name'     => $customer->name,
            'name2'    => $customer->name2,
            'number'   => $customer->number,
            'type'     => $customer->type,
            'address1' => $customer->address->name,
            'address2' => $customer->address->address1,
            'street'   => $customer->address->street,
            'postcode' => $customer->address->postcode,
            'city'     => $customer->address->city
        ];

        $this->gatewayCustomers->insert($set);
        
        return $this->gatewayCustomers->lastInsertValue;
    }

    public function insertInvoice($customerID, $invoice)
    {
        if(is_null($invoice->date))
        {
            $invoice->date = date('Y-m-d');
        }

        $set = [
            'status'      => $invoice->status,
            'title'       => $invoice->title,
            'number'      => $invoice->number,
            'description' => $invoice->description,
            'date'        => $invoice->date,
            'due'         => $invoice->due,
            'customer'    => $customerID,
            'address1'    => $invoice->address->name,
            'address2'    => $invoice->address->address1,
            'street'      => $invoice->address->street,
            'postcode'    => $invoice->address->postcode,
            'city'        => $invoice->address->city,
        ];

        $this->gatewayInvoices->insert($set);
        
        return $this->gatewayInvoices->lastInsertValue;
    }

    public function insertPosition($invoiceID, $position)
    {
        $set = [
            'invoice'     => $invoiceID,
            'description' => $position->description,
            'quantity'    => $position->quantity,
            'unit'        => $position->unit,
            'price'       => $position->price,
            'total'       => $position->quantity * $position->price
        ];

        $this->gatewayPositions->insert($set);
        
        return $this->gatewayPositions->lastInsertValue;
    }

    public function truncate($gateway)
    {
        $query = $gateway->getAdapter()->query('TRUNCATE TABLE '.$gateway->getTable());
        $query->execute();
    }

    public function getFileContent()
    {
        return json_decode(file_get_contents($this->getFileName()));
    }

    public function getFileName()
    {
        return __DIR__ . '/../../data/export.json';
    }
}
