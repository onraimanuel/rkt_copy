@extends('user/toko/layout/main')

@section('title', 'Rumah Kreatif Toba - Warehouse')

@section('css')
    <link rel="stylesheet" href="{{ asset('asset/css/warehouse.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Warehouse</li>
@endsection

@section('container')
    <div class="col-12 warehouse">
        <div class="d-flex justify-content-between align-items-center">
            <p class="title-warehouse">Warehouse</p>
            <a class="btn btn-success" href="{{ route('warehouse.create') }}">Request Penyimpanan</a>
        </div>
        @if (session('status'))
            <div class="alert alert-success my-3">
                {{ session('status') }}
            </div>
        @endif
        <div class="col-md-12">
            <div class="row mt-2">
                <div class="col-md-3 p-4 border">
                    <p class="title-card-warehouse">Kategori Barang</p>
                    <p class="fw-bold">{{ count($categories_warehouse) }}</p>
                </div>
                <div class="col-md-3 p-4 border ml-5">
                    <p class="title-card-warehouse">Total Barang</p>
                    <p class="fw-bold">{{ $total }}</p>
                </div>
                <div class="col-md-3 p-4 border ml-5">
                    <p class="title-card-warehouse">Total Barang yang sudah terjual</p>
                    <p class="fw-bold">{{ $galleries_sold->sum('sold_out') }} </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12 p-4 border rounded-lg">
                    <p class="fw-bold">Daftar Produk</p>
                    <form action="{{ route('warehouse.index') }}" method="get">
                        <input type="text" class="input-search mt-2" placeholder="Cari Produk" name="query">
                        <button type="submit" class="d-none"></button>
                    </form>
                    <table class="table mt-2 table-responsive table-warehouse">
                        <thead class="">
                            <tr>
                                <th style="width: 50px" class="text-center">No</th>
                                <th style="width: 100px">Nama</th>
                                <th style="width: 100px">Harga</th>
                                <th style="width: 100px">Jumlah</th>
                                <th style="width: 100px">Kategori Barang</th>
                                <th style="width: 100px">Berat Barang</th>
                                <th style="width: 100px">Expired Date</th>
                                <th style="width: 100px">Alasan Penolakan</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($warehouses as $item)
                                @if ($item->is_accepted == 2)
                                    <tr class="bg-danger text-white text-center">
                                        <td>{{ (request()->input('page', 1) - 1) * 10 + $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('warehouse.show', $item->id) }}">
                                                {{ $item->product_name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->price }}</td>

                                        <td>{{ $item->stok }}</td>
                                        <td>{{ $item->category->nama_kategori }}</td>
                                        <td>{{ $item->heavy }}</td>
                                        <td>{{ $item->expired_at }}</td>
                                        @if ($item->is_accepted == 2)
                                            <td>{{ $item->alasan_ditolak }}</td>
                                        @elseif($item->is_accepted == 0)
                                            <td>Menunggu</td>
                                        @else
                                            <td>Diterima</td>
                                        @endif

                                    </tr>
                                @elseif ($item->is_accepted == 0)
                                    <tr class="bg-warning  text-center">
                                        <td>{{ (request()->input('page', 1) - 1) * 10 + $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('warehouse.show', $item->id) }}">
                                                {{ $item->product_name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->price }}</td>

                                        <td>{{ $item->stok }}</td>
                                        <td>{{ $item->category->nama_kategori }}</td>
                                        <td>{{ $item->heavy }}</td>
                                        <td>{{ $item->expired_at }}</td>
                                        @if ($item->is_accepted == 2)
                                            <td>{{ $item->alasan_ditolak }}</td>
                                        @elseif($item->is_accepted == 0)
                                            <td>Menunggu</td>
                                        @else
                                            <td>Diterima</td>
                                        @endif

                                    </tr>
                                @else
                                    <tr class="text-center">
                                        <td>{{ (request()->input('page', 1) - 1) * 10 + $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('warehouse.show', $item->id) }}">
                                                {{ $item->product_name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->price }}</td>

                                        <td>{{ $item->stok }}</td>
                                        <td>{{ $item->category->nama_kategori }}</td>
                                        <td>{{ $item->heavy }}</td>
                                        <td>{{ $item->expired_at }}</td>
                                        @if ($item->is_accepted == 2)
                                            <td>{{ $item->alasan_ditolak }}</td>
                                        @elseif($item->is_accepted == 0)
                                            <td>Menunggu</td>
                                        @else
                                            <td>Diterima</td>
                                        @endif

                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    {{ $warehouses->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
