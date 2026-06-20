<?php

namespace App\Http\Livewire\Users;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración')]
class SettingsPage extends Component
{
    public string $companyName = '';
    public string $currencySymbol = '';
    public string $currencyCode = '';
    public string $timezone = '';
    public string $dateFormat = '';
    public int $defaultWarehouseId = 0;

    public function mount(): void
    {
        $this->companyName     = config('erp.company_name', '');
        $this->currencySymbol  = config('erp.currency_symbol', '$');
        $this->currencyCode    = config('erp.currency_code', 'ARS');
        $this->timezone        = config('app.timezone', 'America/Argentina/Buenos_Aires');
        $this->dateFormat      = config('erp.date_format', 'd/m/Y');
    }

    public function render()
    {
        return view('livewire.users.settings-page', [
            'timezones' => [
                'America/Lima' => 'Lima (PEN, UTC-5)',
                'America/Bogota' => 'Bogotá (COP, UTC-5)',
                'America/Santiago' => 'Santiago (CLP, UTC-3/-4)',
                'America/Buenos_Aires' => 'Buenos Aires (ARS, UTC-3)',
                'America/Mexico_City' => 'Ciudad de México (MXN, UTC-6)',
                'Europe/Madrid' => 'Madrid (EUR, UTC+1/+2)',
            ],
        ]);
    }
}
