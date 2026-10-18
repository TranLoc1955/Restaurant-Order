<?php
/**
 * Menu Controller
 * Xử lý danh sách thực đơn món ăn, tìm kiếm, lọc danh mục, sắp xếp và chi tiết món
 */

class MenuController {
    private Product $productModel;
    private Category $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Danh sách thực đơn
     */
    public function index(): void {
        $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        $search = trim($_GET['search'] ?? '');
        $sort = $_GET['sort'] ?? 'default';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;

        $filters = [];
        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $categories = $this->categoryModel->getActive();
        $products = $this->productModel->getAvailable($filters, $sort, $page, $perPage);
        $totalProducts = $this->productModel->countAvailable($filters);
        $totalPages = (int)ceil($totalProducts / $perPage);

        // Lấy tên danh mục hiện tại nếu có lọc
        $selectedCategory = null;
        if ($categoryId) {
            $selectedCategory = $this->categoryModel->findById($categoryId);
        }

        Response::view('customer.menu', [
            'pageTitle'        => 'Thực đơn - Nhà Hàng Trần Lộc',
            'categories'       => $categories,
            'products'         => $products,
            'totalProducts'    => $totalProducts,
            'totalPages'       => $totalPages,
            'currentPage'      => $page,
            'currentCategory'  => $categoryId,
            'selectedCategory' => $selectedCategory,
            'search'           => $search,
            'sort'             => $sort,
        ], 'main');
    }

    /**
     * Chi tiết món ăn
     */
    public function detail(string|int $id): void {
        $product = $this->productModel->findById((int)$id);

        if (!$product || $product['availability_status'] === 'hidden') {
            Response::abort(404, 'Món ăn không tồn tại hoặc đã ngừng phục vụ.');
        }

        // Lấy các món cùng danh mục
        $relatedProducts = $this->productModel->getAvailable([
            'category_id' => $product['category_id']
        ], 'default', 1, 4);

        // Lọc bỏ chính món hiện tại
        $relatedProducts = array_filter($relatedProducts, fn($p) => (int)$p['id'] !== (int)$product['id']);

        Response::view('customer.product-detail', [
            'pageTitle'       => $product['name'] . ' - Nhà Hàng Trần Lộc',
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
        ], 'main');
    }
}
