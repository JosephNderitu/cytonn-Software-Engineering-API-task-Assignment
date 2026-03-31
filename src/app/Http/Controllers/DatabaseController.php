<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    // ─── GET /api/db/tables ───────────────────────────────────────────────────
    // Returns all user-created table names in the public schema
    public function tables(): JsonResponse
    {
        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
              AND table_type = 'BASE TABLE'
            ORDER BY table_name
        ");

        return response()->json([
            'success' => true,
            'tables'  => array_column($tables, 'table_name'),
        ]);
    }

    // ─── GET /api/db/tables/{table} ───────────────────────────────────────────
    // Returns columns + up to 100 rows from the given table
    public function show(string $table): JsonResponse
    {
        // Whitelist — only allow alphanumeric + underscore table names
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            return response()->json(['success' => false, 'message' => 'Invalid table name.'], 422);
        }

        // Check table actually exists
        $exists = DB::select("
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = ?
        ", [$table]);

        if (empty($exists)) {
            return response()->json(['success' => false, 'message' => 'Table not found.'], 404);
        }

        // Get column definitions
        $columns = DB::select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = ?
            ORDER BY ordinal_position
        ", [$table]);

        // Get row count
        $countResult = DB::select("SELECT COUNT(*) as total FROM \"{$table}\"");
        $total = $countResult[0]->total ?? 0;

        // Get rows (limit 100 for safety)
        $rows = DB::select("SELECT * FROM \"{$table}\" LIMIT 100");

        // Convert stdClass objects to arrays
        $rows = array_map(fn($row) => (array) $row, $rows);

        return response()->json([
            'success' => true,
            'table'   => $table,
            'total'   => (int) $total,
            'columns' => $columns,
            'rows'    => $rows,
        ]);
    }
}