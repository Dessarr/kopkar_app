<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class BillingUploadImport implements ToArray
{
    /**
     * Import Excel data to array without heading row
     */
    public function array(array $array)
    {
        return $array;
    }
}
