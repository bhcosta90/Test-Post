<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Providers;

use Illuminate\Support\ServiceProvider;

final class ControllerGraphQL extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', // caminho do config padrão do pacote
            'quantum-controller-graphql' // nome da chave de configuração
        );
    }

    public function boot(): void
    {
    }
}
