<?php

use App\Mcp\Servers\Database;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('database', Database::class);

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
