<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\Request;

class MikroTikController extends Controller
{
    protected $mikroTikService;

    public function __construct(MikroTikService $mikroTikService)
    {
        $this->mikroTikService = $mikroTikService;
    }

    public function testConnection()
    {
     
        if ($this->mikroTikService->testConnection()) {
            return response()->json(['message' => 'Connected to MikroTik router successfully.']);
        } else {
            return response()->json(['message' => 'Failed to connect to MikroTik router.'], 500);
        }
    }

    public function getAddresses()
    {
        $addresses = $this->mikroTikService->getIPAddresses();

        if ($addresses) {
            return response()->json($addresses);
        } else {
            return response()->json(['message' => 'Failed to retrieve IP addresses.'], 500);
        }
    }
}
