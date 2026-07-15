<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SortLink extends Component
{
    public $column;
    public $label;

    public function __construct($column, $label)
    {
        $this->column = $column;
        $this->label = $label;
    }

    public function render()
    {
        $direction = request('sort_order') === 'asc' ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort_by' => $this->column, 'sort_order' => $direction]);

        // Menentukan ikon
        $icon = 'fa-sort';
        if (request('sort_by') === $this->column) {
            $icon = request('sort_order') === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        }

        return view('components.sort-link', compact('url', 'icon'));
    }
}
