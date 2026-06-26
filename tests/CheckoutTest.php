<?php

require_once __DIR__ . '/../src/Checkout.php';

use PHPUnit\Framework\TestCase;
use App\Checkout;

class CheckoutTest extends TestCase
{
    private $seedFile;
    private $testFile;
    private $orderFile;
    private $checkout;

    protected function setUp(): void
    {
        $this->seedFile = __DIR__ . '/../data/products_seed.json';
        $this->testFile = __DIR__ . '/../data/products_test.json';
        $this->orderFile = __DIR__ . '/../data/orders_test.json';

        // Pastikan file seed ada
        $this->assertFileExists($this->seedFile, "products_seed.json tidak ditemukan!");

        // Salin seed menjadi data test
        copy($this->seedFile, $this->testFile);

        // Buat file orders kosong
        file_put_contents($this->orderFile, json_encode([], JSON_PRETTY_PRINT));

        // Inisialisasi Checkout
        $this->checkout = new Checkout($this->testFile, $this->orderFile);
    }

    public function testCheckoutReducesStock()
    {
        // Pastikan produk tersedia
        $products = json_decode(file_get_contents($this->testFile), true);

        $this->assertArrayHasKey('PRD-002', $products);
        $this->assertEquals(5, $products['PRD-002']['stok']);

        // Checkout
        $keranjang = [
            'PRD-002' => 1
        ];

        $nota = $this->checkout->prosesCheckout(
            'test@mail.com',
            'Jl. Sudirman',
            $keranjang
        );

        // Baca ulang file produk
        $products = json_decode(file_get_contents($this->testFile), true);

        // Pastikan stok berkurang
        $this->assertEquals(4, $products['PRD-002']['stok']);

        // Pastikan nota berhasil dibuat
        $this->assertEquals('test@mail.com', $nota['email']);
        $this->assertEquals('Menunggu Pembayaran', $nota['status']);

        // Pastikan order tersimpan
        $orders = json_decode(file_get_contents($this->orderFile), true);
        $this->assertCount(1, $orders);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }

        if (file_exists($this->orderFile)) {
            unlink($this->orderFile);
        }
    }
}