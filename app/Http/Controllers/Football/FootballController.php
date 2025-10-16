<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FootballController extends Controller
{
    private $baseUrl = 'https://v3.football.api-sports.io/';
    private $headers;

    public function __construct()
    {
        $this->headers = [
            'x-rapidapi-host' => 'v3.football.api-sports.io',
            'x-rapidapi-key' => '2ad6e2bd0535440652d1d5f23bfe65f5',
        ];
    }

    public function liveMatchesPage()
    {
        return view('football.live'); // Blade page
    }

    public function getLiveMatches()
    {
        $response = Http::withHeaders($this->headers)
            ->get($this->baseUrl . 'fixtures', [
                'live' => 'all'
            ]);

        $data = $response->json();
        // dd($data);

        return response()->json($data['response'] ?? []);
    }

    public function fixturesApi()
    {
        // Get today’s date in Indian timezone
        $today = now('Asia/Kolkata')->format('Y-m-d'); // e.g. 2025-10-16

        // Fetch fixtures for today's date only
        $response = Http::withHeaders($this->headers)
            ->get($this->baseUrl . 'fixtures', [
                'date' => $today,
            ]);

        return $response->json()['response'] ?? [];
    }
}
