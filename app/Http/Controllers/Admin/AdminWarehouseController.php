<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Merchants;
use App\Models\Category;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AdminWarehouseController extends Controller
{
    private $baseApiUrl;

    public function __construct()
    {
        $this->baseApiUrl = 'https://kreatif.tobakab.go.id/api';
    }

    public function index()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();

            if ($cek_admin_id) {
                try {
                    try {
                        $stocks = Stock::with(['merchant'])
                            ->where('sisa_stok', '>', 0)
                            ->get();
                        
                        $transaksi = Transaksi::All();
                
                        $availableProductsCount = $stocks->sum('sisa_stok');
                        $soldProduct = $transaksi->sum('jumlah_barang_keluar'); 
            
                        $client = new Client();
                        $response_product = $client->get($this->baseApiUrl . '/listdaftarproduk');
                        $response_category = $client->get($this->baseApiUrl . '/pilihkategori');
                
                        if ($response_product->getStatusCode() !== 200 || $response_category->getStatusCode() !== 200) {
                            throw new \Exception('Gagal mengambil data produk atau kategori dari API');
                        }
                
                        $products = json_decode($response_product->getBody()->getContents(), true);
                        $categories = json_decode($response_category->getBody()->getContents(), true);
                
                        $productMap = collect($products)->keyBy('product_id');
                        $categoryMap = collect($categories)->keyBy('category_id');
                
                        $data = $stocks->map(function ($stock) use ($productMap, $categoryMap) {
                            $product = $productMap->get($stock->product_id);
                
                            $categoryId = $product['category_id'] ?? null;
                
                            $categoryName = $categoryMap->get($categoryId)['nama_kategori'] ?? 'Kategori Tidak Ditemukan';
                
                            return [
                                'stock_id' => $stock->stock_id,
                                'product_name' => $product['product_name'] ?? 'Produk Tidak Ditemukan',
                                'merchant_name' => $stock->merchant->nama_merchant,
                                'stok' => $stock->jumlah_stok,
                                'sisa_stok' => $stock->sisa_stok,
                                'kategori' => $categoryName,
                                'spesifikasi' => $stock->spesifikasi,
                                'hargamodal' => $stock->hargamodal,
                                'hargajual' => $stock->hargajual,
                                'tanggal_masuk' => $stock->tanggal_masuk,
                                'tanggal_expired' => $stock->tanggal_expired,
                            ];
                        });
                
                        $data = $data->sortByDesc('stock_id')->values()->all();
                
                        return view('admin.warehouse.index', [
                            'data' => $data,
                            'availableProductsCount' => $availableProductsCount,
                            'soldProduct' => $soldProduct
                        ]);
                    } catch (\Exception $e) {
                        return view('admin.warehouse.index', ['error' => $e->getMessage()]);
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function produkwarehouse()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    try {
                        $stocks = Stock::with(['merchant'])
                            ->where('sisa_stok', '>', 0)
                            ->get();
                
                        $client = new Client();
                
                        $response_product = $client->get($this->baseApiUrl . '/listdaftarproduk');
                        $response_category = $client->get($this->baseApiUrl . '/pilihkategori');
                
                        if ($response_product->getStatusCode() !== 200 || $response_category->getStatusCode() !== 200) {
                            throw new \Exception('Gagal mengambil data produk atau kategori dari API');
                        }
                
                        $products = json_decode($response_product->getBody()->getContents(), true);
                        $categories = json_decode($response_category->getBody()->getContents(), true);
                
                        $productMap = collect($products)->keyBy('product_id');
                        $categoryMap = collect($categories)->keyBy('category_id');
                
                        $data = $stocks->map(function ($stock) use ($productMap, $categoryMap) {
                            $product = $productMap->get($stock->product_id);
                
                            $categoryId = $product['category_id'] ?? null;
                
                            $categoryName = $categoryMap->get($categoryId)['nama_kategori'] ?? 'Kategori Tidak Ditemukan';
                
                            return [
                                'stock_id' => $stock->stock_id,
                                'product_name' => $product['product_name'] ?? 'Produk Tidak Ditemukan',
                                'merchant_name' => $stock->merchant->nama_merchant,
                                'stok' => $stock->jumlah_stok,
                                'sisa_stok' => $stock->sisa_stok,
                                'kategori' => $categoryName,
                                'spesifikasi' => $stock->spesifikasi,
                                'hargamodal' => $stock->hargamodal,
                                'hargajual' => $stock->hargajual,
                                'tanggal_masuk' => $stock->tanggal_masuk,
                                'tanggal_expired' => $stock->tanggal_expired,
                            ];
                        });
                
                        $data = $data->sortByDesc('stock_id')->values()->all();
                
                        return view('admin.warehouse.produk_warehouse', ['data' => $data]);
                    } catch (\Exception $e) {
                        return view('admin.warehouse.produk_warehouse', ['error' => $e->getMessage()]);
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function pembelianproduk()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $client = new Client();
                    $response = $client->get($this->baseApiUrl . '/pembelian');
                    if ($response->getStatusCode() === 200) {
                        $data = json_decode($response->getBody()->getContents(), true);
                        $purchases = $data['purchases'] ?? [];
                    } else {
                        $purchases = [];
                    }
                    return view('admin.warehouse.pembelian_produk', compact('purchases'));
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }
    
    public function tambahproduk()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {      
                    $merchants = Merchants::all();
                    $categories = Category::all();
                    $products = Product::all();
                    return view('admin.warehouse.tambah_produk', compact('merchants', 'categories', 'products'));

                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function addStock(Request $request)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $request->validate([
                        'namaToko' => 'required|exists:merchants,merchant_id',
                        'kategoriProduk' => 'required|exists:categories,category_id',
                        'namaProduk' => 'required|exists:products,product_id',
                        'spesifikasi' => 'required|string|max:255',
                        'lokasi' => 'required|string|max:255',
                        'jumlah' => 'required|integer|min:1',
                        'hargamodal' => 'required|numeric|min:0',
                        'hargajual' => 'required|numeric|min:0',
                        'tanggalExpired' => 'required|date',
                    ]);
            
                    try {
                        $stock = new Stock();
                        $stock->product_id = $request->namaProduk;
                        $stock->merchant_id = $request->namaToko;
                        $stock->jumlah_stok = $request->jumlah;
                        $stock->sisa_stok = $request->jumlah;
                        $stock->spesifikasi = $request->spesifikasi;
                        $stock->hargamodal = $request->hargamodal;
                        $stock->hargajual = $request->hargajual;
                        $stock->tanggal_masuk = now();
                        $stock->tanggal_expired = $request->tanggalExpired;
                        $stock->lokasi = $request->lokasi;
                        $stock->save();
            
                        return redirect()->route('admin.produk.warehouse')->with('success', 'Stok berhasil ditambahkan');
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', 'Gagal menambahkan stok: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function editProduk($id)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
            if ($cek_admin_id) {
                try {
                    try {
                        $stock = Stock::with(['merchant'])->findOrFail($id);
                        $merchants = Merchants::all();
                        $categories = Category::all();
                        $products = Product::all();
                        return view('admin.warehouse.edit_produk', compact('stock','merchants','categories','products'));
                    } catch (\Exception $e) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function updateProduk(Request $request, $id)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $request->validate([
                        'namaToko' => 'required',
                        'kategoriProduk' => 'required',
                        'namaProduk' => 'required',
                        'spesifikasi' => 'required',
                        'lokasi' => 'required',
                        'jumlah' => 'required|numeric',
                        'hargamodal' => 'required|numeric',
                        'hargajual' => 'required|numeric',
                        'tanggalExpired' => 'required|date',
                    ]);
            
                    $stock = Stock::findOrFail($id);
                    $stock->merchant_id = $request->namaToko;
                    $stock->product_id = $request->namaProduk;
                    $stock->spesifikasi = $request->spesifikasi;
                    $stock->lokasi = $request->lokasi;
                    $stock->jumlah_stok = $request->jumlah;
                    $stock->sisa_stok = $request->jumlah;
                    $stock->hargamodal = $request->hargamodal;
                    $stock->hargajual = $request->hargajual;
                    $stock->tanggal_expired = $request->tanggalExpired;
                    $stock->save();
            
                    return redirect()->route('admin.produk.warehouse')->with('success', 'Produk berhasil diperbarui');

                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function hapusProduk($id)
    {
        try {
            $stock = Stock::findOrFail($id);
            $stock->delete();

            return redirect()->back();

        } catch (\Exception $e) {
            return view('admin.warehouse.produk_warehouse')->withErrors($e->getMessage());
        }
    }

    public function transaksiwarehouse() 
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            // Jika pengguna adalah admin
            if ($cek_admin_id) {
                try {
                    
                    $client = new Client();
                    $response = $client->get($this->baseApiUrl . '/listdaftarproduk');
                
                    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                        $products = json_decode($response->getBody()->getContents(), true);
                        $productMap = [];
                
                        foreach ($products as $product) {
                            $productMap[$product['product_id']] = $product['product_name'];
                        }
                
                        $transaksis = Transaksi::select(
                            'transaksi.transaksi_id',
                            'stocks_warehouse.product_id',
                            'merchants.nama_merchant',
                            'transaksi.penanggung_jawab',
                            'transaksi.jumlah_barang_keluar',
                            'transaksi.tanggal_keluar',

                        )
                        ->join('stocks_warehouse', 'transaksi.stock_id', '=', 'stocks_warehouse.stock_id')
                        ->join('merchants', 'stocks_warehouse.merchant_id', '=', 'merchants.merchant_id')
                        ->orderBy('transaksi.tanggal_keluar', 'desc') 
                        ->get();
                
                        foreach ($transaksis as $transaksi) {
                            $transaksi->product_name = $productMap[$transaksi->product_id] ?? 'Unknown Product';
                        }
                        
                        return view('admin.warehouse.transaksi_warehouse', compact('transaksis'));
                
                    } else {
                        return view('admin.warehouse.transaksi_warehouse')->withErrors('Failed to fetch products from external API');
                    }

                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function tambahtransaksi()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $transaksi = Transaksi::all();
                    $products = Stock::with(['merchant'])
                            ->where('sisa_stok', '>', 0)
                            ->get();
                    return view('admin.warehouse.tambah_transaksi', compact('transaksi','products'));

                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function addTransaksi(Request $request)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    try {
                        DB::beginTransaction();
                
                        $stock = Stock::where('stock_id', $request->stock_id)->first();
                        
                        if (!$stock) {
                            return redirect()->back()->withErrors('Stok tidak ditemukan.');
                        }
                
                        if ($stock->sisa_stok < $request->jumlah_barang_keluar) {
                            return redirect()->back()->withErrors('Jumlah stok tidak mencukupi.');
                        }
                
                        $stock->sisa_stok -= $request->jumlah_barang_keluar;
                        $stock->save();
                
                        $transaksi = new Transaksi();
                        $transaksi->stock_id = $request->stock_id;
                        $transaksi->penanggung_jawab = $request->penanggung_jawab;
                        $transaksi->jumlah_barang_keluar = $request->jumlah_barang_keluar;
                        $transaksi->tanggal_keluar = $request->tanggal_keluar;
                        $transaksi->save();
                
                        DB::commit();
                        return redirect()->route('admin.transaksiwarehouse.warehouse')->with('success', 'Data berhasil ditambahkan.');
                        
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return redirect()->back()->withErrors($e->getMessage());
                    }

                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function deleteTransaksi($id)
    {   
        try {

            $transaksi = Transaksi::findOrFail($id);
            $transaksi->delete();
            return redirect()->route('admin.transaksiwarehouse.warehouse');

        } catch (\Exception $e) {
            return view('admin.warehouse.index')->withErrors($e->getMessage());
        }
    }

    public function editTransaksi($id)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    try {
                        $transaksi = Transaksi::select(
                            'transaksi.transaksi_id',
                            'transaksi.stock_id',
                            'products.product_name',
                            'products.product_id',
                            'merchants.nama_merchant',
                            'transaksi.penanggung_jawab',
                            'transaksi.jumlah_barang_keluar',
                            DB::raw("DATE_FORMAT(transaksi.tanggal_keluar, '%Y-%m-%d') as tanggal_keluar")
                        )
                        ->join('stocks_warehouse', 'transaksi.stock_id', '=', 'stocks_warehouse.stock_id')
                        ->join('products', 'stocks_warehouse.product_id', '=', 'products.product_id')
                        ->join('merchants', 'stocks_warehouse.merchant_id', '=', 'merchants.merchant_id')
                        ->where('transaksi.transaksi_id', $id)
                        ->first();
            
                        if (!$transaksi) {
                            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
                        }
            
                        $products = Stock::with('product')->get();
            
                        return view('admin.warehouse.edit_transaksi', compact('transaksi', 'products'));
                        
                    } catch (\Exception $e) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function updateTransaksi(Request $request, $id)
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $request->validate([
                        'stock_id' => 'required|exists:stocks_warehouse,stock_id',
                        'penanggung_jawab' => 'required|string|max:255',
                        'jumlah_barang_keluar' => 'required|integer|min:1',
                        'tanggal_keluar' => 'required|date',
                    ]);
            
                    try {
                        $transaksi = Transaksi::findOrFail($id);
                        $transaksi->stock_id = $request->input('stock_id');
                        $transaksi->penanggung_jawab = $request->input('penanggung_jawab');
                        $transaksi->jumlah_barang_keluar = $request->input('jumlah_barang_keluar');
                        $transaksi->tanggal_keluar = $request->input('tanggal_keluar');
                        $transaksi->save();
            
                        return redirect()->route('admin.transaksiwarehouse.warehouse')->with('success', 'Transaksi berhasil diupdate.');
            
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate transaksi: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function laporanpemesanan()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $client = new Client();
                    $response = $client->get($this->baseApiUrl . '/pembelian');
                    if ($response->getStatusCode() === 200) {
                        $data = json_decode($response->getBody()->getContents(), true);
                        $purchases = $data['purchases'] ?? [];
                    } else {
                        $purchases = [];
                    }
                    return view('admin.warehouse.laporan_pemesanan', compact('purchases'));
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }            
    }

    public function LaporanStok()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    try {
                        $client = new Client();
                        $response_product = $client->get($this->baseApiUrl . '/listdaftarproduk');
            
                        if ($response_product->getStatusCode() !== 200) {
                            throw new \Exception('Gagal mengambil data produk dari API');
                        }
                        $products = json_decode($response_product->getBody()->getContents(), true);
                
                        $response_category = $client->get($this->baseApiUrl . '/pilihkategori');
                        if ($response_category->getStatusCode() !== 200) {
                            throw new \Exception('Gagal mengambil data kategori dari API');
                        }
                        $categories = json_decode($response_category->getBody()->getContents(), true);
                
                        $categoryMap = collect($categories)->keyBy('category_id');
                
                        $stocks = Stock::with(['merchant', 'transaksi'])->get();
                
                        $data = $stocks->map(function ($stock) use ($products, $categoryMap) {
                            $product = collect($products)->firstWhere('product_id', $stock->product_id);
                
                            $category = $product ? $categoryMap->get($product['category_id']) : null;
                
                            $totalBarangKeluar = $stock->transaksi->sum('jumlah_barang_keluar');
                
                            $stokTersisa = max($stock->jumlah_stok - $totalBarangKeluar, 0);
                
                            $transaksiTerakhir = $stock->transaksi->max('created_at');
                
                            $transaksiTerakhir = $transaksiTerakhir ? $transaksiTerakhir->toDateTimeString() : null;
                
                            return [
                                'stock_id' => $stock->stock_id,
                                'product_name' => $product['product_name'] ?? 'Produk Tidak Ditemukan',
                                'merchant_name' => $stock->merchant->nama_merchant,
                                'stok' => $stock->jumlah_stok,
                                'kategori' => $category['nama_kategori'] ?? 'Belum dikategorikan',
                                'hargamodal' => $stock->hargamodal,
                                'hargajual' => $stock->hargajual,
                                'tanggal_expired' => $stock->tanggal_expired,
                                'transaksi_terakhir' => $transaksiTerakhir,
                                'total_barang_keluar' => $totalBarangKeluar,
                                'tanggal_masuk' => $stock->tanggal_masuk,
                                'stok_tersisa' => $stokTersisa,
                            ];
                        });
            
                        $data = $data->sortByDesc('tanggal_masuk')->values()->all();
                        return view('admin.warehouse.laporan_stok', compact('data'));     
            
                    } catch (\Exception $e) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function getNotifications()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $today = Carbon::today();
                    $barangMasuk = Carbon::today()->subDays(1)->toDateString();
                    $barangExp = Carbon::today()->addDays(30)->toDateString();

                    $newProducts = DB::table('stocks_warehouse')
                                    ->join('products', 'stocks_warehouse.product_id', '=', 'products.product_id')
                                    ->join('merchants', 'stocks_warehouse.merchant_id', '=', 'merchants.merchant_id')
                                    ->join('categories', 'products.category_id', '=', 'categories.category_id')
                                    ->select(
                                        'stocks_warehouse.*',
                                        'products.product_name',
                                        'merchants.nama_merchant as merchant_name',
                                        'categories.nama_kategori as kategori_produk'
                                    )
                                    ->where('tanggal_masuk', '>=', $barangMasuk)
                                    ->get();

                    $expiringProducts = DB::table('stocks_warehouse')
                                        ->join('products', 'stocks_warehouse.product_id', '=', 'products.product_id')
                                        ->join('merchants', 'stocks_warehouse.merchant_id', '=', 'merchants.merchant_id')
                                        ->join('categories', 'products.category_id', '=', 'categories.category_id')
                                        ->select(
                                            'stocks_warehouse.*',
                                            'products.product_name',
                                            'merchants.nama_merchant as merchant_name',
                                            'categories.nama_kategori as kategori_produk'
                                        )
                                        ->where('tanggal_expired', '<=', $barangExp)
                                        ->get();
                    return view('admin.warehouse.notifikasi', compact('newProducts', 'expiringProducts'));
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function getNotificationsCount()
    {
        if (Auth::check()) {
            $id = Auth::user()->id;
            $cek_admin_id = DB::table('users')->where('id', $id)->where('is_admin', 1)->first();
    
            if ($cek_admin_id) {
                try {
                    $barangMasuk = Carbon::today()->subDays(1)->toDateString();
                    $barangExp = Carbon::today()->addDays(30)->toDateString();
                
                    $newProductsCount = DB::table('stocks_warehouse')
                        ->where('tanggal_masuk', '>=', $barangMasuk)
                        ->count();
                
                    $expiringProductsCount = DB::table('stocks_warehouse')
                        ->where('tanggal_expired', '<=', $barangExp)
                        ->count();
                
                    $totalCount = $newProductsCount + $expiringProductsCount;
                
                    return response()->json(['count' => $totalCount, 'newProductsCount' => $newProductsCount, 'expiringProductsCount' => $expiringProductsCount]);
                } catch (\Exception $e) {
                    return view('admin.warehouse.index')->withErrors($e->getMessage());
                }
            } else {
                return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } else {
            return redirect('/login');
        }
    }

    public function searchByDate(Request $request)
    {
        $client = new Client();
        $response = $client->get('https://kreatif.tobakab.go.id/api/pembelian');
        $apiData = json_decode($response->getBody()->getContents(), true);
        $purchasesAPI = $apiData['purchases'];
    
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        if ($startDate && $endDate) {
            $purchasesAPI = array_filter($purchasesAPI, function ($purchase) use ($startDate, $endDate) {
                $purchaseDate = strtotime($purchase['created_at']);
                return $purchaseDate >= strtotime($startDate) && $purchaseDate <= strtotime($endDate);
            });
        }
    
        return response()->json([
            "purchasesAPI" => array_values($purchasesAPI),
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

}
