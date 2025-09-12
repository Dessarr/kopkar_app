<?php

namespace App\Exports;

use App\Models\TblUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblUserExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;
    protected $status;
    protected $level;
    protected $cabang;

    public function __construct($search = null, $status = null, $level = null, $cabang = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->level = $level;
        $this->cabang = $cabang;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = TblUser::with('cabang');

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->status) {
            $query->where('aktif', $this->status);
        }

        if ($this->level) {
            $query->where('level', $this->level);
        }

        if ($this->cabang) {
            $query->where('id_cabang', $this->cabang);
        }

        return $query->ordered()->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Username',
            'Level',
            'Cabang',
            'Status Aktif',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }

    /**
     * @param TblUser $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->u_name,
            $user->level_text,
            $user->cabang ? $user->cabang->nama : '-',
            $user->status_aktif_text,
            $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
            $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 20,  // Username
            'C' => 20,  // Level
            'D' => 25,  // Cabang
            'E' => 15,  // Status Aktif
            'F' => 20,  // Tanggal Dibuat
            'G' => 20,  // Tanggal Diupdate
        ];
    }
}
