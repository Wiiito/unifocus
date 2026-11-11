<x-guest-layout>
    <div class="flex flex-col items-center gap-md text-center">
        <div>
            <h1 class="text-2xl font-bold text-primary">
                {{ __('Sua rotina de estudos em um só lugar') }}
            </h1>

            <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                {{ __('Acompanhe notas, prazos e revisões diárias sem perder o foco.') }}
            </p>
        </div>

        @auth
            <x-ui.button :href="route('dashboard')" class="w-full">
                <x-ui.icon name="dashboard" size="text-xl" />
                {{ __('Ir para o dashboard') }}
            </x-ui.button>
        @else
            <div class="flex w-full flex-col gap-sm">
                <x-ui.button :href="route('login')" class="w-full">
                    {{ __('Entrar') }}
                </x-ui.button>

                <x-ui.button variant="secondary" :href="route('register')" class="w-full">
                    {{ __('Criar conta') }}
                </x-ui.button>
            </div>
        @endauth
    </div>
</x-guest-layout>
