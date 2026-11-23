<?php

declare(strict_types=1);

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Profile;
use Illuminate\Database\Migrations\Migration;

/*
| Fills in the start/end months (from the CV) on the previous jobs already saved
| in the dashboard, matched by company name. They sort the jobs on the home
| page. Only empty fields are filled, so dates edited in the dashboard are
| never overwritten.
*/
return new class extends Migration
{
    public function up(): void
    {
        $dates = [];

        foreach (Profile::seedPrevious() as $job) {
            $dates[(string) $job['company']] = ['start' => $job['start'], 'end' => $job['end']];
        }

        foreach (ContentSection::query()->section('previous_jobs')->get() as $row) {
            $this->fill($row, $dates[(string) ($row->data['company'] ?? '')] ?? []);
        }

        Content::flush();
    }

    public function down(): void
    {
        foreach (ContentSection::query()->section('previous_jobs')->get() as $row) {
            $data = $row->data;
            unset($data['start'], $data['end']);
            $row->update(['data' => $data]);
        }

        Content::flush();
    }

    /**
     * @param  array<string, string>  $values
     */
    private function fill(ContentSection $row, array $values): void
    {
        $data = $row->data;

        foreach ($values as $key => $value) {
            if (trim((string) ($data[$key] ?? '')) === '') {
                $data[$key] = $value;
            }
        }

        if ($data !== $row->data) {
            $row->update(['data' => $data]);
        }
    }
};
