<?php

namespace App\Controllers;

class MenuController extends BaseController
{
    private function dataFile()
    {
        return WRITEPATH . 'menus.json';
    }

    private function defaultMenus()
    {
        return [
            [
                "id" => 1,
                "name" => "Nasi Ayam Geprek",
                "category" => "Makanan",
                "price" => 15000,
                "stock" => 20
            ],
            [
                "id" => 2,
                "name" => "Es Teh Manis",
                "category" => "Minuman",
                "price" => 5000,
                "stock" => 30
            ],
            [
                "id" => 3,
                "name" => "Mie Goreng Telur",
                "category" => "Makanan",
                "price" => 12000,
                "stock" => 15
            ]
        ];
    }

    private function readMenus()
    {
        $file = $this->dataFile();

        if (!file_exists($file)) {
            file_put_contents($file, json_encode($this->defaultMenus(), JSON_PRETTY_PRINT));
        }

        return json_decode(file_get_contents($file), true);
    }

    private function writeMenus($menus)
    {
        file_put_contents($this->dataFile(), json_encode($menus, JSON_PRETTY_PRINT));
    }

    public function health()
    {
        return $this->response->setJSON([
            "service" => "MenuService",
            "status" => "running",
            "port" => 8002
        ]);
    }

    public function index()
    {
        return $this->response->setJSON([
            "success" => true,
            "message" => "Data menu berhasil diambil",
            "data" => $this->readMenus()
        ]);
    }

    public function show($id)
    {
        $menus = $this->readMenus();

        foreach ($menus as $menu) {
            if ($menu["id"] == $id) {
                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Detail menu berhasil diambil",
                    "data" => $menu
                ]);
            }
        }

        return $this->response->setStatusCode(404)->setJSON([
            "success" => false,
            "message" => "Menu tidak ditemukan"
        ]);
    }

    public function reduceStock($id)
    {
        $input = $this->request->getJSON(true);
        $quantity = isset($input["quantity"]) ? (int) $input["quantity"] : 0;

        if ($quantity <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                "success" => false,
                "message" => "Quantity harus lebih dari 0"
            ]);
        }

        $menus = $this->readMenus();

        foreach ($menus as &$menu) {
            if ($menu["id"] == $id) {
                if ($menu["stock"] < $quantity) {
                    return $this->response->setStatusCode(400)->setJSON([
                        "success" => false,
                        "message" => "Stok menu tidak mencukupi"
                    ]);
                }

                $menu["stock"] -= $quantity;
                $this->writeMenus($menus);

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Stok menu berhasil dikurangi",
                    "data" => $menu
                ]);
            }
        }

        return $this->response->setStatusCode(404)->setJSON([
            "success" => false,
            "message" => "Menu tidak ditemukan"
        ]);
    }

    public function orderHistory($id)
    {
        try {
            $client = \Config\Services::curlrequest();

            $response = $client->get("http://127.0.0.1:8003/api/orders/menu/" . $id, [
                "http_errors" => false
            ]);

            $body = json_decode($response->getBody(), true);

            return $this->response->setJSON([
                "success" => true,
                "message" => "Histori pesanan menu berhasil diambil dari OrderService",
                "source" => "OrderService",
                "data" => $body["data"] ?? []
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                "success" => false,
                "message" => "OrderService belum berjalan atau tidak dapat diakses",
                "error" => $e->getMessage()
            ]);
        }
    }
}