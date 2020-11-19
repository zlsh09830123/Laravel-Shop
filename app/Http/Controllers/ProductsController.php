<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 建立一個 Query builder
        $builder = Product::query()->where('on_sale', true);
        // 判斷是否有提交 search 參數，如果有就賦值給 $search 變量
        // search 參數用來模糊搜尋商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜尋商品標題、商品描述、SKU 標題、SKU 描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                });
            });
        }

        // 是否有提交 order 參數，如果有就賦值給 $order 變量
        // order 參數用來控制商品的排序規則
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或 _desc 結尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字串的開頭符合在此陣列中的字串之一，說明這是一個合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根據傳入的排序值來建立排序參數
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }

    public function show(Product $product, Request $request)
    {
        // 判斷商品是否已經上架，如果沒有上架則拋出異常
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;
        // 用戶未登入時返回的是 null，已登入時返回的是對應的用戶對象
        if ($user = $request->user()) {
            // 從當前用戶已收藏的商品中搜尋 id 為當前商品的 id 的商品
            // boolval() 函數用於把值轉為布林值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 預先加載關聯關係
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 篩選出已評價的商品
            ->orderBy('reviewed_at', 'desc') // 按評價時間倒序排序
            ->limit(10) // 取出 10 條
            ->get();

        return view('products.show', ['product' => $product, 'favored' => $favored, 'reviews' => $reviews]);
    }

    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }
}
