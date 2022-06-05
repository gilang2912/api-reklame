<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __invoke(Request $request)
    {
        $response = Http::withBasicAuth('user_gis', 'user-gis</>')
            ->get(env('SIMPAKDU_API_URL') . 'payment', [
                'kd_objek_pajak' => $request->kd_op
            ])
            ->json();

        $response2 = Http::withBasicAuth('user_gis', 'user-gis</>')
            ->get(env('SIMPAKDU_API_URL') . 'objek-pajak', [
                'kd_objek_pajak' => $request->kd_op
            ])
            ->json();

        if (in_array('404', $response)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $data = [];
        sort($response['data']);
        foreach ($response['data'] as $index => $r) {
            if ($index === array_key_last($response['data'])) {
                $data = (object) [
                    'kd_objek_pajak' => $r['kd_objek_pajak'],
                    'npwpd' => $r['npwpd'],
                    'nm_wp' => $r['nm_wp'],
                    'objek_pajak' => $r['objek_pajak'],
                    'alamat' => $r['alamat'],
                    'jns_reklame' => $response2['jns_reklame'],
                    'kecamatan' => $response2['kecamatan'],
                    'panjang' => $r['panjang'],
                    'lebar' => $r['lebar'],
                    'tinggi' => $r['tinggi']
                ];
            }
        }

        return new PaymentResource($data);
    }
}
