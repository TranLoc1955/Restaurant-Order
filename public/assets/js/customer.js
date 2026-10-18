/**
 * Customer JavaScript
 * Xử lý thêm món vào giỏ qua AJAX, cập nhật số lượng, xóa món và xác nhận hủy đơn
 */

// Hàm cập nhật số lượng huy hiệu giỏ hàng ở Header
function refreshCartBadge() {
    fetch(window.APP_BASE_URL + '/api/cart/count')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('cart-count-badge');
            if (badge) {
                badge.textContent = data.count || 0;
                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            }
        })
        .catch(err => console.error('Error fetching cart count:', err));
}

// Thêm món vào giỏ hàng bằng AJAX
function addToCart(productId, quantity = 1, productName = '') {
    const postData = {
        product_id: parseInt(productId),
        quantity: parseInt(quantity)
    };

    fetch(window.APP_BASE_URL + '/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('success', data.message || `Đã thêm món vào giỏ hàng!`);
            const badge = document.getElementById('cart-count-badge');
            if (badge) {
                badge.textContent = data.total_count;
                badge.style.display = 'inline-block';
            }
        } else {
            Toast.show('error', data.message || 'Không thể thêm món vào giỏ hàng.');
        }
    })
    .catch(err => {
        console.error(err);
        Toast.show('error', 'Lỗi kết nối máy chủ khi thêm giỏ hàng.');
    });
}

// Cập nhật số lượng món trên trang Cart
function updateCartItem(productId, quantity) {
    fetch(window.APP_BASE_URL + '/api/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: parseInt(productId),
            quantity: parseInt(quantity)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Tải lại trang để đồng bộ toàn bộ bảng và phí ship
            window.location.reload();
        } else {
            Toast.show('error', data.message || 'Lỗi cập nhật số lượng.');
        }
    })
    .catch(err => console.error(err));
}

// Xóa món khỏi giỏ trên trang Cart
function removeCartItem(productId) {
    if (!confirm('Bạn có chắc chắn muốn xóa món này khỏi giỏ hàng?')) {
        return;
    }

    fetch(window.APP_BASE_URL + '/api/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: parseInt(productId)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            Toast.show('error', data.message || 'Lỗi khi xóa món.');
        }
    })
    .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', () => {
    // Gắn sự kiện cho các nút thêm vào giỏ nhanh trên Food Card
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name') || '';
            const qtyInput = document.getElementById('product-qty');
            const qty = qtyInput ? parseInt(qtyInput.value) : 1;
            addToCart(id, qty, name);
        });
    });

    // Đồng bộ số lượng badge ban đầu
    refreshCartBadge();
});
