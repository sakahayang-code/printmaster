<?php
namespace App\Modules\Products;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use Exception;

class ProductController extends Controller {
    public function index(): void {
        $this->requirePermission('catalog.view');

        $categoryId = Request::get('category_id');
        $search = trim(Request::get('search', ''));

        $where = ["p.is_active = 1"];
        $params = [];

        if (!empty($categoryId)) {
            $where[] = "p.category_id = :cat";
            $params['cat'] = $categoryId;
        }

        if (!empty($search)) {
            $where[] = "(p.name LIKE :s1 OR p.code LIKE :s2)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $sql = "
            SELECT p.*, pc.name as category_name 
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY pc.sort_order ASC, p.name ASC
        ";
        $products = Database::fetchAll($sql, $params);
        $categories = Database::fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order ASC");
        $finishings = Database::fetchAll("SELECT * FROM product_finishing WHERE is_active = 1 ORDER BY name ASC");

        $this->render('products/index', [
            'title'      => 'Katalog Produk & Layanan Percetakan',
            'products'   => $products,
            'categories' => $categories,
            'finishings' => $finishings,
            'categoryId' => $categoryId,
            'search'     => $search
        ]);
    }

    public function store(): void {
        $this->requirePermission('catalog.manage');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $categoryId = (int) Request::post('category_id');
        $mode = Request::post('pricing_mode', 'pcs');
        $basePrice = (float) Request::post('base_price', 0);
        $minPrice = (float) Request::post('min_price', 0);
        $margin = (float) Request::post('default_margin_percent', 30);
        $waste = (float) Request::post('waste_percent', 5);
        $desc = trim(Request::post('description'));

        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM products");
        $code = 'PRD-' . strtoupper(substr($mode, 0, 3)) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        try {
            Database::insert('products', [
                'company_id'             => 1,
                'category_id'            => $categoryId,
                'code'                   => $code,
                'name'                   => $name,
                'description'            => $desc,
                'pricing_mode'           => $mode,
                'base_price'             => $basePrice,
                'min_price'              => $minPrice ?: ($basePrice * 0.8),
                'default_margin_percent' => $margin,
                'waste_percent'          => $waste,
                'is_active'              => 1
            ]);

            $this->flash('success', "Produk baru '{$name}' ({$code}) berhasil ditambahkan!");
            $this->redirect('/products');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambah produk: ' . $e->getMessage());
            $this->back();
        }
    }

    public function update(string $id): void {
        $this->requirePermission('catalog.manage');
        $this->validateCSRF();
        $productId = (int) $id;

        $name = trim(Request::post('name'));
        $categoryId = (int) Request::post('category_id');
        $mode = Request::post('pricing_mode', 'pcs');
        $basePrice = (float) Request::post('base_price', 0);
        $minPrice = (float) Request::post('min_price', 0);
        $margin = (float) Request::post('default_margin_percent', 30);
        $desc = trim(Request::post('description'));

        try {
            Database::update('products', [
                'name'                   => $name,
                'category_id'            => $categoryId,
                'pricing_mode'           => $mode,
                'base_price'             => $basePrice,
                'min_price'              => $minPrice,
                'default_margin_percent' => $margin,
                'description'            => $desc
            ], 'id = :id', ['id' => $productId]);

            $this->flash('success', "Produk '{$name}' berhasil diperbarui!");
            $this->redirect('/products');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal update produk: ' . $e->getMessage());
            $this->back();
        }
    }

    public function delete(string $id): void {
        $this->requirePermission('catalog.manage');
        $this->validateCSRF();
        $productId = (int) $id;

        Database::update('products', ['is_active' => 0], 'id = :id', ['id' => $productId]);
        $this->flash('success', 'Produk berhasil dinonaktifkan.');
        $this->redirect('/products');
    }

    public function storeFinishing(): void {
        $this->requirePermission('catalog.manage');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $type = Request::post('calculation_type', 'per_pcs');
        $cost = (float) Request::post('cost_price', 0);
        $selling = (float) Request::post('selling_price', 0);

        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM product_finishing");
        $code = 'FIN-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        try {
            Database::insert('product_finishing', [
                'company_id'       => 1,
                'code'             => $code,
                'name'             => $name,
                'calculation_type' => $type,
                'cost_price'       => $cost,
                'selling_price'    => $selling,
                'is_active'        => 1
            ]);

            $this->flash('success', "Opsi finishing '{$name}' berhasil ditambahkan!");
            $this->redirect('/products');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambah finishing: ' . $e->getMessage());
            $this->back();
        }
    }
}
