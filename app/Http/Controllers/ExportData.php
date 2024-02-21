<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\Adapters\GoogleSheet;
use App\Exports\AllDataExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExportData extends Controller
{
    /** @var GoogleSheet */
    private $client;

    /** @var AllDataExporter */
    private $exporter;

    public function __construct()
    {
        $this->client = new GoogleSheet();
        $this->exporter = new AllDataExporter();
    }

    /**
     * @param  Request  $request
     * @return View
     */
    public function exportView(Request $request): View
    {
        $authError = $request->session()->get('authError');
        $authResult = $request->session()->get('authResult');

        $view = view('pages.export-data', [
            'sheetUrl' => $this->client->sheetUrl(),
            'authorization' => $authResult ?: $this->client->checkAuthorization(),
        ]);

        if ($authError) {
            $view->withErrors(['An error has occured. Please try again.']);
        }

        return $view;
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function authorizeGoogleClient(Request $request): RedirectResponse
    {
        $code = $request->get('code');

        if (! $code) {
            $request->session()->flash('authError', true);
        }

        $authentication = $this->client->authenticateCode($code);

        if (! $authentication['success']) {
            $request->session()->flash('authError', true);
            $request->session()->flash('authResult', $authentication);
        }

        return redirect(route('export-data-view'));
    }

    /**
     * @return JsonResponse
     */
    public function exportStart(): JsonResponse
    {
        $this->exporter->deleteManifest()->createManifest();

        $manifest = $this->exporter->manifest()->value;

        $this->client->emptySheet($manifest);

        $manifest = $this->exporter->manifest()->value;

        if ($this->client->error) {
            $manifest['error'] = $this->client->error->getMessage();
        }

        return response()->json($manifest);
    }

    /**
     * @return JsonResponse
     */
    public function processChunk(): JsonResponse
    {
        $this->exporter->processChunk($this->client);
        $manifest = $this->exporter->manifest()->value;

        if ($this->client->error) {
            $manifest['error'] = $this->client->error->getMessage();
        }

        return response()->json($manifest);
    }
}
