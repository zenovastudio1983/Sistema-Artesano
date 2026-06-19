<?php

namespace App\Http\Livewire\Reports;

use App\Domains\Reports\Services\ReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reportes')]
class ReportsDashboard extends Component
{
    public function render(ReportService $service)
    {
        return view('livewire.reports.reports-dashboard', [
            'kpis' => $service->getDashboardKpis(),
        ]);
    }
}
