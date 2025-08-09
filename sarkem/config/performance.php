<?php
// Performance Configuration
return [
    // Cache settings
    'cache' => [
        'enabled' => true,
        'driver' => 'file', // file, redis, memcached
        'ttl' => 3600, // Default TTL in seconds
        'directory' => 'cache/',
        'prefix' => 'sarkem_'
    ],
    
    // Database optimization
    'database' => [
        'persistent_connection' => true,
        'charset' => 'utf8mb4',
        'timezone' => '+07:00',
        'buffer_size' => 1000,
        'query_cache' => true,
        'slow_query_log' => true,
        'slow_query_threshold' => 1000 // milliseconds
    ],
    
    // Session optimization
    'session' => [
        'lifetime' => 3600, // seconds
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'cookie_lifetime' => 0,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ],
    
    // File upload optimization
    'upload' => [
        'max_file_size' => 10485760, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => 'uploads/',
        'thumbnail_size' => [150, 150],
        'quality' => 80
    ],
    
    // Image optimization
    'image' => [
        'quality' => 80,
        'max_width' => 1920,
        'max_height' => 1080,
        'thumbnail_width' => 150,
        'thumbnail_height' => 150
    ],
    
    // Email optimization
    'email' => [
        'queue' => true,
        'batch_size' => 50,
        'retry_attempts' => 3,
        'retry_delay' => 60
    ],
    
    // API optimization
    'api' => [
        'rate_limit' => 100,
        'rate_limit_window' => 3600,
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1
    ],
    
    // Security optimization
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900,
        'session_regenerate' => true,
        'csrf_protection' => true,
        'xss_protection' => true,
        'sql_injection_protection' => true
    ],
    
    // Logging optimization
    'logging' => [
        'level' => 'info',
        'rotation' => true,
        'max_files' => 10,
        'max_size' => 10485760, // 10MB
        'compression' => true
    ],
    
    // Performance monitoring
    'monitoring' => [
        'enabled' => true,
        'threshold' => [
            'query_time' => 1000, // milliseconds
            'memory_usage' => 104857600, // 100MB
            'cpu_usage' => 80 // percentage
        ],
        'alerts' => [
            'email' => true,
            'sms' => false,
            'webhook' => false
        ]
    ],
    
    // Database connection pooling
    'connection_pool' => [
        'enabled' => true,
        'max_connections' => 10,
        'min_connections' => 2,
        'timeout' => 30,
        'retry_attempts' => 3
    ],
    
    // Query optimization
    'query_optimization' => [
        'enabled' => true,
        'cache_queries' => true,
        'cache_ttl' => 300,
        'slow_query_log' => true,
        'explain_queries' => true
    ],
    
    // Memory optimization
    'memory' => [
        'limit' => 134217728, // 128MB
        'gc_enabled' => true,
        'gc_probability' => 1,
        'gc_divisor' => 100
    ],
    
    // CPU optimization
    'cpu' => [
        'limit' => 80, // percentage
        'priority' => 'normal',
        'affinity' => null
    ],
    
    // Disk optimization
    'disk' => [
        'cache_dir' => 'cache/',
        'log_dir' => 'logs/',
        'upload_dir' => 'uploads/',
        'backup_dir' => 'backups/'
    ],
    
    // Network optimization
    'network' => [
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1
    ],
    
    // Compression optimization
    'compression' => [
        'enabled' => true,
        'level' => 6,
        'types' => ['gzip', 'deflate', 'brotli']
    ],
    
    // CDN optimization
    'cdn' => [
        'enabled' => false,
        'url' => '',
        'types' => ['css', 'js', 'images']
    ],
    
    // Browser optimization
    'browser' => [
        'cache_control' => true,
        'etag' => true,
        'expires' => true,
        'gzip' => true
    ],
    
    // Mobile optimization
    'mobile' => [
        'responsive' => true,
        'viewport' => true,
        'touch' => true,
        'gestures' => true
    ],
    
    // SEO optimization
    'seo' => [
        'meta_tags' => true,
        'sitemap' => true,
        'robots' => true,
        'canonical' => true
    ],
    
    // Accessibility optimization
    'accessibility' => [
        'aria' => true,
        'alt_text' => true,
        'keyboard' => true,
        'screen_reader' => true
    ],
    
    // Internationalization optimization
    'i18n' => [
        'enabled' => true,
        'default_locale' => 'id_ID',
        'fallback_locale' => 'en_US',
        'timezone' => 'Asia/Jakarta'
    ],
    
    // Security headers
    'security_headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self'"
    ],
    
    // Error handling
    'error_handling' => [
        'display_errors' => false,
        'log_errors' => true,
        'error_log' => 'logs/error.log',
        'error_reporting' => E_ALL
    ],
    
    // Debugging
    'debugging' => [
        'enabled' => false,
        'profiler' => false,
        'trace' => false,
        'memory' => false
    ],
    
    // Testing
    'testing' => [
        'enabled' => false,
        'coverage' => false,
        'profiling' => false,
        'benchmarking' => false
    ],
    
    // Deployment
    'deployment' => [
        'environment' => 'production',
        'debug' => false,
        'maintenance' => false,
        'backup' => true
    ],
    
    // Monitoring
    'monitoring' => [
        'enabled' => true,
        'interval' => 60,
        'threshold' => [
            'cpu' => 80,
            'memory' => 80,
            'disk' => 80,
            'network' => 80
        ],
        'alerts' => [
            'email' => true,
            'sms' => false,
            'webhook' => false
        ]
    ],
    
    // Backup
    'backup' => [
        'enabled' => true,
        'interval' => 86400, // 24 hours
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Recovery
    'recovery' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 7, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Maintenance
    'maintenance' => [
        'enabled' => false,
        'interval' => 604800, // 7 days
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Optimization
    'optimization' => [
        'enabled' => true,
        'interval' => 86400, // 24 hours
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Performance
    'performance' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Security
    'security' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Compliance
    'compliance' => [
        'enabled' => true,
        'interval' => 86400, // 24 hours
        'retention' => 90, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Audit
    'audit' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 90, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Analytics
    'analytics' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 90, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Reporting
    'reporting' => [
        'enabled' => true,
        'interval' => 86400, // 24 hours
        'retention' => 90, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Notifications
    'notifications' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Alerts
    'alerts' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Monitoring
    'monitoring' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Dashboard
    'dashboard' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // API
    'api' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Webhook
    'webhook' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Integration
    'integration' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Migration
    'migration' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Upgrade
    'upgrade' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Update
    'update' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Patch
    'patch' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Fix
    'fix' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Hotfix
    'hotfix' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Emergency
    'emergency' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ],
    
    // Critical
    'critical' => [
        'enabled' => true,
        'interval' => 3600, // 1 hour
        'retention' => 30, // days
        'compression' => true,
        'encryption' => true
    ]
];
?>
