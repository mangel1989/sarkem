<?php
class CacheHelper {
    private $cacheDir;
    private $defaultTTL;
    
    public function __construct($cacheDir = 'cache/', $defaultTTL = 3600) {
        $this->cacheDir = $cacheDir;
        $this->defaultTTL = $defaultTTL;
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     * @param string $key Cache key
     * @return mixed Cached data or null if not found
     */
    public function get($key) {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cached data
     * @param string $key Cache key
     * @param mixed $value Data to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->defaultTTL;
        }
        
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    /**
     * Delete cached data
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key) {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     * @return int Number of files deleted
     */
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Clean expired cache
     * @return int Number of files cleaned
     */
    public function cleanExpired() {
        $files = glob($this->cacheDir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get cache statistics
     * @return array Cache statistics
     */
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
        $totalFiles = count($files);
        $totalSize = 0;
        $expiredFiles = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                $expiredFiles++;
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'expired_files' => $expiredFiles,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Get cache filename
     * @param string $key Cache key
     * @return string Cache filename
     */
    private function getCacheFilename($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }
    
    /**
     * Cache dashboard statistics
     * @param DatabaseHelper $db Database helper instance
     * @return array Dashboard statistics
     */
    public function getDashboardStats($db) {
        $cacheKey = 'dashboard_stats';
        $stats = $this->get($cacheKey);
        
        if ($stats === null) {
            $stats = [
                'total_users' => $db->getCount('users'),
                'total_pelanggan' => $db->getCount('pelanggan'),
                'total_perbaikan' => $db->getCount('perbaikan'),
                'total_barang' => $db->getCount('barang'),
                'total_cabang' => $db->getCount('cabang'),
                'active_teknisi' => $db->getCount('users', 'role = ? AND status = ?', ['teknisi', 'active']),
                'perbaikan_hari_ini' => $db->getCount('perbaikan', 'DATE(tanggal) = CURDATE()'),
                'perbaikan_bulan_ini' => $db->getCount('perbaikan', 'MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())'),
                'pelanggan_baru_bulan_ini' => $db->getCount('pelanggan', 'MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())')
            ];
            
            $this->set($cacheKey, $stats, 300); // Cache for 5 minutes
        }
        
        return $stats;
    }
    
    /**
     * Cache user data
     * @param int $userId User ID
     * @param DatabaseHelper $db Database helper instance
     * @return array User data
     */
    public function getUserData($userId, $db) {
        $cacheKey = "user_data_$userId";
        $userData = $this->get($cacheKey);
        
        if ($userData === null) {
            $userData = $db->getSingleRow(
                "SELECT id, username, role, nama, email, status FROM users WHERE id = ?",
                [$userId]
            );
            
            $this->set($cacheKey, $userData, 1800); // Cache for 30 minutes
        }
        
        return $userData;
    }
    
    /**
     * Cache user permissions
     * @param int $userId User ID
     * @param DatabaseHelper $db Database helper instance
     * @return array User permissions
     */
    public function getUserPermissions($userId, $db) {
        $cacheKey = "user_permissions_$userId";
        $permissions = $this->get($cacheKey);
        
        if ($permissions === null) {
            $permissions = $db->getMultipleRows(
                "SELECT p.resource, p.action 
                 FROM permissions p
                 JOIN user_permissions up ON p.id = up.permission_id
                 WHERE up.user_id = ?",
                [$userId]
            );
            
            $this->set($cacheKey, $permissions, 3600); // Cache for 1 hour
        }
        
        return $permissions;
    }
    
    /**
     * Cache role permissions
     * @param string $role Role name
     * @param DatabaseHelper $db Database helper instance
     * @return array Role permissions
     */
    public function getRolePermissions($role, $db) {
        $cacheKey = "role_permissions_$role";
        $permissions = $this->get($cacheKey);
        
        if ($permissions === null) {
            $permissions = $db->getMultipleRows(
                "SELECT p.resource, p.action 
                 FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 JOIN roles r ON rp.role_id = r.id
                 WHERE r.name = ?",
                [$role]
            );
            
            $this->set($cacheKey, $permissions, 3600); // Cache for 1 hour
        }
        
        return $permissions;
    }
    
    /**
     * Cache system settings
     * @param DatabaseHelper $db Database helper instance
     * @return array System settings
     */
    public function getSystemSettings($db) {
        $cacheKey = 'system_settings';
        $settings = $this->get($cacheKey);
        
        if ($settings === null) {
            $settings = $db->getMultipleRows(
                "SELECT setting_key, setting_value FROM settings"
            );
            
            $settingsArray = [];
            foreach ($settings as $setting) {
                $settingsArray[$setting['setting_key']] = $setting['setting_value'];
            }
            
            $this->set($cacheKey, $settingsArray, 3600); // Cache for 1 hour
        }
        
        return $settings;
    }
    
    /**
     * Cache user sessions
     * @param int $userId User ID
     * @param DatabaseHelper $db Database helper instance
     * @return array User sessions
     */
    public function getUserSessions($userId, $db) {
        $cacheKey = "user_sessions_$userId";
        $sessions = $this->get($cacheKey);
        
        if ($sessions === null) {
            $sessions = $db->getMultipleRows(
                "SELECT * FROM user_sessions 
                 WHERE user_id = ? 
                 AND expires_at > NOW()
                 ORDER BY last_activity DESC",
                [$userId]
            );
            
            $this->set($cacheKey, $sessions, 300); // Cache for 5 minutes
        }
        
        return $sessions;
    }
    
    /**
     * Cache recent activities
     * @param int $limit Limit of activities
     * @param DatabaseHelper $db Database helper instance
     * @return array Recent activities
     */
    public function getRecentActivities($limit = 10, $db) {
        $cacheKey = "recent_activities_$limit";
        $activities = $this->get($cacheKey);
        
        if ($activities === null) {
            $activities = $db->getMultipleRows(
                "SELECT ua.*, u.username, u.nama 
                 FROM user_activities ua
                 JOIN users u ON ua.user_id = u.id
                 ORDER BY ua.created_at DESC
                 LIMIT ?",
                [$limit]
            );
            
            $this->set($cacheKey, $activities, 300); // Cache for 5 minutes
        }
        
        return $activities;
    }
    
    /**
     * Cache user statistics
     * @param int $userId User ID
     * @param DatabaseHelper $db Database helper instance
     * @return array User statistics
     */
    public function getUserStats($userId, $db) {
        $cacheKey = "user_stats_$userId";
        $stats = $this->get($cacheKey);
        
        if ($stats === null) {
            $stats = [
                'total_perbaikan' => $db->getCount('perbaikan', 'user_id = ?', [$userId]),
                'total_absensi' => $db->getCount('absensi', 'user_id = ?', [$userId]),
                'total_gaji' => $db->getCount('gaji', 'user_id = ?', [$userId]),
                'total_kasbon' => $db->getCount('kasbon', 'user_id = ?', [$userId]),
                'last_activity' => $db->getSingleRow(
                    "SELECT created_at FROM user_activities 
                     WHERE user_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 1",
                    [$userId]
                )
            ];
            
            $this->set($cacheKey, $stats, 600); // Cache for 10 minutes
        }
        
        return $stats;
    }
    
    /**
     * Cache system statistics
     * @param DatabaseHelper $db Database helper instance
     * @return array System statistics
     */
    public function getSystemStats($db) {
        $cacheKey = 'system_stats';
        $stats = $this->get($cacheKey);
        
        if ($stats === null) {
            $stats = [
                'total_users' => $db->getCount('users'),
                'total_pelanggan' => $db->getCount('pelanggan'),
                'total_perbaikan' => $db->getCount('perbaikan'),
                'total_barang' => $db->getCount('barang'),
                'total_cabang' => $db->getCount('cabang'),
                'total_absensi' => $db->getCount('absensi'),
                'total_gaji' => $db->getCount('gaji'),
                'total_kasbon' => $db->getCount('kasbon'),
                'active_sessions' => $db->getCount('user_sessions', 'expires_at > NOW()'),
                'today_logins' => $db->getCount('login_attempts', 'DATE(attempt_time) = CURDATE() AND success = 1'),
                'failed_logins_today' => $db->getCount('login_attempts', 'DATE(attempt_time) = CURDATE() AND success = 0')
            ];
            
            $this->set($cacheKey, $stats, 300); // Cache for 5 minutes
        }
        
        return $stats;
    }
}
?>
