<?php

namespace App\View\Components;

use App\Models\OauthIdentity;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OauthButtons extends Component
{
    /**
     * Provedores prontos para uso (declarados e com credenciais preenchidas).
     *
     * @var array<string, array<string, mixed>>
     */
    public array $providers;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->providers = OauthIdentity::configuredProviders();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.oauth-buttons');
    }
}
