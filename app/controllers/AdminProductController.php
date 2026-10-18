<?php
/**
 * Admin Product Controller
 * Quản lý món ăn trong thực đơn: Thêm, sửa, đổi giá, ẩn/hiện, cập nhật tình trạng còn hàng
 */

class AdminProductController {
    private Product $productModel;
    private Category $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    public function index(): void {
        AuthMiddleware::requireRole('admin');

        $search = trim($_GET['search'] ?? '');
        $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        $availability = $_GET['availability'] ?? null;

        $products = $this->productModel->getAllForAdmin($search, $categoryId, $availability);
        $categories = $this->categoryModel->getAll();

        Response::view('admin.products.index', [
            'pageTitle'        => 'Quản lý Món ăn - Quản trị viên',
            'products'         => $products,
            'categories'       => $categories,
            'search'           => $search,
            'currentCategory'  => $categoryId,
            'currentStatus'    => $availability,
            'csrfToken'        => Session::csrfToken()
        ], 'admin');
    }

    public function store(): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $availability = $_POST['availability_status'] ?? 'in_stock';

        $validator = Validator::make($_POST)
            ->required('name', 'Tên món ăn')
            ->minLength('name', 2, 'Tên món ăn')
            ->positiveNumber('price', 'Giá món ăn');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/products');
            return;
        }

        try {
            $image = $this->handleImageUpload('default-food.jpg');

            $this->productModel->create([
                'category_id'         => $categoryId,
                'name'                => $name,
                'description'         => $description,
                'price'               => $price,
                'image'               => $image,
                'availability_status' => $availability,
            ]);
            Session::flash('success', "Đã thêm món '{$name}' vào thực đơn thành công.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi khi thêm món: " . $e->getMessage());
        }

        Response::redirect('admin/products');
    }

    public function update(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $productId = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $availability = $_POST['availability_status'] ?? 'in_stock';
        $currentImage = trim($_POST['current_image'] ?? 'default-food.jpg');

        $validator = Validator::make($_POST)
            ->required('name', 'Tên món ăn')
            ->minLength('name', 2, 'Tên món ăn')
            ->positiveNumber('price', 'Giá món ăn');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/products');
            return;
        }

        try {
            $image = $this->handleImageUpload($currentImage);

            $this->productModel->update($productId, [
                'category_id'         => $categoryId,
                'name'                => $name,
                'description'         => $description,
                'price'               => $price,
                'image'               => $image,
                'availability_status' => $availability,
            ]);
            Session::flash('success', "Đã cập nhật thông tin và ảnh món '{$name}'.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi cập nhật: " . $e->getMessage());
        }

        Response::redirect('admin/products');
    }

    /**
     * Xử lý tải lên hoặc thay thế ảnh món ăn
     */
    private function handleImageUpload(string $currentImage = 'default-food.jpg'): string {
        // 1. Kiểm tra nếu có upload tệp file ảnh mới
        if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Kích thước tệp ảnh vượt quá giới hạn 5MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes, true)) {
                throw new Exception("Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, WEBP, GIF.");
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception("Phần mở rộng tệp không được hỗ trợ.");
            }

            $targetDir = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/public/uploads/products/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $newFilename = 'dish_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $targetPath = $targetDir . $newFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception("Không thể lưu tệp ảnh lên máy chủ. Vui lòng kiểm tra quyền thư mục.");
            }

            // Xóa ảnh cũ nếu là ảnh tự upload trước đó
            if (!empty($currentImage) && $currentImage !== 'default-food.jpg' && !str_starts_with($currentImage, 'http')) {
                $oldFilePath = $targetDir . $currentImage;
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }

            return $newFilename;
        }

        // 2. Kiểm tra nếu người dùng dán URL ảnh trực tuyến
        $imageUrl = trim($_POST['image_url'] ?? '');
        if (!empty($imageUrl)) {
            return $imageUrl;
        }

        // 3. Giữ nguyên ảnh hiện tại
        return !empty($currentImage) ? $currentImage : 'default-food.jpg';
    }

    public function toggleAvailability(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $status = $_POST['status'] ?? 'in_stock';
        try {
            $this->productModel->updateAvailability((int)$id, $status);
            Session::flash('success', "Đã chuyển trạng thái món sang '{$status}'.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/products');
    }

    public function delete(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        try {
            $this->productModel->deleteOrHide((int)$id);
            Session::flash('success', "Đã xử lý xóa/ẩn món ăn khỏi thực đơn.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/products');
    }
}
