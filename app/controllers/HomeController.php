<?php
/**
 * Home Controller
 * Hiển thị Trang chủ khách hàng: Hero banner, món nổi bật, danh mục, bestsellers
 */

class HomeController {
    private Product $productModel;
    private Category $categoryModel;
    private RestaurantSetting $settingModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->settingModel = new RestaurantSetting();
    }

    public function index(): void {
        $categories = $this->categoryModel->getActive();
        $featuredProducts = $this->productModel->getFeatured(6);
        $bestSellers = $this->productModel->getBestSellers(6);
        $settings = $this->settingModel->getSettings();

        Response::view('customer.home', [
            'pageTitle'        => $settings['restaurant_name'] . ' - Đặt đồ ăn trực tuyến & Trợ lý AI',
            'categories'       => $categories,
            'featuredProducts' => $featuredProducts,
            'bestSellers'      => $bestSellers,
            'settings'         => $settings,
        ], 'main');
    }
}
