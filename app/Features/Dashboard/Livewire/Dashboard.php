<?php

namespace App\Features\Dashboard\Livewire;

use App\Features\Stock\Enums\StockStatus;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stocks = auth()->user()->stocks();

        return view('livewire.dashboard.dashboard', [
            'total' => (clone $stocks)->count(),
            'available' => (clone $stocks)->where('status', StockStatus::AVAILABLE)->count(),
            'low' => (clone $stocks)->where('status', StockStatus::LOW_STOCK)->count(),
            'out' => (clone $stocks)->where('status', StockStatus::OUT_OF_STOCK)->count(),
        ])->layout('components.layouts.app');
    }
}
