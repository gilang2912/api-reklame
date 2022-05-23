<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ObjekPajakResource;
use App\Models\v1\Geolocation;
use App\Models\v1\ObjekImage;
use App\Models\v1\ObjekPajak;
use Illuminate\Http\Request;

class ObjekPajakController extends Controller
{
    public function index()
    {
        $op = ObjekPajak::with('geolocation')->latest()->get();

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
                'npwpd' => $request->npwpd,
                'kd_objek_pajak' => $request->kd_op,
                'nama_wp' => $request->nm_wp,
                'objek_pajak' => $request->objek_pajak,
                'lokasi_objek' => $request->lokasi_objek,
                'jns_reklame' => $request->jns_reklame,
                'kecamatan' => $request->kecamatan,
                'panjang' => $request->panjang,
                'lebar' => $request->lebar,
                'tinggi' => $request->tinggi,
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
}
