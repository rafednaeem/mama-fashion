<?php
// config/database.php
// Updated for Supabase REST API

require_once __DIR__ . '/supabase_api.php';

function getPDO() {
    // Return Supabase API instance (PDO-compatible interface)
    global $supabase;
    return $supabase;
}
