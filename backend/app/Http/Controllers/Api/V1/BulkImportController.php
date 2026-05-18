<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\MediaOutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BulkImportController extends Controller
{
    private const REQUIRED_CSV_COLS = ['name', 'city_id', 'url', 'type', 'language'];

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,json|max:2048',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        $rows = $ext === 'json'
            ? $this->parseJson($file->getContent())
            : $this->parseCsv($file->getContent());

        $imported = 0;
        $errors   = 0;
        $results  = [];

        DB::transaction(function () use ($rows, &$imported, &$errors, &$results) {
            foreach ($rows as $i => $row) {
                $rowNum = $i + 2; // 1-indexed, skipping header

                $validator = Validator::make($row, [
                    'name'         => 'required|string|max:255',
                    'city_id'      => 'required|integer|exists:cities,id',
                    'url'          => 'required|url|max:512',
                    'type'         => 'required|in:' . implode(',', MediaOutlet::TYPES),
                    'language'     => 'required|string|in:' . implode(',', MediaOutlet::LANGUAGES),
                    'description'  => 'nullable|string|max:5000',
                    'logo_url'     => 'nullable|url|max:512',
                    'founded_year' => 'nullable|integer|min:1600|max:2100',
                ]);

                if ($validator->fails()) {
                    $errors++;
                    $results[] = [
                        'row'    => $rowNum,
                        'name'   => $row['name'] ?? '(unknown)',
                        'status' => 'error',
                        'error'  => implode(' | ', $validator->errors()->all()),
                    ];
                    continue;
                }

                $data = $validator->validated();
                MediaOutlet::updateOrCreate(
                    ['slug' => Str::slug($data['name'])],
                    array_merge($data, [
                        'slug'         => Str::slug($data['name']),
                        'is_active'    => true,
                        'is_featured'  => false,
                        'founded_year' => isset($data['founded_year']) ? (int)$data['founded_year'] : null,
                    ])
                );

                $imported++;
                $results[] = [
                    'row'    => $rowNum,
                    'name'   => $data['name'],
                    'status' => 'ok',
                ];
            }
        });

        return response()->json([
            'data' => [
                'imported' => $imported,
                'errors'   => $errors,
                'rows'     => $results,
            ],
        ]);
    }

    private function parseCsv(string $content): array
    {
        $lines  = array_filter(explode("\n", trim($content)));
        $header = array_map('trim', str_getcsv(array_shift($lines)));
        $rows   = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($header)) continue;
            $rows[] = array_combine($header, array_map('trim', $values));
        }

        return $rows;
    }

    private function parseJson(string $content): array
    {
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
}
