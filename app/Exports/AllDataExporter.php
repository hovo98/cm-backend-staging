<?php

declare(strict_types=1);

namespace App\Exports;

use App\Config;
use App\Exports\Adapters\GoogleSheet;
use App\Exports\Sheets\AllConnections;
use App\Exports\Sheets\AllDeals;
use App\Exports\Sheets\AllLenderDealsPreferences;
use App\Exports\Sheets\AllQuotes;
use App\Exports\Sheets\AllReferrals;
use App\Exports\Sheets\SheetInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * Class AllDataExport
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllDataExporter
{
    private const CONFIG_EXPORT_MANIFEST_KEY = 'CONFIG_EXPORT_MANIFEST';

    public const CHUNK_SIZE = 100;

    /** @var Collection */
    private $sheets;

    /** @var array */
    private $manifest;

    /**
     * @return Collection
     */
    public function sheets(): Collection
    {
        if ($this->sheets) {
            return $this->sheets;
        }

        $allDeals = new AllDeals();
        $allQuotes = new AllQuotes();
        $allConnections = new AllConnections();
        $allLenderDealsPreferences = new AllLenderDealsPreferences();
        $allReferrals = new AllReferrals();

        $this->sheets = collect([
            $allDeals->camelTitle() => $allDeals,
            $allQuotes->camelTitle() => $allQuotes,
            $allConnections->camelTitle() => $allConnections,
            $allLenderDealsPreferences->camelTitle() => $allLenderDealsPreferences,
            $allReferrals->camelTitle() => $allReferrals,
        ]);

        return $this->sheets;
    }

    public function processChunk(GoogleSheet $client)
    {
        if (empty($this->manifest()->getAttribute('value')['sheets'])) {
            $client->error = new \Exception('No sheets registered, unable to proceed.');

            return;
        }

        $currentSheetManifest = collect($this->manifest()->getAttribute('value')['sheets'])
            ->firstWhere('completed', false);

        if ($currentSheetManifest['chunksProcessed'] === 0) {
            $client->insertHeadings($currentSheetManifest['title'], $currentSheetManifest['headings']);
        }

        /** @var LazyCollection $chunk */
        $chunk = (new $currentSheetManifest['class']())->prepareChunk($currentSheetManifest)->toArray();

        $client->appendRows($currentSheetManifest['title'], $chunk);

        if ($client->error) {
            return;
        }

        $count = count($chunk);

        $this->updateManifest($currentSheetManifest['key'], $count);
    }

    public function updateManifest(string $sheetKey, int $exported)
    {
        $manifest = $this->manifest()->getAttribute('value');

        $manifest['sheets'][$sheetKey]['chunksProcessed'] += 1;
        $manifest['sheets'][$sheetKey]['completed'] =
            $manifest['sheets'][$sheetKey]['chunksProcessed'] === $manifest['sheets'][$sheetKey]['chunks'];
        $manifest['sheets'][$sheetKey]['exported'] += $exported;
        $manifest['exported'] += $exported;
        $manifest['chunksLeft'] = $manifest['chunksLeft'] > 0 ? $manifest['chunksLeft'] - 1 : 0;
        $manifest['completed'] = $manifest['chunksLeft'] === 0;

        $this->manifest()->setAttribute('value', $manifest)->save();
    }

    public function createManifest()
    {
        $chunksTotal = 0;
        $totalRows = 0;

        $sheets = $this->sheets()->map(function (SheetInterface $sheet) use (&$chunksTotal, &$totalRows) {
            $chunks = intval(ceil($sheet->count() / self::CHUNK_SIZE));
            $chunksTotal += $chunks;
            $totalRows += $sheet->count();

            return [
                'class' => get_class($sheet),
                'key' => $sheet->camelTitle(),
                'title' => $sheet->title(),
                'exported' => 0,
                'total' => $sheet->count(),
                'chunks' => $chunks,
                'chunksProcessed' => 0,
                'headings' => $sheet->headings()->toArray(),
                'completed' => $chunks === 0,
            ];
        });

        $manifest = [
            'started' => time(),
            'sheets' => $sheets->toArray(),
            'exported' => 0,
            'total' => $totalRows,
            'chunkSize' => self::CHUNK_SIZE,
            'chunksTotal' => $chunksTotal,
            'chunksLeft' => $chunksTotal,
            'completed' => false,
        ];

        $this->manifest()->setAttribute('value', $manifest)->save();

        return $this;
    }

    public function deleteManifest()
    {
        $this->manifest()->setAttribute('value', [])->save();

        return $this;
    }

    public function manifest()
    {
        // Lazy load manifest
        if ($this->manifest === null) {
            $this->manifest = Config::query()->where('key', '=', self::CONFIG_EXPORT_MANIFEST_KEY)->first();

            // If it is not yet created, create an empty one now
            if ($this->manifest === null) {
                $this->manifest = (new Config([
                    'key' => self::CONFIG_EXPORT_MANIFEST_KEY,
                    'value' => [],
                ]))->save();
            }
        }

        return $this->manifest;
    }
}
