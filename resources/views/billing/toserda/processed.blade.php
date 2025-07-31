@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing Toserda Processed</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('billing.toserda') }}">Billing Belum Lunas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('billing.toserda.processed') }}">Billing Processed</a>
                        </li>
                    </ul>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No KTP</th>
                                    <th>Nama Anggota</th>
                                    <th>Total Tagihan</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->no_ktp }}</td>
                                        <td>{{ $row->nama_anggota }}</td>
                                        <td>Rp {{ number_format($row->total_tagihan, 2, ',', '.') }}</td>
                                        <td>{{ date('d/m/Y', strtotime($row->tanggal_bayar)) }}</td>
                                        <td>
                                            <span class="badge badge-success">Lunas</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data billing yang sudah diproses</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection