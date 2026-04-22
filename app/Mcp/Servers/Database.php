<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('database')]
#[Version('0.0.1')]
#[Instructions('Enables read-only operations against databases')]
class Database extends Server
{
    protected array $tools = [
        \App\Mcp\Tools\ListConnections::class,
        \App\Mcp\Tools\ListDatabases::class,
    ];

    protected array $resources = [
        \App\Mcp\Resources\SchemaOverview::class,
    ];

    protected array $prompts = [
        //
    ];
}
