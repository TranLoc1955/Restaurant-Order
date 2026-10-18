<?php
/**
 * RestaurantSetting Model
 * Quản lý thông tin cấu hình nhà hàng
 */

class RestaurantSetting {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy thông tin cấu hình nhà hàng
     */
    public function getSettings(): array {
        $stmt = $this->db->query("SELECT * FROM restaurant_settings WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch();
        if (!$row) {
            return [
                'restaurant_name'      => 'Nhà Hàng Trần Lộc',
                'address'              => '150 Nguyễn Như Hạnh, Hòa Minh, Liên Chiểu, Đà Nẵng',
                'phone'                => '0778591167',
                'opening_hours'        => '08:00 - 23:00 (Hằng ngày)',
                'delivery_fee'         => 20000.00,
                'minimum_order_amount' => 50000.00,
            ];
        }
        return $row;
    }

    /**
     * Cập nhật thông tin cấu hình nhà hàng
     */
    public function updateSettings(array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE restaurant_settings 
            SET restaurant_name = ?, address = ?, phone = ?, opening_hours = ?, 
                delivery_fee = ?, minimum_order_amount = ?
            WHERE id = 1
        ");
        return $stmt->execute([
            trim($data['restaurant_name']),
            trim($data['address']),
            trim($data['phone']),
            trim($data['opening_hours']),
            (float)$data['delivery_fee'],
            (float)$data['minimum_order_amount'],
        ]);
    }
}
