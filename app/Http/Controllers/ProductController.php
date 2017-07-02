<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Manufacturer;
use App\Translation;
use App\Pricing;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    private $page_size = 50;

    public function post ($product_id, Request $request)
    {
        if (!empty($request->title) && !empty($request->barcode) && !empty($request->supplier_id)) {

            $manufacturer_title             =   $request->manufacturer;

            if (!empty($manufacturer_title)) {
                $manufacturers              =   Manufacturer::select('*')->where('Title', $manufacturer_title)->get();
                $manufacturer               =   $manufacturers->first();

                if (empty($manufacturer)) {
                    $manufacturer           =   new Manufacturer(array('Title' => $manufacturer_title));
                    $manufacturer->save();
                }
            }

            $product                        =   null;

            if (!empty($product_id)) {
                $product                    =   Product::get()->where('id', $product_id)
                                                              ->where('supplier_id', $request->supplier_id);

                $product                    =   $product->first();
            }

            $product                        =   !empty($product) ? $product : (new Product());

            $product->Title                 =   $request->title;
            $product->Barcode               =   $request->barcode;

            $product->Measurement           =   $request->measurement;
            $product->Width                 =   $request->width;
            $product->Height                =   $request->height;
            $product->Depth                 =   $request->depth;
            $product->Weight                =   $request->weight;
            $product->StockCount            =   $request->stock_count;
            $product->supplier_id           =   $request->supplier_id;

            if (!empty($manufacturer)) {
                $product->manufacturer_id   =   $manufacturer->id;
            }

            $product->save();

            $cost                           =   $request->cost;
            $price                          =   $request->price;

            if (!empty($cost) || !empty($price)) {
                $create_new                 =   true;
                $cost                       =   !empty($cost) ? $cost : 0;
                $price                      =   !empty($price) ? $price : ($cost * 1.3225);

                if ($cur_pricing = $product->Pricings->first()) {
                    if ($cur_pricing->Price == $price && $cur_pricing->Cost == $cost) {
                        $create_new         =   false;
                    }
                }

                if ($create_new) {
                    $pricing = new Pricing(array(
                        'Cost'              =>  $cost,
                        'Price'             =>  $price,
                        'product_id'        =>  $product->id
                    ));

                    $pricing->save();
                }
            }

            $chinese_title                  =   $request->chinese_title;

            if (!empty($product->Translation)) {
                $translation                =   $product->Translation;
                $translation->Chinese       =   !empty($chinese_title) ? $chinese_title : '';
                $translation->save();
            } else {
                if (!empty($chinese_title)) {
                    $translation            =   new Translation(array('Chinese' => $chinese_title, 'product_id' => $product->id));
                    $translation->save();
                }
            }

            $last_update            =   \DateTime::createFromFormat('Y-m-d H:i:s', $product->updated_at->toDateTimeString());
            $last_update            =   $last_update->setTimezone(new \DateTimeZone('Pacific/Auckland'));

            return response()->json(
                [
                    'id'            =>  $product->id,
                    'title'         =>  $product->Title,
                    'barcode'       =>  $product->Barcode,
                    'chinese_title' =>  !empty($chinese_title) ? $chinese_title : null,
                    'measurement'   =>  $product->Measurement,
                    'width'         =>  $product->Width,
                    'height'        =>  $product->Height,
                    'depth'         =>  $product->Depth,
                    'weight'        =>  $product->Weight,
                    'manufacturer'  =>  !empty($product->Manufacturer) ? $product->Manufacturer->Title : '',
                    'stock_count'   =>  $product->StockCount,
                    'cost'          =>  $cost,
                    'price'         =>  $price,
                    'last_update'   =>  $last_update->format('Y-m-d H:i:s')
                ]
            );
        }

        return response()->json(array(
                            'code'          =>  500,
                            'message'       =>  'failure'
                    ));
    }

    public function get (Request $request)
    {
        $page = !empty($request->page) ? $request->page : 1;

        if ($barcode = $request->barcode) {
            if ($product = Product::where('Barcode', $barcode)->get()->first()) {
                $pricing            =   $product->Pricings->first();
                $last_update        =   \DateTime::createFromFormat('Y-m-d H:i:s', $product->updated_at->toDateTimeString());
                $last_update        =   $last_update->setTimezone(new \DateTimeZone('Pacific/Auckland'));
                // $data = empty($request->detailed) ? [
                //     'id'            =>  $product->id,
                //     'title'         =>  $product->Title,
                //     'price'         =>  $pricing ? $pricing->Price : 0
                // ] : [
                //     'id'            =>  $product->id,
                //     'title'         =>  $product->Title,
                //     'barcode'       =>  $product->Barcode,
                //     'chinese_title' =>  !empty($product->Translation->Chinese) ? $product->Translation->Chinese : null,
                //     'measurement'   =>  $product->Measurement,
                //     'width'         =>  $product->Width,
                //     'height'        =>  $product->Height,
                //     'depth'         =>  $product->Depth,
                //     'weight'        =>  $product->Weight,
                //     'manufacturer'  =>  !empty($product->Manufacturer) ? $product->Manufacturer->Title : '',
                //     'stock_count'   =>  $product->StockCount,
                //     'cost'          =>  $pricing ? $pricing->Cost : 0,
                //     'price'         =>  $pricing ? $pricing->Price : 0,
                //     'last_update'   =>  $last_update->format('Y-m-d H:i:s')
                // ];

                $data               =   null;

                if (empty($request->detailed) && empty($request->yogo)) {
                    $data           =   [
                                            'id'            =>  $product->id,
                                            'title'         =>  $product->Title,
                                            'price'         =>  $pricing ? $pricing->Price : 0
                                        ];
                } else if (!empty($request->detailed)){
                    $data           =   [
                                            'id'            =>  $product->id,
                                            'title'         =>  $product->Title,
                                            'barcode'       =>  $product->Barcode,
                                            'chinese_title' =>  !empty($product->Translation->Chinese) ? $product->Translation->Chinese : null,
                                            'measurement'   =>  $product->Measurement,
                                            'width'         =>  $product->Width,
                                            'height'        =>  $product->Height,
                                            'depth'         =>  $product->Depth,
                                            'weight'        =>  $product->Weight,
                                            'manufacturer'  =>  !empty($product->Manufacturer) ? $product->Manufacturer->Title : '',
                                            'stock_count'   =>  $product->StockCount,
                                            'cost'          =>  $pricing ? $pricing->Cost : 0,
                                            'price'         =>  $pricing ? $pricing->Price : 0,
                                            'last_update'   =>  $last_update->format('Y-m-d H:i:s')
                                        ];
                } else {
                    $data           =   [
                                            'MCProductID'   =>  $product->id,
                                            'Title'         =>  $product->Title,
                                            'Chinese'       =>  !empty($product->Translation->Chinese) ? $product->Translation->Chinese : null,
                                            'Measurement'   =>  $product->Measurement,
                                            'Width'         =>  $product->Width,
                                            'Height'        =>  $product->Height,
                                            'Depth'         =>  $product->Depth,
                                            'Weight'        =>  $product->Weight,
                                            'Manufacturer'  =>  !empty($product->Manufacturer) ? $product->Manufacturer->Title : '',
                                            'Price'         =>  $pricing ? $pricing->Price : 0,
                                            'SupplierID'    =>  $product->supplier_id

                                        ];
                }

                return response()->json($data);
            }

            return response()->json(array());
        } elseif ($supplierID = $request->supplier) {
            $raw                    =   Product::where('supplier_id', $supplierID)->orderBy('Title', 'asc')->get();
            $products               =   $raw->forPage($page, $this->page_size);
            $datalist               =   [];
            foreach($products as $product)
            {
                $pricing            =   $product->Pricings->first();
                $data = [
                    'id'            =>  $product->id,
                    'title'         =>  $product->Title,
                    'barcode'       =>  $product->Barcode,
                    'chinese_title' =>  !empty($product->Translation->Chinese) ? $product->Translation->Chinese : null,
                    'measurement'   =>  $product->Measurement,
                    'width'         =>  $product->Width,
                    'height'        =>  $product->Height,
                    'depth'         =>  $product->Depth,
                    'weight'        =>  $product->Weight,
                    'manufacturer'  =>  !empty($product->Manufacturer) ? $product->Manufacturer->Title : '',
                    'stock_count'   =>  $product->StockCount,
                    'cost'          =>  $pricing ? $pricing->Cost : 0,
                    'price'         =>  $pricing ? $pricing->Price : 0,
                    'last_update'   =>  $product->updated_at
                ];

                $datalist[] = $data;
            }

            $response               =   [
                                            'item_count'    =>  $raw->count(),
                                            'page_count'    =>  ceil($raw->count() / $this->page_size),
                                            'data'          =>  $datalist
                                        ];

            return response()->json($response);
        } else {
            $products = Product::orderBy('Title', 'asc')->get()->forPage($page, $this->page_size);
            $datalist = [];
            foreach($products as $product)
            {
                $data = [
                    'id'        =>  $product->id,
                    'title'     =>  $product->Title,
                    'price'     =>  $product->Pricings->first() ? $product->Pricings->first()->Price : 0
                ];
                $datalist[] = $data;
            }
            return response()->json($datalist);
        }
    }
}
