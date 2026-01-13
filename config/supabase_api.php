<?php
// config/supabase_api.php
// Simple Supabase REST API wrapper

class SupabaseAPI {
    private $baseURL;
    private $apiKey;
    
    public function __construct() {
        $this->baseURL = 'https://eydancfwhwwdygpeivol.supabase.co';
        $this->apiKey = $_ENV['SUPABASE_ANON_KEY'] ?? '';
    }
    
    public function query($sql, $params = []) {
        $url = $this->baseURL . '/rest/v1/rpc';
        
        $data = [
            'sql' => $sql,
            'params' => $params
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'apikey: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            throw new Exception('Supabase API error: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        return $result;
    }
    
    public function insert($table, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values));
        
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES ($placeholders)";
        
        return $this->query($sql, $values);
    }
    
    public function select($sql, $params = []) {
        return $this->query($sql, $params);
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        $allParams = array_values($data);
        
        foreach ($data as $column => $value) {
            $setClause[] = "$column = ?";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE $where";
        
        return $this->query($sql, array_merge($allParams, $whereParams));
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params);
    }
}

// Global instance
$supabase = new SupabaseAPI();
