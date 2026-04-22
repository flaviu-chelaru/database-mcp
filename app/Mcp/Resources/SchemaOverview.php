<?php

namespace App\Mcp\Resources;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Schema overview for a given connection and database: tables, columns, indexes, foreign keys, and views.')]
#[MimeType('application/json')]
class SchemaOverview extends Resource implements HasUriTemplate
{
    public function __construct(private readonly DatabaseManager $dbManager)
    {
    }

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('schema://db/{connection}/{database}');
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $connectionName = $request->get('connection');

        $allowed = config('database.mcp_connections');
        if (! in_array($connectionName, $allowed, true)) {
            return Response::error("Connection '{$connectionName}' is not allowed.");
        }

        $schema = $this->dbManager->connection($connectionName)->getSchemaBuilder();

        $tables = [];
        foreach ($schema->getTables() as $table) {
            $name = $table['name'];

            $tables = [...$tables, $name => [
                'size' => $this->formatBytes($table['size']),
                'engine' => $table['engine'],
                'collation' => $table['collation'],
                'comment' => $table['comment'],
                'columns' => $this->columns($schema, $name),
                'indexes' => $this->indexes($schema, $name),
                'foreign_keys' => $this->foreignKeys($schema, $name),
            ]];
        }

        $views = [];
        foreach ($schema->getViews() as $view) {
            $views = [...$views, $view['name'] => [
                'definition' => $view['definition'],
            ]];
        }

        return Response::structured(array_filter([
            'tables' => $tables,
            'views' => $views,
        ]));
    }

    private function columns(SchemaBuilder $schema, string $table): array
    {
        $results = [];

        foreach ($schema->getColumns($table) as $col) {
            $results = [...$results, $col['name'] => [
                'type' => $col['type'],
                'nullable' => $col['nullable'],
                'default' => $col['default'],
                'auto_increment' => $col['auto_increment'],
            ]];
        }

        return $results;
    }

    private function indexes(SchemaBuilder $schema, string $table): array
    {
        $results = [];

        foreach ($schema->getIndexes($table) as $idx) {
            $results = [...$results, $idx['name'] => [
                'columns' => $idx['columns'],
                'type' => $idx['type'],
                'unique' => $idx['unique'],
                'primary' => $idx['primary'],
            ]];
        }

        return $results;
    }

    private function foreignKeys(SchemaBuilder $schema, string $table): array
    {
        $results = [];

        foreach ($schema->getForeignKeys($table) as $fk) {
            $results = [...$results, $fk['name'] => [
                'columns' => $fk['columns'],
                'foreign_table' => $fk['foreign_table'],
                'foreign_columns' => $fk['foreign_columns'],
                'on_update' => $fk['on_update'],
                'on_delete' => $fk['on_delete'],
            ]];
        }

        return $results;
    }

    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes === 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
