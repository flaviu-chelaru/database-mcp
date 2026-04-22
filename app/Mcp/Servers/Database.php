<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\SchemaOverview;
use App\Mcp\Tools\ListConnections;
use App\Mcp\Tools\ListDatabases;
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
        ListConnections::class,
        ListDatabases::class,
    ];

    protected array $resources = [
        SchemaOverview::class,
    ];

    protected array $prompts = [
        //
    ];
}
