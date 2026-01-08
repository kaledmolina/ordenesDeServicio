<?php

namespace App\Filament\Resources\ClienteResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ImportProgress extends Widget
{
    protected static string $view = 'filament.resources.cliente-resource.widgets.import-progress';
    protected int|string|array $columnSpan = 'full';

    public $progress = 0;
    public $total = 0;
    public $status = 'idle'; // idle, running, finished

    public function mount()
    {
        $this->updateProgress();
    }

    public function updateProgress()
    {
        $userId = auth()->id();
        $key = 'import_progress_' . $userId;
        $data = Cache::get($key);

        if ($data) {
            $this->total = $data['total'];
            $processed = $data['processed'];
            $this->status = $data['status'];

            if ($this->total > 0) {
                $this->progress = round(($processed / $this->total) * 100);
            } else {
                $this->progress = 0;
            }
        } else {
            $this->status = 'idle';
            $this->progress = 0;
        }
    }
}
