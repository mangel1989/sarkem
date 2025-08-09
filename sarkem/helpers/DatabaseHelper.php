<?php
class DatabaseHelper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Execute a prepared statement with parameters
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @param string $types Parameter types (s, i, d, b)
     * @return mysqli_result|bool
     */
    public function executeQuery($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Get single row from database
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param string $types Parameter types
     * @return array|null
     */
    public function getSingleRow($sql, $params = [], $types = '') {
        $result = $this->executeQuery($sql, $params, $types);
        return $result->fetch_assoc();
    }
    
    /**
     * Get multiple rows from database
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param string $types Parameter types
     * @return array
     */
    public function getMultipleRows($sql, $params = [], $types = '') {
        $result = $this->executeQuery($sql, $params, $types);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Insert data into database
     * @param string $table Table name
     * @param array $data Associative array of data
     * @return int Last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $types = str_repeat('s', count($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
        
        return $this->conn->insert_id;
    }
    
    /**
     * Update data in database
     * @param string $table Table name
     * @param array $data Associative array of data
     * @param string $where Where clause
     * @param array $whereParams Where parameters
     * @return int Affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $params = array_merge(array_values($data), $whereParams);
        $types = str_repeat('s', count($params));
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->affected_rows;
    }
    
    /**
     * Delete data from database
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Parameters
     * @param string $types Parameter types
     * @return int Affected rows
     */
    public function delete($table, $where, $params = [], $types = '') {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->affected_rows;
    }
    
    /**
     * Get count of records
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Parameters
     * @return int Count
     */
    public function getCount($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
        $result = $this->executeQuery($sql, $params);
        return $result->fetch_assoc()['count'];
    }
    
    /**
     * Log user activity
     * @param int $userId User ID
     * @param string $activityType Type of activity
     * @param string $description Description
     * @param string $ipAddress IP address
     */
    public function logActivity($userId, $activityType, $description, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $this->insert('user_activities', [
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => $ipAddress
        ]);
    }
    
    /**
     * Log audit trail
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $tableName Table affected
     * @param int $recordId Record ID
     * @param array $oldValues Old values
     * @param array $newValues New values
     * @param string $ipAddress IP address
     */
    public function logAudit($userId, $action, $tableName, $recordId, $oldValues = [], $newValues = [], $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $this->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => $ipAddress
        ]);
    }
    
    /**
     * Get user by username
     * @param string $username Username
     * @return array|null User data
     */
    public function getUserByUsername($username) {
        return $this->getSingleRow(
            "SELECT id, username, password, role, nama, email, status FROM users WHERE username = ? AND status = 'active'",
            [$username]
        );
    }
    
    /**
     * Record login attempt
     * @param string $ipAddress IP address
     * @param string $username Username
     * @param bool $success Success status
     * @param string $userAgent User agent
     */
    public function recordLoginAttempt($ipAddress, $username, $success, $userAgent = null) {
        $this->insert('login_attempts', [
            'ip_address' => $ipAddress,
            'username' => $username,
            'success' => $success,
            'user_agent' => $userAgent
        ]);
    }
    
    /**
     * Check if IP is locked out
     * @param string $ipAddress IP address
     * @param int $maxAttempts Maximum attempts
     * @param int $lockoutDuration Lockout duration in seconds
     * @return bool Is locked out
     */
    public function isIpLockedOut($ipAddress, $maxAttempts = 5, $lockoutDuration = 900) {
        $sql = "SELECT COUNT(*) as attempts 
                FROM login_attempts 
                WHERE ip_address = ? 
                AND success = FALSE 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $result = $this->getSingleRow($sql, [$ipAddress, $lockoutDuration]);
        return $result['attempts'] >= $maxAttempts;
    }
    
    /**
     * Get login attempts for IP
     * @param string $ipAddress IP address
     * @param int $duration Duration in seconds
     * @return array Login attempts
     */
    public function getLoginAttempts($ipAddress, $duration = 900) {
        return $this->getMultipleRows(
            "SELECT * FROM login_attempts 
             WHERE ip_address = ? 
             AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
             ORDER BY attempt_time DESC",
            [$ipAddress, $duration]
        );
    }
    
    /**
     * Clean up old login attempts
     * @param int $days Days to keep
     * @return int Deleted rows
     */
    public function cleanupOldLoginAttempts($days = 30) {
        return $this->delete(
            'login_attempts',
            'attempt_time < DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$days]
        );
    }
}
?>
