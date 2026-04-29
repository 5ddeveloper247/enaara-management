<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UniversityDirectoryService
{
    public function pakistanUniversities(): array
    {
        $cacheKey = 'pakistan_universities_directory_v1';
        $cached = Cache::get($cacheKey, []);
        if (is_array($cached) && count($cached) > 0) {
            return $cached;
        }

        $recognized = $this->supplementalUniversities();
        $officialHec = $this->fetchOfficialHecUniversities();
        if (count($officialHec) > 0) {
            $combined = $this->normalizeAndSort(array_merge($recognized, $officialHec));
            if (count($combined) > 0) {
                Cache::put($cacheKey, $combined, now()->addHours(12));
                $recognized = $combined;
            }
        }

        $recognizedMap = $this->buildRecognizedMap($recognized);

        $endpoints = [
            'https://universities.hipolabs.com/search',
            'http://universities.hipolabs.com/search',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::timeout(8)->retry(1, 300)->get($endpoint, [
                    'country' => 'Pakistan',
                ]);

                if (! $response->ok()) {
                    continue;
                }

                $rows = $response->json();
                if (! is_array($rows)) {
                    continue;
                }

                $apiNames = collect($rows)
                    ->map(fn ($row) => is_array($row) ? trim((string) ($row['name'] ?? '')) : '')
                    ->filter(fn ($name) => $name !== '')
                    ->values()
                    ->all();

                $apiMatchedRecognized = $this->filterRecognizedFromApi($apiNames, $recognizedMap);
                $names = $this->normalizeAndSort(array_merge($recognized, $apiMatchedRecognized));

                if (is_array($names) && count($names) > 0) {
                    Cache::put($cacheKey, $names, now()->addHours(12));
                    return $names;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $fallback = $this->normalizeAndSort($recognized);
        if (count($fallback) > 0) {
            Cache::put($cacheKey, $fallback, now()->addHours(12));
            return $fallback;
        }

        return is_array($cached) ? $cached : [];
    }

    private function fetchOfficialHecUniversities(): array
    {
        $urls = [
            'https://www.hec.gov.pk/english/universities/pages/recognised.aspx',
            'https://www.hec.gov.pk/english/universities/Pages/DAIs/HEC-recognized-Campuses.aspx',
        ];

        $all = [];
        foreach ($urls as $url) {
            try {
                $response = Http::timeout(15)
                    ->retry(1, 300)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->withOptions(['verify' => false])
                    ->get($url);

                if (! $response->ok()) {
                    continue;
                }

                $all = array_merge($all, $this->extractUniversityLikeNames($response->body()));
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $this->normalizeAndSort($all);
    }

    private function extractUniversityLikeNames(string $html): array
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//a|//li|//td|//span');
        if (! $nodes) {
            return [];
        }

        $names = [];
        foreach ($nodes as $node) {
            $text = $this->cleanDisplayName((string) $node->textContent);
            if ($text === '' || mb_strlen($text) < 6) {
                continue;
            }

            $isNarrativeNoise = preg_match('/\b(upgraded|intake\s+stopped|admissions?\s+stopped|afterwards?|w\.?\s*e\.?\s*f\.?|fall\s+\d{4})\b/i', $text) === 1;
            if ($isNarrativeNoise) {
                continue;
            }

            $hasUniversityKeyword = preg_match('/\b(university|institute|college|academy)\b/i', $text) === 1;
            if (! $hasUniversityKeyword) {
                continue;
            }

            $isNoise = preg_match('/\b(sign in|search|download|default|sector|province|discipline|chartered|city|hec)\b/i', $text) === 1;
            if ($isNoise) {
                continue;
            }

            $names[] = $text;
        }

        return $this->normalizeAndSort($names);
    }

    private function filterRecognizedFromApi(array $apiNames, array $recognizedMap): array
    {
        $matched = [];
        foreach ($apiNames as $apiName) {
            $key = $this->normalizedKey((string) $apiName);
            if ($key !== '' && isset($recognizedMap[$key])) {
                $matched[] = $recognizedMap[$key];
            }
        }
        return $matched;
    }

    private function buildRecognizedMap(array $recognized): array
    {
        $map = [];
        foreach ($recognized as $name) {
            $key = $this->normalizedKey((string) $name);
            if ($key !== '' && !isset($map[$key])) {
                $map[$key] = $name;
            }
        }
        return $map;
    }

    private function normalizedKey(string $name): string
    {
        $v = mb_strtolower($this->cleanDisplayName($name));
        $v = str_replace(['&', '+'], [' and ', ' plus '], $v);
        $v = preg_replace('/[\(\)\[\],.;:"`]/u', ' ', (string) $v);
        $v = preg_replace('/\s+/u', ' ', (string) $v);
        return trim((string) $v);
    }

    private function cleanDisplayName(string $name): string
    {
        $v = trim($name);
        if ($v === '') {
            return '';
        }

        $v = html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $v = preg_replace('/\[[^\]]*]/u', ' ', (string) $v);
        $v = preg_replace('/\(\s*upgraded[^)]*\)/iu', ' ', (string) $v);
        $v = preg_replace('/\(\s*admissions?[^)]*\)/iu', ' ', (string) $v);
        $v = preg_replace('/\bw\.?\s*e\.?\s*f\.?[^,)]*/iu', ' ', (string) $v);
        $v = preg_replace('/\band afterwards campus has been upgraded to fullfledge university\b.*$/iu', ' ', (string) $v);
        $v = preg_replace('/\bintake stopped after fall\s*\d{4}\b.*$/iu', ' ', (string) $v);
        $v = preg_replace('/\s+/u', ' ', (string) $v);
        $v = trim((string) $v, " \t\n\r\0\x0B,;-");

        if (preg_match('/^comsats institute of information technology\b/i', $v) === 1) {
            $v = preg_replace('/^comsats institute of information technology\b[\s,]*/i', 'COMSATS University Islamabad, ', $v);
            $v = preg_replace('/^COMSATS University Islamabad,\s*Islamabad$/i', 'COMSATS University Islamabad', $v);
        }

        $provinceSuffixPattern = '/\s+(islamabad capital territory|khyber pakhtunkhwa|punjab|sindh|balochistan|azad jammu\s*&\s*kashmir|gilgit baltistan)$/iu';
        $protectedProvinceNames = [
            'University of Sindh',
            'University of the Punjab',
            'University of Balochistan',
        ];
        $isProtected = false;
        foreach ($protectedProvinceNames as $protectedName) {
            if (stripos($v, $protectedName) === 0) {
                $isProtected = true;
                break;
            }
        }
        if (! $isProtected) {
            $v = preg_replace($provinceSuffixPattern, '', (string) $v);
        }

        // Remove accidental duplicated trailing tokens e.g. "University of Sindh Sindh"
        $v = preg_replace('/\b([A-Za-z]+)\s+\1$/u', '$1', (string) $v);

        $v = preg_replace('/\s+/u', ' ', (string) $v);
        return trim((string) $v, " \t\n\r\0\x0B,;-");
    }

    private function normalizeAndSort(array $names): array
    {
        $unique = [];
        foreach ($names as $name) {
            $clean = $this->cleanDisplayName((string) $name);
            if ($clean === '') {
                continue;
            }
            $key = $this->normalizedKey($clean);
            if ($key === '' || isset($unique[$key])) {
                continue;
            }
            $unique[$key] = $clean;
        }

        $values = array_values($unique);
        $campusBases = [];
        foreach ($values as $entry) {
            if (stripos($entry, ' campus') === false) {
                continue;
            }
            $parts = explode(',', $entry, 2);
            $base = trim((string) ($parts[0] ?? ''));
            if ($base !== '') {
                $campusBases[$this->normalizedKey($base)] = true;
            }
        }

        $filtered = array_values(array_filter($values, function (string $entry) use ($campusBases) {
            $entryKey = $this->normalizedKey($entry);
            if (!isset($campusBases[$entryKey])) {
                return true;
            }
            return stripos($entry, ' campus') !== false;
        }));

        $baseNameKeys = [];
        foreach ($filtered as $entry) {
            if (strpos($entry, ',') !== false) {
                continue;
            }
            $baseNameKeys[$this->normalizedKey($entry)] = true;
        }

        $locationSuffixes = [
            'islamabad',
            'rawalpindi',
            'lahore',
            'karachi',
            'peshawar',
            'quetta',
            'faisalabad',
            'multan',
            'hyderabad',
            'sukkur',
            'gujranwala',
            'sahiwal',
            'vehari',
            'wah',
            'attock',
            'abbottabad',
            'sindh',
            'punjab',
            'khyber pakhtunkhwa',
            'balochistan',
            'ajk',
            'gilgit baltistan',
            'islamabad capital territory',
        ];

        $filtered = array_values(array_filter($filtered, function (string $entry) use ($baseNameKeys, $locationSuffixes) {
            if (stripos($entry, ' campus') !== false) {
                return true;
            }
            if (strpos($entry, ',') === false) {
                return true;
            }

            $parts = array_map('trim', explode(',', $entry));
            if (count($parts) !== 2) {
                return true;
            }

            $base = $parts[0];
            $suffix = mb_strtolower($parts[1]);
            $isLocationSuffix = in_array($suffix, $locationSuffixes, true);
            if (! $isLocationSuffix) {
                return true;
            }

            $baseKey = $this->normalizedKey($base);
            if ($baseKey !== '' && isset($baseNameKeys[$baseKey])) {
                return false;
            }

            return true;
        }));

        // Remove "base + city/province" duplicates when same base university exists.
        $filteredSet = [];
        foreach ($filtered as $entry) {
            $filteredSet[$this->normalizedKey($entry)] = true;
        }
        $filtered = array_values(array_filter($filtered, function (string $entry) use ($filteredSet) {
            if (stripos($entry, ' campus') !== false) {
                return true;
            }
            if (strpos($entry, ',') !== false) {
                $parts = array_map('trim', explode(',', $entry));
                if (count($parts) === 2) {
                    $baseKey = $this->normalizedKey($parts[0]);
                    if ($baseKey !== '' && isset($filteredSet[$baseKey])) {
                        return false;
                    }
                }
            }
            return true;
        }));

        // Remove short/generic variant when a fuller "X University of Y" exists.
        $allNames = $filtered;
        $filtered = array_values(array_filter($filtered, function (string $entry) use ($allNames) {
            if (!preg_match('/^(.+ University)$/i', $entry, $m)) {
                return true;
            }
            $base = trim($m[1]);
            foreach ($allNames as $candidate) {
                if (strcasecmp($candidate, $entry) === 0) {
                    continue;
                }
                if (stripos($candidate, $base . ' of ') === 0) {
                    return false;
                }
            }
            return true;
        }));

        return collect($filtered)
            ->sort(fn (string $a, string $b) => strcasecmp($a, $b))
            ->values()
            ->all();
    }

    private function supplementalUniversities(): array
    {
        return [
            'Abdul Wali Khan University',
            'Abasyn University',
            'Abbottabad University of Science and Technology (AUST)',
            'Aga Khan University',
            'Air University',
            'Al-Ghazali University',
            'Al-Kawthar University, Karachi',
            'Al-Qadir University Project Trust',
            'Allama Iqbal Open University',
            'Bahauddin Zakariya University, Multan',
            'Bacha Khan University',
            'Bahria University',
            'Baqai Medical University',
            'Capital University of Science and Technology',
            'COMSATS University Islamabad',
            'COMSATS University Islamabad, Islamabad Campus',
            'COMSATS University Islamabad, Lahore Campus',
            'COMSATS University Islamabad, Abbottabad Campus',
            'COMSATS University Islamabad, Attock Campus',
            'COMSATS University Islamabad, Wah Campus',
            'COMSATS University Islamabad, Sahiwal Campus',
            'COMSATS University Islamabad, Vehari Campus',
            'Dawood University of Engineering and Technology',
            'DHA Suffa University',
            'Dow University of Health Sciences',
            'FAST - National University of Computer and Emerging Sciences (NUCES)',
            'Fatima Jinnah Women University',
            'Forman Christian College',
            'Ghulam Ishaq Khan Institute of Engineering Sciences and Technology',
            'Government College University Faisalabad',
            'Government College University Lahore',
            'Government College Women University Faisalabad',
            'Hazara University',
            'Information Technology University, Lahore',
            'Institute of Business Administration (IBA) Karachi',
            'Institute of Business Administration, Sukkur',
            'Institute of Space Technology',
            'International Islamic University, Islamabad',
            'Iqra University',
            'Islamia University of Bahawalpur',
            'Karachi School of Business and Leadership',
            'Kinnaird College for Women University',
            'Kohat University of Science and Technology',
            'Lahore College for Women University',
            'Lahore University of Management Sciences',
            'Liaquat University of Medical and Health Sciences',
            'Mehran University of Engineering and Technology',
            'Mir Chakar Khan Rind University',
            'Muhammad Nawaz Sharif University of Engineering and Technology, Multan',
            'Muhammad Nawaz Sharif University of Agriculture, Multan',
            'National College of Arts',
            'National Defence University',
            'National University of Computer and Emerging Sciences',
            'National University of Medical Sciences',
            'National University of Modern Languages',
            'National University of Sciences and Technology',
            'NED University of Engineering and Technology',
            'Pakistan Institute of Development Economics',
            'Pakistan Institute of Engineering and Applied Sciences',
            'Pir Mehr Ali Shah Arid Agriculture University, Rawalpindi',
            'Pir Mehr Ali Shah Arid Agriculture University, Murree Campus',
            'Quaid-i-Azam University',
            'Riphah International University',
            'Shaheed Benazir Bhutto Women University',
            'Shaheed Zulfiqar Ali Bhutto Medical University',
            'Sukkur IBA University',
            'University of Agriculture Faisalabad',
            'University of Balochistan',
            'University of Central Punjab',
            'University of Engineering and Technology, Lahore',
            'University of Engineering and Technology, Peshawar',
            'University of Engineering and Technology, Taxila',
            'University of Gujrat',
            'University of Haripur',
            'University of Health Sciences, Lahore',
            'University of Jhang',
            'University of Karachi',
            'University of Lahore',
            'University of Malakand',
            'University of Management and Technology',
            'University of Mianwali',
            'University of Narowal',
            'University of Okara',
            'University of Peshawar',
            'University of Poonch Rawalakot',
            'University of Sahiwal',
            'University of Sargodha',
            'University of Sindh',
            'University of Swabi',
            'University of the Punjab',
            'University of the Punjab, Gujranwala Campus',
            'University of the Punjab, Jhelum Campus',
            'University of the Punjab, Khanspur Campus',
            'University of the Punjab, PUGC',
            'University of the Punjab, Quaid-e-Azam Campus',
            'University of Veterinary and Animal Sciences, Lahore',
            'University of Wah',
            'Virtual University of Pakistan',
            'Women University Mardan',
            'Women University Multan',
            'Women University Swabi',
        ];
    }
}
