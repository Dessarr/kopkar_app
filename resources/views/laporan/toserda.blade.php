@extends('layouts.app')

@section('title', 'Laporan Toserda')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 text-center">
    <h2 class="text-xl font-bold mb-4">Laporan Toserda</h2>
    <p class="mb-6 text-gray-600">{{ $message ?? 'Laporan Toserda belum tersedia.' }}</p>
    <div class="flex justify-center gap-2">
        <button class="px-4 py-2 bg-red-400 text-white rounded-md opacity-50 cursor-not-allowed" disabled>Export PDF</button>
        <button class="px-4 py-2 bg-green-400 text-white rounded-md opacity-50 cursor-not-allowed" disabled>Export Excel</button>
    </div>
</div>
@endsection 