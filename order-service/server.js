const express = require("express");
const axios = require("axios");
const cors = require("cors");
const fs = require("fs");
const path = require("path");

const app = express();
const PORT = 8003;

const USER_SERVICE_URL = "http://127.0.0.1:8001/api";
const MENU_SERVICE_URL = "http://127.0.0.1:8002/api";

app.use(cors());
app.use(express.json());

app.get("/", (req, res) => {
  res.json({
    service: "OrderService",
    message: "OrderService is running",
    endpoints: [
      "GET /api/health",
      "GET /api/orders",
      "POST /api/orders"
    ]
  });
});

const dataFile = path.join(__dirname, "orders.json");

function readOrders() {
  if (!fs.existsSync(dataFile)) {
    fs.writeFileSync(dataFile, JSON.stringify([], null, 2));
  }

  const data = fs.readFileSync(dataFile);
  return JSON.parse(data);
}

function writeOrders(orders) {
  fs.writeFileSync(dataFile, JSON.stringify(orders, null, 2));
}

app.get("/api/health", (req, res) => {
  res.json({
    service: "OrderService",
    status: "running",
    port: PORT,
    technology: "Node.js Express"
  });
});

app.get("/api/orders", (req, res) => {
  const orders = readOrders();

  res.json({
    success: true,
    message: "Data order berhasil diambil",
    data: orders
  });
});

app.get("/api/orders/:id", (req, res) => {
  const orders = readOrders();
  const orderId = parseInt(req.params.id);

  const order = orders.find((item) => item.id === orderId);

  if (!order) {
    return res.status(404).json({
      success: false,
      message: "Order tidak ditemukan"
    });
  }

  res.json({
    success: true,
    message: "Detail order berhasil diambil",
    data: order
  });
});

app.get("/api/orders/user/:userId", (req, res) => {
  const orders = readOrders();
  const userId = parseInt(req.params.userId);

  const userOrders = orders.filter((item) => item.user.id === userId);

  res.json({
    success: true,
    message: "Histori pesanan user berhasil diambil",
    data: userOrders
  });
});

app.get("/api/orders/menu/:menuId", (req, res) => {
  const orders = readOrders();
  const menuId = parseInt(req.params.menuId);

  const menuOrders = orders.filter((item) => item.menu.id === menuId);

  res.json({
    success: true,
    message: "Histori pesanan menu berhasil diambil",
    data: menuOrders
  });
});

app.post("/api/orders", async (req, res) => {
  try {
    const { userId, menuId, quantity, notes } = req.body;

    if (!userId || !menuId || !quantity) {
      return res.status(400).json({
        success: false,
        message: "userId, menuId, dan quantity wajib diisi"
      });
    }

    const userResponse = await axios.get(`${USER_SERVICE_URL}/users/${userId}`);
    const user = userResponse.data.data;

    const menuResponse = await axios.get(`${MENU_SERVICE_URL}/menus/${menuId}`);
    const menu = menuResponse.data.data;

    if (menu.stock < quantity) {
      return res.status(400).json({
        success: false,
        message: "Stok menu tidak mencukupi"
      });
    }

    await axios.patch(`${MENU_SERVICE_URL}/menus/${menuId}/reduce-stock`, {
      quantity: quantity
    });

    const orders = readOrders();

    const newId =
      orders.length > 0 ? Math.max(...orders.map((item) => item.id)) + 1 : 1;

    const newOrder = {
      id: newId,
      user: {
        id: user.id,
        name: user.name,
        nim: user.nim
      },
      menu: {
        id: menu.id,
        name: menu.name,
        price: menu.price
      },
      quantity: Number(quantity),
      totalPrice: menu.price * quantity,
      notes: notes || "-",
      status: "CREATED",
      createdAt: new Date().toISOString()
    };

    orders.push(newOrder);
    writeOrders(orders);

    res.status(201).json({
      success: true,
      message: "Order berhasil dibuat melalui komunikasi antar service",
      communicationFlow: [
        "OrderService menerima request pembuatan order",
        "OrderService mengambil data user dari UserService",
        "OrderService mengambil data menu dari MenuService",
        "OrderService meminta MenuService mengurangi stok",
        "OrderService menyimpan data order"
      ],
      data: newOrder
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: "Gagal membuat order",
      error: error.message
    });
  }
});

app.listen(PORT, () => {
  console.log(`OrderService running on http://127.0.0.1:${PORT}`);
});