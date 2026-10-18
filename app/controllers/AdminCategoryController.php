<?php
/**
 * Admin Category Controller
 * Quản lý danh mục món ăn: Thêm, sửa, ẩn/hiện danh mục
 */

class AdminCategoryController {
    private Category $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    public function index(): void {
        AuthMiddleware::requireRole('admin');
        $categories = $this->categoryModel->getAll();

        Response::view('admin.categories.index', [
            'pageTitle'  => 'Quản lý Danh mục - Quản trị viên',
            'categories' => $categories,
            'csrfToken'  => Session::csrfToken()
        ], 'admin');
    }

    public function store(): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        $validator = Validator::make($_POST)
            ->required('name', 'Tên danh mục')
            ->minLength('name', 2, 'Tên danh mục');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/categories');
            return;
        }

        try {
            $this->categoryModel->create([
                'name'        => $name,
                'description' => $description,
                'status'      => $status,
            ]);
            Session::flash('success', "Đã thêm danh mục '{$name}' thành công.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi khi thêm danh mục: " . $e->getMessage());
        }

        Response::redirect('admin/categories');
    }

    public function update(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        $validator = Validator::make($_POST)
            ->required('name', 'Tên danh mục')
            ->minLength('name', 2, 'Tên danh mục');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/categories');
            return;
        }

        try {
            $this->categoryModel->update((int)$id, [
                'name'        => $name,
                'description' => $description,
                'status'      => $status,
            ]);
            Session::flash('success', "Đã cập nhật danh mục '{$name}'.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi cập nhật: " . $e->getMessage());
        }

        Response::redirect('admin/categories');
    }

    public function toggleStatus(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        try {
            $this->categoryModel->toggleStatus((int)$id);
            Session::flash('success', "Đã thay đổi trạng thái danh mục.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/categories');
    }
}
