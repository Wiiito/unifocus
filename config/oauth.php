<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Padrões de autenticação
    |--------------------------------------------------------------------------
    |
    | Valores usados quando um provedor não define os seus. O "model" é o
    | destino da autenticação: qualquer model autenticável que use a trait
    | App\Models\Concerns\HasOauthIdentities serve — não precisa ser o User.
    |
    */

    'defaults' => [
        'model' => User::class,
        'guard' => 'web',
        'redirect' => '/dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Provedores habilitados
    |--------------------------------------------------------------------------
    |
    | A chave precisa ser exatamente o nome do driver do Socialite e ter uma
    | entrada correspondente em config/services.php. Para adicionar um novo
    | login basta acrescentar a chave aqui e as credenciais em services.php —
    | nenhuma rota, controller ou migration precisa ser alterada.
    |
    | Cada provedor aceita, opcionalmente: label, scopes, with, model, guard
    | e redirect (os quatro últimos sobrescrevem os "defaults" acima).
    |
    */

    'providers' => [

        'google' => [
            'label' => 'Google',
            'with' => ['prompt' => 'select_account'],
        ],

        // Exemplos — descomente e adicione as credenciais em config/services.php:
        //
        // 'github' => [
        //     'label' => 'GitHub',
        //     'scopes' => ['read:user', 'user:email'],
        // ],
        //
        // 'gitlab' => ['label' => 'GitLab'],
        // 'bitbucket' => ['label' => 'Bitbucket'],
        // 'linkedin-openid' => ['label' => 'LinkedIn'],
        //
        // Um provedor pode autenticar em outra entidade que não o User:
        //
        // 'slack' => [
        //     'label' => 'Slack',
        //     'model' => App\Models\Operador::class,
        //     'guard' => 'operadores',
        //     'redirect' => '/operadores/painel',
        // ],

    ],

];
