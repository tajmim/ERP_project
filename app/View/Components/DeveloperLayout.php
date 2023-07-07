<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DeveloperLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('developer.layouts.app');
    }
}
