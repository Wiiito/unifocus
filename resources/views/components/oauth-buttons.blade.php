{{--
    Botões de login social.
    A lista vem de config/oauth.php e só exibe provedores que já têm
    credenciais em config/services.php, evitando botões quebrados.
--}}
@if (filled($providers))
    <div class="relative mt-6">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-outline-variant"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-card px-2 text-on-surface-variant">{{ __('ou continue com') }}</span>
        </div>
    </div>

    <div class="mt-6 space-y-3">
        @foreach ($providers as $name => $provider)
            <a href="{{ route('oauth.redirect', $name) }}"
               class="inline-flex w-full items-center justify-center gap-3 rounded-full border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface shadow-sm transition duration-fast hover:bg-surface-container-low active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background">
                @switch ($name)
                    @case ('google')
                        <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        @break

                    @case ('github')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="#181717" aria-hidden="true">
                            <path d="M12 .3a12 12 0 00-3.8 23.4c.6.1.8-.3.8-.6v-2.2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.8.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2 0-.4-.5-1.6.2-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 016 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.6.2 2.8.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0012 .3z"/>
                        </svg>
                        @break

                    @default
                        <svg class="h-5 w-5 text-on-surface-variant" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.03 5.91l-2.47 2.47a2.25 2.25 0 01-1.59.66H9v1.5a.75.75 0 01-.75.75H6.75v1.5a.75.75 0 01-.75.75H3.75a.75.75 0 01-.75-.75v-2.44c0-.6.24-1.17.66-1.59l6.18-6.18A6 6 0 1121.75 8.25z"/>
                        </svg>
                @endswitch

                {{ $provider['label'] }}
            </a>
        @endforeach
    </div>
@endif
