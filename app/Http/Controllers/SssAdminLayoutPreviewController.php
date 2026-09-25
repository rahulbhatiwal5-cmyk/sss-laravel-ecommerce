<?php
namespace App\Http\Controllers;
use Illuminate\View\View;
class SssAdminLayoutPreviewController extends Controller
{
    public function editProduct(int $product): View
    {
        $item = collect(config('sss-admin-demo.products', []))->firstWhere('id', $product);
        abort_unless($item, 404);
        return view('sss-admin.products.form', ['product' => $item]);
    }
    public function showOrder(string $order): View
    {
        $item = collect(config('sss-admin-demo.orders', []))->firstWhere('id', $order);
        abort_unless($item, 404);
        return view('sss-admin.orders.show', ['order' => $item]);
    }
}
