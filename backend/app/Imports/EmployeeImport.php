<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{
    /**
     * Convert the spreadsheet to an array
     * 
     * @param array $array
     * @return array
     */
    public function array(array $array): array
    {
        return $array;
    }
}
