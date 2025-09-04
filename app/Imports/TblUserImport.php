<?php

namespace App\Imports;

use App\Models\TblUser;
use App\Models\Cabang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TblUserImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            // Skip if username is empty
            if (empty($row['username'])) {
                continue;
            }

            // Check if user already exists
            $existingUser = TblUser::where('u_name', $row['username'])->first();
            
            if ($existingUser) {
                // Update existing user
                $existingUser->update([
                    'level' => $row['level'] ?? $existingUser->level,
                    'id_cabang' => $this->getCabangId($row['cabang']) ?? $existingUser->id_cabang,
                    'aktif' => $row['status_aktif'] ?? $existingUser->aktif,
                ]);
            } else {
                // Create new user
                TblUser::create([
                    'u_name' => $row['username'],
                    'pass_word' => Hash::make($row['password'] ?? 'password123'),
                    'level' => $row['level'] ?? 'admin',
                    'id_cabang' => $this->getCabangId($row['cabang']),
                    'aktif' => $row['status_aktif'] ?? 'Y',
                ]);
            }
        }
    }

    /**
     * Get cabang ID by name
     */
    private function getCabangId($cabangName)
    {
        if (empty($cabangName)) {
            return null;
        }

        $cabang = Cabang::where('nama', 'like', '%' . $cabangName . '%')->first();
        return $cabang ? $cabang->id_cabang : null;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.username' => 'required|string|max:255',
            '*.level' => 'nullable|string|in:admin,pinjaman,simpanan,kas,laporan,supervisor,manager',
            '*.cabang' => 'nullable|string',
            '*.status_aktif' => 'nullable|string|in:Y,N',
            '*.password' => 'nullable|string|min:6',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '*.username.required' => 'Username harus diisi',
            '*.username.string' => 'Username harus berupa teks',
            '*.username.max' => 'Username maksimal 255 karakter',
            '*.level.in' => 'Level harus salah satu dari: admin, pinjaman, simpanan, kas, laporan, supervisor, manager',
            '*.status_aktif.in' => 'Status Aktif harus Y atau N',
            '*.password.min' => 'Password minimal 6 karakter',
        ];
    }
}
