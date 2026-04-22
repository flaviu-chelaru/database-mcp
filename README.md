# Database MCP

An MCP (Model Context Protocol) server that gives AI coding assistants read-only access to your database schemas. Connect multiple databases and let tools like Claude Code, Cursor, or GitHub Copilot understand your table structures, columns, indexes, foreign keys, and views -- without any risk of data modification.

## Features

- **Multi-database support** -- connect to MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server simultaneously
- **Read-only by design** -- all operations are strictly read-only; no data modification is possible
- **Full schema introspection** -- tables, columns, indexes, foreign keys, and views with metadata (engine, collation, size, comments)
- **DSN-based configuration** -- define connections as simple DSN strings in a single environment variable
- **Graceful error handling** -- connection failures are reported without crashing the server

## MCP Interface

### Tools

| Tool | Description |
|------|-------------|
| `ListConnections` | Returns all configured database connections and their drivers |
| `ListDatabases` | Lists available databases for each configured connection |

### Resources

| URI | Description |
|-----|-------------|
| `schema://db/{connection}/{database}` | Full schema overview for a database -- tables, columns, indexes, foreign keys, and views |

## Quick Start

### Using Docker

```bash
docker run --rm \
  -e DB_CONNECTIONS='{"mydb":"mysql://user:pass@host.docker.internal:3306/mydb"}' \
  ghcr.io/flaviu-chelaru/database-mcp
```

### From source

```bash
git clone git@github.com:flaviu-chelaru/database-mcp.git
cd database-mcp
composer run setup
```

The `setup` script installs dependencies, copies `.env.example`, and generates an app key.

## Configuration

Set the `DB_CONNECTIONS` environment variable with a JSON object mapping connection names to DSN strings:

```env
DB_CONNECTIONS='{"my_app":"mysql://user:pass@localhost:3306/my_app","analytics":"pgsql://user:pass@localhost:5432/analytics"}'
```

Supported DSN schemes: `mysql`, `mariadb`, `pgsql`, `sqlite`, `sqlsrv`.

## Running the MCP Server

```bash
# Start the stdio MCP server
php artisan mcp:start database

# Start the inspector web UI (requires Node.js)
composer mcp
```

## Docker

### Connecting from Claude Code

Add the server to your `.mcp.json`:

```json
{
  "mcpServers": {
    "database": {
      "command": "docker",
      "args": [
        "run", "--rm", "-i",
        "-e", "DB_CONNECTIONS={\"mydb\":\"mysql://user:pass@host.docker.internal:3306/mydb\"}",
        "ghcr.io/flaviu-chelaru/database-mcp"
      ]
    }
  }
}
```

### Connecting to a database on the host machine

Use `host.docker.internal` to reach databases running on your host:

```bash
docker run --rm -i \
  -e DB_CONNECTIONS='{"app":"mysql://root:secret@host.docker.internal:3306/app_db"}' \
  ghcr.io/flaviu-chelaru/database-mcp
```

### Connecting to a database in another Docker container

Use a shared Docker network:

```bash
# Create a network (or use an existing one)
docker network create mynet

# Run the MCP server on the same network
docker run --rm -i --network mynet \
  -e DB_CONNECTIONS='{"app":"mysql://root:secret@mysql-container:3306/app_db"}' \
  ghcr.io/flaviu-chelaru/database-mcp
```

### Multiple databases at once

```bash
docker run --rm -i \
  -e DB_CONNECTIONS='{"main":"mysql://root:pass@host.docker.internal:3306/main","analytics":"pgsql://user:pass@host.docker.internal:5432/analytics","legacy":"sqlsrv://sa:pass@host.docker.internal:1433/legacy"}' \
  ghcr.io/flaviu-chelaru/database-mcp
```

### Running the MCP Inspector (development)

The inspector provides a web UI for testing MCP tools and resources:

```bash
docker compose up inspector
```

Open `http://localhost:6274` in your browser.

### Overriding the command

The entrypoint is `php artisan`, so you can run any artisan command:

```bash
# List available artisan commands
docker run --rm ghcr.io/flaviu-chelaru/database-mcp list

# Run tinker
docker run --rm -it ghcr.io/flaviu-chelaru/database-mcp tinker
```

## Development

```bash
# Run tests
composer run test

# Format code (PSR-12)
vendor/bin/pint
```

## Requirements

- PHP 8.4+
- Composer
- One or more supported database servers

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
