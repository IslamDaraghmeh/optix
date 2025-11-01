<?php
/**
 * Inventory Controller - Manages product inventory, stock levels, adjustments, transfers
 */

namespace App\Controllers;

use App\Models\Product;
use App\Models\Inventory;

class InventoryController extends BaseController
{
    private Product $productModel;
    private Inventory $inventoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->inventoryModel = new Inventory();
    }

    public function products(): void
    {
        $this->requirePermission('view_products');
        $page = (int)$this->get('page', 1);
        $search = $this->get('search', '');
        $category = $this->get('category', null);

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR sku LIKE ? OR barcode LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : null;
        $products = $this->productModel->paginate($page, 20, $whereClause, $params, 'name', 'ASC');

        $this->view('inventory/products', ['products' => $products, 'search' => $search, 'category' => $category]);
    }

    public function createProduct(): void
    {
        $this->requirePermission('create_products');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = ['sku' => 'required', 'name' => 'required', 'category' => 'required', 'selling_price' => 'required'];

            if (!$this->validate($this->post(), $rules)) {
                $this->flashAndRedirect('error', 'Validation failed', $this->back());
            }

            $data = [
                'sku' => $this->post('sku'),
                'barcode' => $this->post('barcode'),
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'category' => $this->post('category'),
                'brand' => $this->post('brand'),
                'supplier_id' => $this->post('supplier_id'),
                'cost_price' => $this->post('cost_price', 0),
                'selling_price' => $this->post('selling_price'),
                'tax_rate' => $this->post('tax_rate', 0),
                'is_taxable' => $this->post('is_taxable', true),
                'is_active' => true
            ];

            try {
                $id = $this->productModel->create($data);
                $this->logActivity('product_created', "Created product ID: {$id}");
                $this->flashAndRedirect('success', 'Product created successfully', APP_URL . '/inventory/products');
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to create product', $this->back());
            }
        } else {
            $this->view('inventory/create-product');
        }
    }

    public function editProduct(int $id): void
    {
        $this->requirePermission('edit_products');
        $product = $this->productModel->find($id);

        if (!$product) {
            $this->error404('Product not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = array_filter([
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'category' => $this->post('category'),
                'brand' => $this->post('brand'),
                'supplier_id' => $this->post('supplier_id'),
                'cost_price' => $this->post('cost_price'),
                'selling_price' => $this->post('selling_price'),
                'tax_rate' => $this->post('tax_rate'),
                'is_active' => $this->post('is_active')
            ], function($v) { return $v !== null; });

            try {
                $this->productModel->update($id, $data);
                $this->logActivity('product_updated', "Updated product ID: {$id}");
                $this->flashAndRedirect('success', 'Product updated successfully', APP_URL . '/inventory/products');
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to update product', $this->back());
            }
        } else {
            $this->view('inventory/edit-product', ['product' => $product]);
        }
    }

    public function deleteProduct(int $id): void
    {
        $this->requirePermission('delete_products');
        $this->requireCsrfToken();

        try {
            $this->productModel->delete($id);
            $this->logActivity('product_deleted', "Deleted product ID: {$id}");
            $this->json(['success' => true, 'message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete product'], 500);
        }
    }

    public function stock(): void
    {
        $this->requirePermission('view_inventory');
        $locationId = $this->get('location_id', null);
        $lowStockOnly = $this->get('low_stock_only', false);

        $inventory = $this->inventoryModel->getWithProductDetails($locationId, $lowStockOnly);
        $statistics = $this->inventoryModel->getStatistics($locationId);

        $this->view('inventory/stock', ['inventory' => $inventory, 'statistics' => $statistics, 'locationId' => $locationId]);
    }

    public function adjustStock(): void
    {
        $this->requirePermission('adjust_inventory');
        $this->requireCsrfToken();

        $productId = (int)$this->post('product_id');
        $locationId = (int)$this->post('location_id');
        $quantityChange = (int)$this->post('quantity_change');
        $adjustmentType = $this->post('adjustment_type');
        $reason = $this->post('reason');

        try {
            $result = $this->inventoryModel->adjustInventory(
                $productId,
                $locationId,
                $quantityChange,
                $adjustmentType,
                $reason,
                $this->auth->getUserId()
            );

            if ($result) {
                $this->logActivity('inventory_adjusted', "Adjusted inventory for product ID: {$productId}");
                $this->json(['success' => true, 'message' => 'Inventory adjusted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to adjust inventory'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to adjust inventory'], 500);
        }
    }

    public function transfer(): void
    {
        $this->requirePermission('transfer_inventory');
        $this->requireCsrfToken();

        $productId = (int)$this->post('product_id');
        $fromLocationId = (int)$this->post('from_location_id');
        $toLocationId = (int)$this->post('to_location_id');
        $quantity = (int)$this->post('quantity');
        $reason = $this->post('reason');

        try {
            $result = $this->inventoryModel->transferInventory(
                $productId,
                $fromLocationId,
                $toLocationId,
                $quantity,
                $this->auth->getUserId(),
                $reason
            );

            if ($result) {
                $this->logActivity('inventory_transferred', "Transferred {$quantity} units of product ID: {$productId}");
                $this->json(['success' => true, 'message' => 'Inventory transferred successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Insufficient stock or transfer failed'], 400);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to transfer inventory'], 500);
        }
    }

    public function lowStock(): void
    {
        $this->requirePermission('view_inventory');
        $locationId = $this->get('location_id', null);

        $lowStockItems = $this->inventoryModel->getLowStockItems($locationId);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $lowStockItems]);
        } else {
            $this->view('inventory/low-stock', ['items' => $lowStockItems, 'locationId' => $locationId]);
        }
    }

    public function stockHistory(): void
    {
        $this->requirePermission('view_inventory_history');

        $productId = $this->get('product_id', null);
        $locationId = $this->get('location_id', null);
        $startDate = $this->get('start_date', null);
        $endDate = $this->get('end_date', null);

        $history = $this->inventoryModel->getAdjustmentHistory($productId, $locationId, $startDate, $endDate);

        $this->view('inventory/history', ['history' => $history]);
    }
}
