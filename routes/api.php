<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

$users = [
    [
        "id" => 1,
        "name" => "Tiffany",
        "nim" => "1206240001",
        "major" => "Sistem Informasi",
        "phone" => "081234567890"
    ],
    [
        "id" => 2,
        "name" => "Budi",
        "nim" => "1206240002",
        "major" => "Sistem Informasi",
        "phone" => "081298765432"
    ],
    [
        "id" => 3,
        "name" => "Alya",
        "nim" => "1206240003",
        "major" => "Sistem Informasi",
        "phone" => "081377788899"
    ]
];

Route::get('/health', function () {
    return response()->json([
        "service" => "UserService",
        "status" => "running",
        "port" => 8001
    ]);
});

Route::get('/users', function () use ($users) {
    return response()->json([
        "success" => true,
        "message" => "Data user berhasil diambil",
        "data" => $users
    ]);
});

Route::get('/users/{id}', function ($id) use ($users) {
    foreach ($users as $user) {
        if ($user["id"] == $id) {
            return response()->json([
                "success" => true,
                "message" => "Detail user berhasil diambil",
                "data" => $user
            ]);
        }
    }

    return response()->json([
        "success" => false,
        "message" => "User tidak ditemukan"
    ], 404);
});

Route::get('/users/{id}/orders', function ($id) {
    try {
        $response = Http::timeout(5)->get("http://127.0.0.1:8003/api/orders/user/$id");

        if ($response->failed()) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil histori pesanan dari OrderService",
                "error" => $response->json()
            ], $response->status());
        }

        return response()->json([
            "success" => true,
            "message" => "Histori pesanan user berhasil diambil dari OrderService",
            "source" => "OrderService",
            "data" => $response->json()["data"]
        ]);
    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "OrderService belum berjalan atau tidak dapat diakses",
            "error" => $e->getMessage()
        ], 500);
    }
});
