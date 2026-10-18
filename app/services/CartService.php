<?php
/**
 * Cart Service
 * Quản lý giỏ hàng trong Session và xác minh tính toàn vẹn giá từ Database
 */

class CartService {
    private Product $productModel;

    public function __construct() {
        Session::start();
        $this->productModel = new Product();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Thêm món vào giỏ hàng
     */
    public function add(int $productId, int $quantity = 1): array {
        if ($quantity <= 0) {
            throw new Exception("Số lượng đặt món phải lớn hơn 0.");
        }

        $product = $this->productModel->findById($productId);
        if (!$product) {
            throw new Exception("Món ăn không tồn tại.");
        }

        if ($product['availability_status'] !== 'in_stock') {
            throw new Exception("Món '{$product['name']}' hiện đang tạm hết hàng hoặc ngừng kinh doanh.");
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        // Giới hạn số lượng tối đa 99 món cho 1 loại trong giỏ để kiểm thử biên
        if ($_SESSION['cart'][$productId] > 99) {
            $_SESSION['cart'][$productId] = 99;
        }

        return [
            'success'     => true,
            'message'     => "Đã thêm '{$product['name']}' vào giỏ hàng.",
            'total_count' => $this->getTotalCount(),
        ];
    }

    /**
     * Cập nhật số lượng món trong giỏ
     */
    public function update(int $productId, int $quantity): array {
        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        if ($quantity > 99) {
            $quantity = 99;
        }

        $product = $this->productModel->findById($productId);
        if (!$product || $product['availability_status'] !== 'in_stock') {
            unset($_SESSION['cart'][$productId]);
            throw new Exception("Món ăn không còn khả dụng và đã được xóa khỏi giỏ hàng.");
        }

        $_SESSION['cart'][$productId] = $quantity;

        return [
            'success'     => true,
            'message'     => "Đã cập nhật số lượng món.",
            'total_count' => $this->getTotalCount(),
            'subtotal'    => $this->getSubtotal(),
        ];
    }

    /**
     * Xóa món khỏi giỏ hàng
     */
    public function remove(int $productId): array {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        return [
            'success'     => true,
            'message'     => "Đã xóa món khỏi giỏ hàng.",
            'total_count' => $this->getTotalCount(),
            'subtotal'    => $this->getSubtotal(),
        ];
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(): void {
        $_SESSION['cart'] = [];
    }

    /**
     * Lấy toàn bộ món trong giỏ kèm dữ liệu giá và ảnh mới nhất từ database
     */
    public function getItems(): array {
        $items = [];
        $rawCart = $_SESSION['cart'] ?? [];

        foreach ($rawCart as $productId => $quantity) {
            $product = $this->productModel->findById((int)$productId);
            if (!$product) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $unitPrice = (float)$product['price'];
            $lineTotal = $unitPrice * (int)$quantity;

            $items[] = [
                'product_id'          => (int)$product['id'],
                'name'                => $product['name'],
                'image'               => $product['image'],
                'price'               => $unitPrice,
                'quantity'            => (int)$quantity,
                'line_total'          => $lineTotal,
                'availability_status' => $product['availability_status'],
                'is_available'        => ($product['availability_status'] === 'in_stock'),
            ];
        }

        return $items;
    }

    /**
     * Tính tổng tiền tạm tính (Subtotal)
     */
    public function getSubtotal(): float {
        $subtotal = 0.00;
        $items = $this->getItems();
        foreach ($items as $item) {
            if ($item['is_available']) {
                $subtotal += $item['line_total'];
            }
        }
        return $subtotal;
    }

    /**
     * Tổng số lượng các món trong giỏ (dành cho huy hiệu badge ở Header)
     */
    public function getTotalCount(): int {
        $count = 0;
        foreach ($_SESSION['cart'] ?? [] as $qty) {
            $count += (int)$qty;
        }
        return $count;
    }
}
