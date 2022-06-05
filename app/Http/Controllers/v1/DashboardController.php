<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ObjekImagesResource;
use App\Models\v1\ObjekPajak;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $param = request()->get('q');

        $op = ObjekPajak::with('geolocation', 'objekimage')
            ->latest()
            ->get();

        if ($param) {
            $op = ObjekPajak::with('geolocation', 'objekimage')
                ->where('nama_wp', 'LIKE', '%' . strtoupper($param) . '%')
                ->orWhere('npwpd', 'LIKE', '%' . $param . '%')
                ->orWhere('kecamatan', 'LIKE', '%' . $param . '%')
                ->orWhere('jns_reklame', 'LIKE', '%' . $param . '%')
                ->get();
        }

        $data = [];

        foreach ($op as $r) {
            $payment = $this->checkPayment($r->kd_objek_pajak);

            $data[] = [
                'kd_op' => $r->kd_objek_pajak,
                'npwpd' => $r->npwpd,
                'nm_wp' => $r->nama_wp,
                'objek_pajak' => $r->objek_pajak,
                'lokasi_objek' => $r->lokasi_objek,
                'jns_reklame' => $r->jns_reklame,
                'kecamatan' => $r->kecamatan,
                'payment' => $payment,
                'coordinate' => [
                    'long' => $r->geolocation->longitude,
                    'lat' => $r->geolocation->latitude,
                ],
                'images' => ObjekImagesResource::collection($r->objekimage)
            ];
        }

        return response()->json($data);
    }

    private function checkPayment($kdop)
    {
        $response = Http::withBasicAuth('user_gis', 'user-gis</>')
            ->get(env('SIMPAKDU_API_URL') . 'payment', [
                'kd_objek_pajak' => $kdop
            ])
            ->json();


        $data = [];
        sort($response['data']);
        foreach ($response['data'] as $index => $res) {
            if ($index === array_key_last($response['data'])) {
                $obj1 = date_create(date('d-m-Y'));
                $obj2 = date_create(str_replace('/', '-', $res['periode_akhir']));
                $interval = date_diff($obj1, $obj2);
                $periodeAwal = date_create(str_replace('/', '-', $res['periode_awal']));
                $now = date_create(date('d-m-Y'));
                $interval2 = date_diff($periodeAwal, $now);
                if (intval($interval->format('%R%a')) <= 0) {
                    $status = 3; // Jatuh tempo
                } else if (intval($interval->format('%R%a')) <= 7) {
                    $status = 2; // mendekati jatuh tempo
                } else if ($res['status_bayar'] === false) {
                    if (intval($interval2->format('%R%a')) >= 14 && $res['status_bayar'] === false) {
                        $status = 3;
                    }
                    $status = 4; // bukti bayar belum disetor
                } else {
                    $status = 1;
                }

                $data = [
                    'status' => $status,
                ];
            }
        }
        return $data;
    }
}
