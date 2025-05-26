<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ClientsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Client::select('full_name', 'email', 'gender', 'device', 'language', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Email',
            'Gender',
            'Device',
            'Language',
            'Registered At'
        ];
    }
}