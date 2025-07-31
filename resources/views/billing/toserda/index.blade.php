@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing Toserda</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('billing.toserda') }}">Billing Belum Lunas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('billing.toserda.processed') }}">Billing Processed</a>
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
                                    <th>Tanggal Billing</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->no_ktp }}</td>
                                        <td>{{ $row->nama_anggota }}</td>
                                        <td>Rp {{ number_format($row->total_tagihan, 2, ',', '.') }}</td>
                                        <td>{{ date('d/m/Y', strtotime($row->tanggal_billing)) }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ $row->status_bayar }}</span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('billing.toserda.proses', $row->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin memproses billing ini?')">
                                                    <i class="fas fa-check"></i> Proses
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data billing yang belum lunas</td>
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