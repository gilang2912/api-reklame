<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ObjekImagesResource;
use App\Http\Resources\v1\ObjekPajakResource;
use App\Models\v1\Geolocation;
use App\Models\v1\ObjekImage;
use App\Models\v1\ObjekPajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ObjekPajakController extends Controller
{
    public function index()
    {
        $q = request()->get('q');

        $op = ObjekPajak::with('geolocation', 'objekimage')->latest()->paginate(5);

        if ($q) {
            $op = ObjekPajak::with('geolocation', 'objekimage')
                ->where('nama_wp', 'LIKE', '%' . strtoupper($q) . '%')
                ->orWhere('npwpd', 'LIKE', '%' . $q . '%')
                ->orWhere('kecamatan', 'LIKE', '%' . $q . '%')
                ->orWhere('jns_reklame', 'LIKE', '%' . $q . '%')
                ->latest()
                ->paginate(5);
        }

        return ObjekPajakResource::collection($op);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'npwpd' => 'required|string',
            'kd_op' => 'required|string|unique:objek_pajak,kd_objek_pajak',
            'nm_wp' => 'required|string',
            'objek_pajak' => 'required|string',
            'lokasi_objek' => 'required|string',
            'jns_reklame' => 'required|string',
            'kecamatan' => 'required|string',
            'long' => 'required',
            'lat' => 'required',
        ]);

        try {
            $images = $request->images;

            foreach ($images as $image) {
                $relativePath = $this->saveImage($image);
                ObjekImage::create([
                    'kd_objek_pajak' => $request->kd_op,
                    'path_name' => $relativePath,
                ]);
            }

            Geolocation::create([
                'kd_objek_pajak' => $request->kd_op,
                'longitude' => $request->long,
                'latitude' => $request->lat,
            ]);

            ObjekPajak::create([
                'npwpd' => trim($request->npwpd),
                'kd_objek_pajak' => trim($request->kd_op),
                'nama_wp' => $request->nm_wp,
                'objek_pajak' => $request->objek_pajak,
                'lokasi_objek' => $request->lokasi_objek,
                'jns_reklame' => $request->jns_reklame,
                'kecamatan' => $request->kecamatan,
                'panjang' => $request->panjang,
                'lebar' => $request->lebar,
                'tinggi' => $request->tinggi,
                'keterangan' => $request->ket
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data objek pajak berhasil ditambahkan.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messeage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request)
    {
        $op = ObjekPajak::with('geolocation', 'objekimage')
            ->where('kd_objek_pajak', '=', $request->kd_op)
            ->first();

        if (!$op) {
            return response()->json([
                'status' => false,
                'message' => 'Data objek pajak tidak ditemukan.'
            ], 404);
        }

        return new ObjekPajakResource($op);
    }

    public function update(Request $request)
    {
        $kd_op = $request->kd_op;
        $this->validate($request, [
            'objek_pajak' => 'required|string',
            'lokasi_objek' => 'required|string',
            'long' => 'required',
            'lat' => 'required'
        ]);

        $op = ObjekPajak::with('geolocation', 'objekimage')
            ->where('kd_objek_pajak', '=', $kd_op)
            ->first();

        if (!$op) {
            return response()->json([
                'status' => false,
                'message' => 'Data objek pajak tidak ditemukan.'
            ], 404);
        }

        if (isset($request->images) && !empty($request->images)) {
            foreach ($op->objekimage as $img) {
                $absolutePath = public_path($img->path_name);
                File::delete($absolutePath);
                $path[] = ObjekImage::find($img->id);
            }

            foreach ($request->images as $key => $value) {
                $relativePath = $this->saveImage($value);
                $path[$key]->update(['path_name' => $relativePath]);
            }
        }

        $op->objek_pajak = $request->objek_pajak;
        $op->lokasi_objek = $request->lokasi_objek;
        $op->geolocation->longitude = $request->long;
        $op->geolocation->latitude = $request->lat;
        $op->keterangan = $request->ket;
        $op->save();

        return response()->json([
            'status' => true,
            'message' => 'Data objek pajak berhasil diupdate.',
        ]);
    }

    public function destroy(Request $request)
    {
        $op = ObjekPajak::with('objekimage')
            ->where('kd_objek_pajak', '=', $request->kd_op)
            ->first();

        $geo = $op->geolocation();
        $images = $op->objekimage();

        if (!$op) {
            return response()->json([
                'status' => false,
                'message' => 'Data objek pajak tidak ditemukan.'
            ], 404);
        }

        foreach ($op->objekimage as $img) {
            $absolutePath = public_path($img->path_name);
            File::delete($absolutePath);
        }

        $geo->delete();
        $images->delete();
        $op->delete();

        return response()->json([], 204);
    }

    public function showWithPayment()
    {
        $kdop = request()->get('kd_op');

        $op = ObjekPajak::with('geolocation', 'objekimage')
            ->where('kd_objek_pajak', '=', $kdop)
            ->first();

        if (!$op) {
            return response()->json([
                'status' => false,
                'message' => 'Data objek pajak tidak ditemukan.'
            ], 404);
        }

        $data = [
            'kd_op' => $op->kd_objek_pajak,
            'npwpd' => $op->npwpd,
            'nm_wp' => $op->nama_wp,
            'objek_pajak' => $op->objek_pajak,
            'lokasi_objek' => $op->lokasi_objek,
            'jns_reklame' => $op->jns_reklame,
            'panjang' => $op->panjang,
            'lebar' => $op->lebar,
            'tinggi' => $op->tinggi,
            'coordinate' => [
                'long' => $op->geolocation->longitude,
                'lat' => $op->geolocation->latitude,
            ],
            'payment' => $this->checkPayment($op->kd_objek_pajak),
            'images' => ObjekImagesResource::collection($op->objekimage),
        ];

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
                    'masa_pajak' => $res['masa_pajak'],
                    'p_awal' => $res['periode_awal'],
                    'p_akhir' => $res['periode_akhir']
                ];
            }
        }
        return $data;
    }
}
