<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\WarehouseRequest;
use App\Models\Category;
use App\Models\Gallery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->input('query') ?? '';
        $warehouses = Warehouse::with('category')
            ->where('user_id', auth()->user()->id)
            ->where('product_name', 'LIKE', '%' . $query . '%')
            ->paginate(10);

        $total = Warehouse::where('is_accepted', '=', '1')
            ->where('user_id', auth()->user()->id)->count();
        $categories_warehouse = Warehouse::where('is_accepted', '=', '1')->where('user_id', auth()->user()->id)->select(DB::raw('count(*) as total'))->groupBy('category_id')->get();

        $galleries_sold = Gallery::where('merchant_id',  auth()->user()->merchant->merchant_id)
            ->with(['transaction' => function ($query) {
                $query->select('gallery_id', 'quantity');
            }])
            ->paginate(10);

        foreach ($galleries_sold as $gallery) {
            foreach ($gallery->transaction as $checkout) {
                $gallery->sold_out += $checkout->quantity;
            }
        }
        // return $warehouses;
        return view('user.toko.warehouse.index', compact('warehouses', 'total', 'categories_warehouse', 'galleries_sold'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('user.toko.warehouse.add', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreWarehouseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WarehouseRequest $request)    
    {
        $input = $request->validated();

        $image = $request->file('upload_image');

        $imageName = time() . '.' . $image->getClientOriginalExtension();

        $image->move(public_path('images'), $imageName);

        Warehouse::create(
            [
                'merchant_id' => auth()->user()->merchant->merchant_id,
                'category_id' => $input['jenis_produk'],
                'user_id' => auth()->user()->id,
                'product_name' => $input['nama_produk'],
                'product_description' => $input['deskripsi_produk'],
                'image' => $imageName,
                'price' => $input['harga'],
                'heavy' => $input['berat'],
                'stok' => $input['jumlah'],
                'expired_at' => Carbon::createFromFormat('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($input['expired_date'])))->format('Y-m-d h:i:s'),
                'is_request' => 1,
                'is_accepted' => 0,
                'alasan_ditolak' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Mollitia eligendi deleniti cum tempore. Ipsa laudantium architecto perspiciatis cupiditate, rerum omnis temporibus non. Ducimus beatae deleniti, hic esse dignissimos recusandae maxime!',
                'in_gallery' => 0,
                'is_deleted' => 0,
            ]
        );

        return redirect()->route('warehouse.index')->with('status', 'Success menambah request');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit(Warehouse $warehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWarehouseRequest  $request
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse)
    {
        //
    }

    public function warehouseListAPI()
    {
        $warehouse = Warehouse::where('is_accepted', 1)->paginate(10)->withQueryString();

        return response()->json($warehouse);
    }
}
