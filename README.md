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

```bash
git clone git@github.com:flaviu-chelaru/database-mcp.git
cd database-mcp
composer run setup
```

The `setup` script installs dependencies, copies `.env.example`, generates an app key, runs migrations, and builds frontend assets.

## Configuration

Add your database connections to `.env` using the `DB_CONNECTIONS` variable. It accepts a JSON object mapping connection names to DSN strings:

```env
DB_CONNECTIONS='{"my_app":"mysql://user:pass@localhost:3306/my_app","analytics":"pgsql://user:pass@localhost:5432/analytics"}'
```

Supported DSN schemes: `mysql`, `mariadb`, `pgsql`, `sqlite`, `sqlsrv`.

## Running the MCP Server

```bash
# Start the MCP inspector
composer mcp
```

This runs `php artisan mcp:inspector database` with authentication disabled for local use.

### Connecting from Claude Code

Add the server to your `.mcp.json`:

```json
{
  "mcpServers": {
    "database": {
      "command": "php",
      "args": ["artisan", "mcp:inspector", "database"],
      "cwd": "/path/to/database-mcp"
    }
  }
}
```

## Development

```bash
# Start dev server with live reload, queue worker, log viewer, and Vite
composer run dev

# Run tests
composer run test

# Format code
vendor/bin/pint
```

## Requirements

- PHP 8.4+
- Composer
- Node.js & npm
- One or more supported database servers

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
