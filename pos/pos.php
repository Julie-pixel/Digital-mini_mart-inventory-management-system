<?php
include("../includes/auth_check.php");
include("../config/db.php");

if (!in_array($_SESSION['role'], ['Admin', 'Manager', 'Staff'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale (POS) | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e2937 100%);
            color: #e2e8f0;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(0,0,0,0.12);
        }
        .sidebar-header {
            padding: 35px 25px 30px;
            text-align: center;
        }
        .sidebar-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .sidebar-header i { font-size: 32px; color: #60a5fa; }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; color: #f8fafc; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 28px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: white;
            border-left: 4px solid #60a5fa;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            display: flex;
            gap: 20px;
            height: 100vh;
            overflow: hidden;
        }

        .products-panel {
            flex: 2;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .search-bar {
            position: relative;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 16px;
        }
        .search-bar i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            overflow-y: auto;
            flex: 1;
        }

        .product-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .product-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.15);
        }
        .product-name {
            font-weight: 600;
            margin: 10px 0 5px;
            font-size: 15px;
        }
        .product-price {
            color: #16a34a;
            font-weight: 700;
            font-size: 18px;
        }
        .stock-info {
            font-size: 13px;
            color: #64748b;
        }

        /* Cart Panel */
        .cart-panel {
            flex: 1.3;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
        }

        .cart-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 20px;
            font-weight: 700;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .total-section {
            padding: 20px;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .grand-total {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
        }

        .btn {
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-complete {
            background: linear-gradient(90deg, #16a34a, #22c55e);
            color: white;
            width: 100%;
            font-size: 18px;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        #successModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 420px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-store"></i>
            <h2>MINI-MART</h2>
        </div>
        <p>Inventory Management System</p>
    </div>
    <nav>
        <a href="../dashboard/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
         <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../suppliers/suppliers.php" class="nav-link"><i class="fas fa-truck"></i><span>Suppliers</span></a>
        <?php endif; ?>
        <a href="../products/products.php" class="nav-link"><i class="fas fa-boxes"></i><span>Products</span></a>
        <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../stock/stock_management.php" class="nav-link"><i class="fas fa-layer-group"></i><span>Stock Management</span></a>
        <?php endif; ?>
         <?php if($_SESSION['role'] == "Admin"){ ?>
                  <a href="../reports/reports.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <?php } ?>
        <a href="../pos/pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span> Sales</span></a>
       
        <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </nav>
</aside>
<div class="main-content">
    <!-- Products Panel -->
    <div class="products-panel">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search products by name..." onkeyup="searchProducts()">
        </div>
        <div class="product-grid" id="productGrid">
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="cart-panel">
        <div class="cart-header">
            <i class="fas fa-shopping-cart"></i> Current Sale
        </div>
        <div class="cart-items" id="cartItems">
            <div class="empty-cart">
                <i class="fas fa-cart-plus" style="font-size:48px; margin-bottom:15px;"></i>
                <p>Your cart is empty</p>
                <small>Click on products to add them</small>
            </div>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="subtotal">KSh 0.00</span>
            </div>
            <div class="total-row grand-total">
                <span>Total</span>
                <span id="grandTotal">KSh 0.00</span>
            </div>

            <button onclick="completeSale()" class="btn btn-complete">
                <i class="fas fa-check"></i> Complete Sale
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal">
    <div class="modal-content">
        <i class="fas fa-check-circle" style="font-size:60px; color:#16a34a; margin-bottom:15px;"></i>
        <h2 style="margin-bottom:8px; color:#166534;">Sale Completed Successfully!</h2>
        <p id="modalTotal" style="font-size:24px; font-weight:700; color:#16a34a; margin:15px 0;"></p>
        
        <div style="background:#f8fafc; border-radius:12px; padding:15px; margin:20px 0; text-align:left; max-height:280px; overflow-y:auto;" id="receiptContent">
            <!-- Receipt generated by JS -->
        </div>

        <div style="display:flex; gap:12px; margin-top:25px;">
            <button onclick="printReceipt()" style="flex:1; padding:14px; background:#2563eb; color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer;">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button onclick="closeModal()" style="flex:1; padding:14px; background:#64748b; color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer;">
                New Sale
            </button>
        </div>
    </div>
</div>

<script>
// Global cart
let cart = [];


window.onload = function() {
    loadProducts();
};

function loadProducts() {
    fetch('get_products.php')
        .then(response => response.json())
        .then(data => renderProducts(data))
        .catch(err => console.error('Error loading products:', err));
}

function renderProducts(products) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '';

    products.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-name">${product.product_name}</div>
            <div class="product-price">KSh ${parseFloat(product.selling_price).toFixed(2)}</div>
            <div class="stock-info">Stock: ${product.quantity}</div>
        `;
        card.onclick = () => addToCart(product);
        grid.appendChild(card);
    });
}

function searchProducts() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        const name = card.querySelector('.product-name').textContent.toLowerCase();
        card.style.display = name.includes(term) ? 'block' : 'none';
    });
}

function addToCart(product) {
    const existing = cart.find(item => item.product_id === product.product_id);
    
    if (existing) {
        if (existing.quantity < product.quantity) {
            existing.quantity++;
        } else {
            alert("Not enough stock!");
            return;
        }
    } else {
        if (product.quantity <= 0) {
            alert("Product is out of stock!");
            return;
        }
        cart.push({
            product_id: product.product_id,
            product_name: product.product_name,
            selling_price: parseFloat(product.selling_price),
            quantity: 1
        });
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-cart-plus" style="font-size:48px; margin-bottom:15px;"></i>
                <p>Your cart is empty</p>
            </div>`;
        updateTotals();
        return;
    }

    cart.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div>
                <strong>${item.product_name}</strong><br>
                <small>KSh ${item.selling_price.toFixed(2)} × ${item.quantity}</small>
            </div>
            <div style="text-align:right">
                <strong>KSh ${(item.selling_price * item.quantity).toFixed(2)}</strong><br>
                <button onclick="removeFromCart(${index})" style="color:#ef4444; border:none; background:none; cursor:pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    cart.forEach(item => subtotal += item.selling_price * item.quantity);

    document.getElementById('subtotal').textContent = `KSh ${subtotal.toFixed(2)}`;
    document.getElementById('grandTotal').textContent = `KSh ${subtotal.toFixed(2)}`;
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

async function completeSale() {
    if (cart.length === 0) {
        alert("Cart is empty!");
        return;
    }

    if (!confirm("Complete this sale?")) return;

    const response = await fetch('process_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart: cart })
    });

    const result = await response.json();

    if (result.success) {
        showSuccessModal(result.total);
    } else {
        alert("Error: " + result.message);
    }
}

function showSuccessModal(total) {
    const modal = document.getElementById('successModal');
    const totalEl = document.getElementById('modalTotal');
    const receiptEl = document.getElementById('receiptContent');

    totalEl.textContent = `Total: KSh ${parseFloat(total).toFixed(2)}`;

    let receiptHTML = `
        <div style="font-family:monospace; font-size:14px; line-height:1.6;">
            <div style="text-align:center; margin-bottom:15px; border-bottom:2px dashed #ccc; padding-bottom:10px;">
                <strong>DIGITAL MINI-MART</strong><br>
                Nairobi, Kenya<br>
                ${new Date().toLocaleString('en-KE')}
            </div>
    `;

    cart.forEach(item => {
        receiptHTML += `
            ${item.product_name}<br>
            ${item.quantity} × KSh ${item.selling_price.toFixed(2)} = KSh ${(item.selling_price * item.quantity).toFixed(2)}<br><br>
        `;
    });

    receiptHTML += `
            <div style="border-top:2px dashed #ccc; padding-top:10px; margin-top:10px;">
                <strong>Grand Total: KSh ${parseFloat(total).toFixed(2)}</strong><br>
                Payment Method: Mpesa<br>
                Thank you! Come again.
            </div>
        </div>
    `;

    receiptEl.innerHTML = receiptHTML;
    modal.style.display = 'flex';

    // Clear cart and refresh stock
    cart = [];
    renderCart();
    loadProducts();
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}

function printReceipt() {
    const printContents = document.getElementById('receiptContent').innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = `<div style="padding:30px; max-width:350px; margin:40px auto; font-family:monospace;">${printContents}</div>`;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
}
</script>

</body>
</html>