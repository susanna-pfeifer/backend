<?php

namespace C14r\Directus\Invoices\Endpoints;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;

use C14r\Directus\Base;
use Dompdf\Dompdf;
use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

class PDF extends Base
{
    public function __invoke(Request $request, Response $response)
    {
        $settings = $this->getSettings();
        $invoice = $this->getInvoice($request);

        //echo $this->createHtml($settings, $invoice->toArray());

    	$this->createPDF($settings, $invoice);
    }

    public function getSettings()
    {
        $params = [
            'single' => 1,
            'fields' => '*.*'
        ];

        return (object) $this->getItemsService()->findAll('settings', $params)['data'];
    }

    public function getInvoice($request)
    {
        $id = $request->getAttribute('id');

        $params = [
            'fields' => '*.*'
        ];

        return static::findByIds('invoices', $id, $params);
    }

    public function createHtml($settings, $invoice)
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../twig');
        $twig = new Environment($loader);
        $twig->addExtension(new IntlExtension());
        $template = $twig->load('index.html');
        return $template->render(compact('settings', 'invoice'));
    }

    public function createPDF($settings, $invoice)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->createHtml($settings, $invoice));
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4');
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Output the generated PDF to Browser
        $dompdf->stream($invoice['number'].'.pdf');
    }

    /*public function download($response, $file)
    {
        $fh = fopen($file, 'rb');

        $stream = new \Slim\Http\Stream($fh);

        return $response->withHeader('Content-Type', 'application/force-download')
                        ->withHeader('Content-Type', 'application/octet-stream')
                        ->withHeader('Content-Type', 'application/download')
                        ->withHeader('Content-Description', 'File Transfer')
                        ->withHeader('Content-Transfer-Encoding', 'binary')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
                        ->withHeader('Expires', '0')
                        ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                        ->withHeader('Pragma', 'public')
                        ->withBody($stream);
    }*/
}
