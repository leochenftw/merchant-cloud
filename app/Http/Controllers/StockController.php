<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Manufacturer;
use App\Translation;
use App\Pricing;
use Illuminate\Http\Response;

class StockController extends Controller
{
    public function post ($action, Request $request)
    {
        $operation = 1;

        if ($action == 'minus' || $action == 'reduce' || $action == 'deduct') {
            $operation = -1;
        }

        $products = json_decode($request->products);

        $titles = [];
        $was = [];
        $now = [];

        foreach ($products as $key => $value) {
            $product    =   Product::get()->where('id', $key)
                                            ->where('supplier_id', $request->supplier_id)
                                            ->first();
            if (!empty($product)) {
                $titles[] = $product->Title;
                $was[] = $product->StockCount;
                $product->StockCount += ($operation * $value);
                $product->save();
                $now[] = $product->StockCount;
            }
        }

        return response()->json(array('affected_products' => $titles, 'was' => $was, 'now' => $now));
    }
}
