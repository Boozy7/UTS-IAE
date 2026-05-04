# KantinConnect - Service-to-Service Communication

KantinConnect adalah project sistem pemesanan kantin kampus yang dibuat untuk menerapkan konsep **Service-to-Service Communication**. Sistem ini terdiri dari beberapa service yang berjalan secara terpisah, tetapi tetap bisa saling berkomunikasi secara langsung menggunakan HTTP request dan format data JSON.

Project ini tidak terlalu berfokus pada tampilan UI, karena inti dari sistem ini adalah bagaimana setiap service bisa berperan sebagai provider dan consumer.

## Gambaran Sistem

Pada sistem KantinConnect, proses utamanya adalah pembuatan pesanan makanan atau minuman di kantin kampus. Ketika client membuat pesanan melalui OrderService, maka OrderService akan mengambil data user dari UserService dan mengambil data menu dari MenuService.

Jika data user dan menu valid, OrderService akan meminta MenuService untuk mengurangi stok menu. Setelah itu, data pesanan akan disimpan oleh OrderService.

Selain itu, UserService dan MenuService juga bisa mengambil data histori pesanan dari OrderService. Jadi setiap service tidak hanya menyediakan data, tetapi juga bisa menggunakan data dari service lain.

## Daftar Service

| Service | Teknologi | Port | Fungsi |
|---|---|---:|---|
| UserService | Laravel | 8001 | Mengelola data user/mahasiswa |
| MenuService | CodeIgniter 4 | 8002 | Mengelola data menu dan stok kantin |
| OrderService | Node.js Express | 8003 | Mengelola transaksi pesanan |

## Konsep Provider dan Consumer

| Service | Sebagai Provider | Sebagai Consumer |
|---|---|---|
| UserService | Menyediakan data user | Mengambil histori pesanan user dari OrderService |
| MenuService | Menyediakan data menu dan mengurangi stok | Mengambil histori pesanan menu dari OrderService |
| OrderService | Menyediakan data order | Mengambil data user dari UserService dan data menu dari MenuService |

## Alur Komunikasi Utama

1. Client mengirim request order ke OrderService.
2. OrderService meminta data user ke UserService.
3. OrderService meminta data menu ke MenuService.
4. OrderService mengecek apakah stok menu masih cukup.
5. Jika stok cukup, OrderService meminta MenuService untuk mengurangi stok.
6. OrderService menyimpan data pesanan.
7. UserService dapat mengambil histori order dari OrderService.
8. MenuService dapat mengambil histori order dari OrderService.

## Teknologi yang Digunakan

- Laravel
- CodeIgniter 4
- Node.js
- Express.js
- PHP
- Composer
- NPM
- REST API
- JSON
- Postman

## Struktur Project

```text
kantinconnect/
├── user-service/
├── menu-service/
├── order-service/
├── note.txt
└── README.md
```

## Cara Menjalankan Project

Pastikan PHP, Composer, Node.js, dan NPM sudah terinstall di perangkat.

### 1. Menjalankan UserService

Masuk ke folder UserService:

```bash
cd user-service
```

Jalankan service dengan port 8001:

```bash
php artisan serve --port=8001
```

UserService akan berjalan di:

```text
http://127.0.0.1:8001
```

### 2. Menjalankan MenuService

Buka terminal baru, lalu masuk ke folder MenuService:

```bash
cd menu-service
```

Jalankan service dengan port 8002:

```bash
php spark serve --host 127.0.0.1 --port 8002
```

MenuService akan berjalan di:

```text
http://127.0.0.1:8002
```

### 3. Menjalankan OrderService

Buka terminal baru, lalu masuk ke folder OrderService:

```bash
cd order-service
```

Install dependency terlebih dahulu jika belum:

```bash
npm install
```

Jalankan service dengan port 8003:

```bash
npm run dev
```

OrderService akan berjalan di:

```text
http://127.0.0.1:8003
```

## Endpoint UserService

### Health Check

```http
GET http://127.0.0.1:8001/api/health
```

Endpoint ini digunakan untuk mengecek apakah UserService sedang berjalan.

### Get All Users

```http
GET http://127.0.0.1:8001/api/users
```

Endpoint ini digunakan untuk mengambil semua data user.

### Get User Detail

```http
GET http://127.0.0.1:8001/api/users/1
```

Endpoint ini digunakan untuk mengambil detail user berdasarkan ID.

### Get User Order History

```http
GET http://127.0.0.1:8001/api/users/1/orders
```

Endpoint ini digunakan untuk mengambil histori pesanan user dari OrderService. Bagian ini menunjukkan bahwa UserService juga berperan sebagai consumer.

## Endpoint MenuService

### Health Check

```http
GET http://127.0.0.1:8002/api/health
```

Endpoint ini digunakan untuk mengecek apakah MenuService sedang berjalan.

### Get All Menus

```http
GET http://127.0.0.1:8002/api/menus
```

Endpoint ini digunakan untuk mengambil semua data menu.

### Get Menu Detail

```http
GET http://127.0.0.1:8002/api/menus/1
```

Endpoint ini digunakan untuk mengambil detail menu berdasarkan ID.

### Reduce Menu Stock

```http
PATCH http://127.0.0.1:8002/api/menus/1/reduce-stock
```

Endpoint ini digunakan untuk mengurangi stok menu.

Contoh body:

```json
{
  "quantity": 1
}
```

### Get Menu Order History

```http
GET http://127.0.0.1:8002/api/menus/1/order-history
```

Endpoint ini digunakan untuk mengambil histori pesanan berdasarkan menu dari OrderService. Bagian ini menunjukkan bahwa MenuService juga berperan sebagai consumer.

## Endpoint OrderService

### Health Check

```http
GET http://127.0.0.1:8003/api/health
```

Endpoint ini digunakan untuk mengecek apakah OrderService sedang berjalan.

### Get All Orders

```http
GET http://127.0.0.1:8003/api/orders
```

Endpoint ini digunakan untuk mengambil semua data order.

### Get Order Detail

```http
GET http://127.0.0.1:8003/api/orders/1
```

Endpoint ini digunakan untuk mengambil detail order berdasarkan ID.

### Get Orders by User

```http
GET http://127.0.0.1:8003/api/orders/user/1
```

Endpoint ini digunakan untuk mengambil data order berdasarkan user tertentu.

### Get Orders by Menu

```http
GET http://127.0.0.1:8003/api/orders/menu/1
```

Endpoint ini digunakan untuk mengambil data order berdasarkan menu tertentu.

### Create Order

```http
POST http://127.0.0.1:8003/api/orders
```

Endpoint ini digunakan untuk membuat pesanan baru. Pada proses ini, OrderService akan mengambil data dari UserService dan MenuService.

Contoh body:

```json
{
  "userId": 1,
  "menuId": 1,
  "quantity": 2,
  "notes": "Pedas sedang"
}
```

Contoh response:

```json
{
  "success": true,
  "message": "Order berhasil dibuat melalui komunikasi antar service",
  "communicationFlow": [
    "OrderService menerima request pembuatan order",
    "OrderService mengambil data user dari UserService",
    "OrderService mengambil data menu dari MenuService",
    "OrderService meminta MenuService mengurangi stok",
    "OrderService menyimpan data order"
  ],
  "data": {
    "id": 1,
    "user": {
      "id": 1,
      "name": "Tiffany",
      "nim": "1206240001"
    },
    "menu": {
      "id": 1,
      "name": "Nasi Ayam Geprek",
      "price": 15000
    },
    "quantity": 2,
    "totalPrice": 30000,
    "notes": "Pedas sedang",
    "status": "CREATED"
  }
}
```

## Data yang Digunakan

Project ini menggunakan data statis dan file JSON sederhana. Jadi project bisa dijalankan tanpa konfigurasi database.

- UserService menggunakan data user statis.
- MenuService menggunakan data menu dan stok.
- OrderService menyimpan data pesanan ke file `orders.json`.

## Dokumentasi API

Dokumentasi API dibuat menggunakan Postman Documentation.

Link Postman Documentation:

```text
[isi link Postman Documentation]
```

## Video Demo

Video demo berisi penjelasan project, cara menjalankan setiap service, pengujian endpoint, dan bukti komunikasi antar service.

Link Video Demo:

```text
[isi link video demo]
```

## Kesimpulan

KantinConnect sudah menerapkan service-to-service communication dengan tiga service yang berjalan secara mandiri. Setiap service memiliki peran sebagai provider dan consumer. Komunikasi antar service dilakukan secara langsung menggunakan REST API, HTTP request, dan format data JSON.