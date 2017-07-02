<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Excel;
use App\Product;
use App\Manufacturer;
use App\Pricing;
use App\Supplier;
use App\Translation;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {filename} {kgShim?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename   =   $this->argument('filename');
        $kg_shim    =   $this->argument('kgShim') ? 1000 : 1;
        $error      =   '%1$s (Barcode: %2$s) already exists. Not importing to database.';

        Excel::load($filename, function($reader) use($error, $kg_shim) {
            $items = $reader->get();
            $importedCount = 0;

            /**
             * The last row should in theory have the count of items
             * to import, so we can ignore.
             * */

            for ($i=0; $i<$items->count(); $i++)
            {
                $item = $items[$i]->toArray();

                if ($title = $item['Title']) {
                    $barcode = $item['Barcode'];
                    $manufacturer_title = trim($item['Manufacturer']);
                    $supplier_title = $item['Supplier'];
                    $chinese_title = empty($item['Title_CN']) ? null : $item['Title_CN'];
                    $weight = $item['Weight'];
                    $item['Weight'] = $weight / $kg_shim;

                    // check to see if photoset exists, if they do, then don't import.


                    if (!empty($supplier_title)) {
                        $suppliers = Supplier::select('*')
                                        ->where('Title', $supplier_title)
                                        ->get();

                        $supplier = $suppliers->first();
                        if (empty($supplier)) {
                            $supplier = new Supplier(array('Title' => $supplier_title));
                            $supplier->save();
                        }

                        $item['supplier_id'] = $supplier->id;
                    }

                    $existing = Product::select('*')
                                        ->where('Barcode', $barcode)
                                        ->where('supplier_id', $supplier->id)
                                        ->get();

                    $cost = !empty($item['Cost']) ? $item['Cost'] : 0;
                    $price = !empty($item['Price']) ? $item['Price'] : 0;

                    if (!empty($manufacturer_title)) {
                        $manufacturers = Manufacturer::select('*')
                                            ->where('Title', $manufacturer_title)
                                            ->get();

                        $manufacturer = $manufacturers->first();

                        if (empty($manufacturer)) {
                            $manufacturer = new Manufacturer(array('Title' => $manufacturer_title));
                            $manufacturer->save();
                        }

                        $item['manufacturer_id'] = $manufacturer->id;
                    }

                    unset($item['Price']);
                    unset($item['Cost']);
                    unset($item['Supplier']);
                    unset($item['Manufacturer']);

                    if ($existing->count() == 0) {


                        $product = new Product($item);
                        $product->save();
                        if (!empty($chinese_title)) {
                            $translation = new Translation(array('Chinese' => $chinese_title, 'product_id' => $product->id));
                            $translation->save();
                            // $item['translation_id'] = $translation->id;
                        }
                        $importedCount++;
                        $pricing = new Pricing(array(
                            'Cost'          =>  $cost,
                            'Price'         =>  $price,
                            'product_id'    =>  $product->id
                        ));

                        $pricing->save();
                    } else {
                        $product = $existing->first();
                        echo sprintf($error,
                            $product->Title,
                            $product->Barcode) . PHP_EOL;
                    }
                }
            }

            echo "----" . PHP_EOL;
            echo 'You have imported ' . $importedCount . ' records.' . PHP_EOL;
        });
    }
}
