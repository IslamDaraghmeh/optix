<?php
/**
 * POS Controller
 *
 * Handles Point of Sale operations including cart, checkout, payments, and receipts
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\Inventory;
use App\Models\Patient;
use App\Helpers\PDF;
use App\Helpers\Email;

class POSController extends BaseController
{
    private Product $productModel;
    private Transaction $transactionModel;
    private Inventory $inventoryModel;
    private Patient $patientModel;
    private PDF $pdf;
    private Email $email;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->transactionModel = new Transaction();
        $this->inventoryModel = new Inventory();
        $this->patientModel = new Patient();
        $this->pdf = new PDF();
        $this->email = new Email();
    }

    public function index(): void
    {
        $this->requirePermission('access_pos');
        $this->view('pos/index', [
            'cart' => $this->getCart(),
            'locationId' => $this->session->get('location_id')
        ]);
    }

    public function addToCart(): void
    {
        $this->requirePermission('access_pos');
        $this->requireCsrfToken();

        $productId = (int)$this->post('product_id');
        $quantity = (int)$this->post('quantity', 1);

        $product = $this->productModel->find($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $cart = $this->getCart();
        $itemKey = 'item_' . $productId;

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'sku' => $product['sku'],
                'price' => $product['selling_price'],
                'tax_rate' => $product['tax_rate'],
                'quantity' => $quantity
            ];
        }

        $this->session->set('pos_cart', $cart);
        $this->json(['success' => true, 'cart' => $cart, 'total' => $this->calculateCartTotal($cart)]);
    }

    public function removeFromCart(): void
    {
        $this->requirePermission('access_pos');
        $this->requireCsrfToken();

        $itemKey = $this->post('item_key');
        $cart = $this->getCart();

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            $this->session->set('pos_cart', $cart);
        }

        $this->json(['success' => true, 'cart' => $cart, 'total' => $this->calculateCartTotal($cart)]);
    }

    public function updateCartItem(): void
    {
        $this->requirePermission('access_pos');
        $this->requireCsrfToken();

        $itemKey = $this->post('item_key');
        $quantity = (int)$this->post('quantity');
        $cart = $this->getCart();

        if (isset($cart[$itemKey]) && $quantity > 0) {
            $cart[$itemKey]['quantity'] = $quantity;
            $this->session->set('pos_cart', $cart);
        }

        $this->json(['success' => true, 'cart' => $cart, 'total' => $this->calculateCartTotal($cart)]);
    }

    public function getCart(): array
    {
        return $this->session->get('pos_cart', []);
    }

    public function checkout(): void
    {
        $this->requirePermission('process_sales');
        $this->requireCsrfToken();

        $cart = $this->getCart();
        if (empty($cart)) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        $totals = $this->calculateCartTotal($cart);
        $patientId = $this->post('patient_id', null);
        $locationId = (int)$this->post('location_id');
        $paymentMethod = $this->post('payment_method');
        $amountPaid = (float)$this->post('amount_paid');

        $transactionData = [
            'patient_id' => $patientId,
            'location_id' => $locationId,
            'cashier_id' => $this->auth->getUserId(),
            'transaction_date' => date('Y-m-d'),
            'transaction_time' => date('H:i:s'),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $this->post('discount', 0),
            'total' => $totals['total'],
            'amount_paid' => $amountPaid,
            'amount_due' => max(0, $totals['total'] - $amountPaid),
            'change_given' => max(0, $amountPaid - $totals['total']),
            'status' => 'completed',
            'payment_status' => $amountPaid >= $totals['total'] ? 'paid' : 'partial'
        ];

        $items = [];
        foreach ($cart as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $taxAmount = $lineTotal * ($item['tax_rate'] / 100);

            $items[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal + $taxAmount,
                'created_at' => date(DATETIME_FORMAT)
            ];
        }

        try {
            $transactionId = $this->transactionModel->createWithItems($transactionData, $items);

            // Process payment
            $this->processPayment($transactionId, $paymentMethod, $amountPaid);

            // Clear cart
            $this->session->remove('pos_cart');

            $this->logActivity('sale_completed', "Completed sale transaction ID: {$transactionId}");
            $this->json(['success' => true, 'transaction_id' => $transactionId]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to process checkout'], 500);
        }
    }

    public function processPayment(int $transactionId, string $method, float $amount): bool
    {
        try {
            $this->db->insert('payments', [
                'transaction_id' => $transactionId,
                'payment_method' => $method,
                'amount' => $amount,
                'payment_date' => date(DATETIME_FORMAT),
                'status' => 'completed',
                'created_at' => date(DATETIME_FORMAT)
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateReceipt(int $transactionId): void
    {
        $this->requirePermission('view_sales');

        $transaction = $this->transactionModel->getWithItems($transactionId);
        if (!$transaction) {
            $this->error404('Transaction not found');
        }

        $html = $this->buildReceiptHtml($transaction);
        $filename = "receipt_{$transaction['transaction_number']}.pdf";

        $this->pdf->generateFromHtml($html, $filename, 'I');
    }

    public function emailReceipt(int $transactionId): void
    {
        $this->requirePermission('email_receipts');
        $this->requireCsrfToken();

        $transaction = $this->transactionModel->getWithItems($transactionId);
        if (!$transaction || !$transaction['patient_email']) {
            $this->json(['success' => false, 'message' => 'Transaction or email not found'], 404);
        }

        try {
            $html = $this->buildReceiptHtml($transaction);
            $pdfPath = STORAGE_PATH . '/temp/receipt_' . $transaction['transaction_number'] . '.pdf';
            $this->pdf->generateFromHtml($html, $pdfPath, 'F');

            $sent = $this->email->send(
                $transaction['patient_email'],
                'Receipt from ' . APP_NAME,
                'Please find your receipt attached.',
                [$pdfPath]
            );

            if (file_exists($pdfPath)) unlink($pdfPath);

            if ($sent) {
                $this->json(['success' => true, 'message' => 'Receipt emailed successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to send email'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to email receipt'], 500);
        }
    }

    public function searchProducts(): void
    {
        $this->requirePermission('access_pos');

        $term = $this->get('term', '');
        $products = $this->productModel->search($term, null, true, 20);

        $this->json(['success' => true, 'data' => $products]);
    }

    public function scanBarcode(): void
    {
        $this->requirePermission('access_pos');

        $barcode = $this->get('barcode');
        $product = $this->productModel->findByBarcode($barcode);

        if ($product) {
            $this->json(['success' => true, 'data' => $product]);
        } else {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }

    private function calculateCartTotal(array $cart): array
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($cart as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $subtotal += $lineTotal;
            $tax += $lineTotal * ($item['tax_rate'] / 100);
        }

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax
        ];
    }

    private function buildReceiptHtml(array $transaction): string
    {
        $html = '<html><head><style>body{font-family:Arial;} table{width:100%;border-collapse:collapse;} th,td{padding:8px;text-align:left;}</style></head><body>';
        $html .= '<h1>' . APP_NAME . '</h1>';
        $html .= '<p>Receipt #: ' . $transaction['transaction_number'] . '</p>';
        $html .= '<p>Date: ' . $transaction['transaction_date'] . ' ' . $transaction['transaction_time'] . '</p>';
        $html .= '<table border="1"><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>';

        foreach ($transaction['items'] as $item) {
            $html .= '<tr><td>' . $item['product_name'] . '</td><td>' . $item['quantity'] . '</td><td>$' . number_format($item['unit_price'], 2) . '</td><td>$' . number_format($item['line_total'], 2) . '</td></tr>';
        }

        $html .= '</table>';
        $html .= '<p><strong>Subtotal:</strong> $' . number_format($transaction['subtotal'], 2) . '</p>';
        $html .= '<p><strong>Tax:</strong> $' . number_format($transaction['tax'], 2) . '</p>';
        $html .= '<p><strong>Total:</strong> $' . number_format($transaction['total'], 2) . '</p>';
        $html .= '<p><strong>Paid:</strong> $' . number_format($transaction['amount_paid'], 2) . '</p>';
        $html .= '<p>Thank you for your business!</p>';
        $html .= '</body></html>';

        return $html;
    }
}
