<?php

namespace App\Mcp\Tools;

use Illuminate\Database\DatabaseManager;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists available databases for each configured connection.')]
#[IsReadOnly]
class ListDatabases extends Tool
{
    public function __construct(private DatabaseManager $dbManager)
    {
    }

    public function handle(Request $request): Response
    {
        /** @var array<string, array{driver: string, databases: list<string>, error?: string}> $results */
        $results = [];

        foreach (config('database.mcp_connections') as $name) {
            $connection = $this->dbManager->connection($name);
            $connectionName = $connection->getName();

            try {
                $schemas = $connection->getSchemaBuilder()->getSchemas();

                $results = [...$results, $connectionName => [
                    'driver' => $connection->getDriverName(),
                    'databases' => array_column($schemas, 'name'),
                ]];
            } catch (\Throwable $e) {
                $results = [...$results, $connectionName => [
                    'driver' => $connection->getDriverName(),
                    'databases' => [],
                    'error' => $e->getMessage(),
                ]];
            }
        }

        return Response::json($results);
    }
}
