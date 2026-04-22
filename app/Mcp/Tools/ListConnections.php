<?php

namespace App\Mcp\Tools;

use Illuminate\Database\DatabaseManager;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists all available database connections and their drivers.')]
#[IsReadOnly]
class ListConnections extends Tool
{
    public function __construct(private DatabaseManager $dbManager)
    {
    }

    public function handle(Request $request): Response
    {
        /** @var array<string, array{driver: string}> $results */
        $results = [];

        foreach (config('database.mcp_connections') as $name) {
            $connection = $this->dbManager->connection($name);

            $results = [...$results, $connection->getName() => [
                'driver' => $connection->getDriverName(),
            ]];
        }

        return Response::json($results);
    }
}
