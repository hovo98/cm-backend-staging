<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class SheetImport
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class SheetImport extends Controller
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function getData(Request $request): JsonResponse
    {
        $url = $request->input('url');

        if (! $url) {
            throw new \Exception('Parameter <i>url</i> is missing!');
        }

        $sheetKey = self::getSheetKey($url);
        $url = "https://docs.google.com/spreadsheets/d/${sheetKey}/export?format=csv";

        // Get the file
        $csv = fopen($url, 'r');

        if (! $csv) {
            Log::error('File not available. Please check the URL.');

            return response()->json(['error' => 'An error occured, please try again. If problem persists, contact the development team for assistance.'], 404);
        }

        // Set the flag for the first row (header)
        $first = true;

        $collected_data = [];

        // Loop through the sheet rows
        while (($data = fgetcsv($csv)) !== false) {
            // Skip the header row
            if ($first) {
                $first = false;

                continue;
            }

            // Collect relevant data
            $name = $data[0];
            $domains = $data[4];

            // Skip rows where domain doesn't exist
            if (! $domains) {
                continue;
            }

            // Break up the domains (if separator is present)
            $domains = array_map('trim', explode('/', $domains));

            // Loop through the domains (even if there is only one)
            foreach ($domains as $domain) {
                $collected_data[] = [$name, $domain];
            }
        }

        return response()->json($collected_data);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public static function processChunk(Request $request): JsonResponse
    {
        // Get config data from request
        $chunk = $request->input('data');

        $chunk = json_decode($chunk);

        // Counters
        $success = 0;
        $exists = 0;
        $problem = 0;

        foreach ($chunk as $row) {
            $name = $row[0];
            $domain = $row[1];

            // Check if the company with the same domain already exists
            $company = Company::where('domain', $domain)->first();
            $existing = true;

            // If not, create the new one
            if (! $company) {
                // Create new Company
                $company = new Company();
                $existing = false;
            }

            $company->company_name = $name;
            $company->domain = $domain;
            $company->is_approved = true;

            // Using saveOrFail() to get the error message from the Exception
            try {
                if ($company->saveOrFail()) {
                    $existing ? $exists++ : $success++;
                } else {
                    Log::error('Error importing domain.', ['name' => $name, 'domain' => $domain, 'existing' => $existing]);
                    $problem++;
                }
            } catch (\Throwable $e) {
                Log::error('Error importing domain.', ['name' => $name, 'domain' => $domain, 'existing' => $existing, 'exception' => $e]);
                $problem++;
            }
        }

        return response()->json([
            'success' => $success,
            'exists' => $exists,
            'problem' => $problem,
        ]);
    }

    /**
     * @param  string  $url
     * @return string
     */
    private static function getSheetKey(string $url): string
    {
        preg_match('/d\/(.*)\/edit/', $url, $m);

        return $m[1] ?? '';
    }
}
