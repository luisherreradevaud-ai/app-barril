<?php

/**
 * Security Class - Comprehensive security utilities for Barril.cl
 *
 * Features:
 * - CSRF token generation and validation
 * - Rate limiting (IP-based)
 * - Input sanitization and validation
 * - Request validation (HTTP method, content-type)
 * - IP blocking and whitelisting
 * - Security audit logging
 * - XSS prevention
 * - SQL injection prevention helpers
 */
class Security {

  // Rate limiting settings (stored in session)
  const RATE_LIMIT_WINDOW = 60; // seconds
  const RATE_LIMIT_MAX_REQUESTS = 60; // max requests per window
  const RATE_LIMIT_BLOCK_DURATION = 300; // 5 minutes block

  // CSRF settings
  const CSRF_TOKEN_LENGTH = 32;
  const CSRF_TOKEN_EXPIRY = 3600; // 1 hour

  /**
   * Initialize security system
   * Call this at the start of app.php
   */
  public static function init() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // Initialize CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
      self::regenerateCSRFToken();
    }

    // Initialize rate limiting storage
    if (!isset($_SESSION['rate_limit'])) {
      $_SESSION['rate_limit'] = [
        'requests' => [],
        'blocked_until' => 0
      ];
    }

    // Clean old rate limit data
    self::cleanRateLimitData();
  }

  /**
   * Generate new CSRF token
   */
  public static function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(self::CSRF_TOKEN_LENGTH));
    $_SESSION['csrf_token_time'] = time();
    return $_SESSION['csrf_token'];
  }

  /**
   * Get current CSRF token
   */
  public static function getCSRFToken() {
    self::init();

    // Regenerate if expired
    if (isset($_SESSION['csrf_token_time'])) {
      if (time() - $_SESSION['csrf_token_time'] > self::CSRF_TOKEN_EXPIRY) {
        return self::regenerateCSRFToken();
      }
    }

    return $_SESSION['csrf_token'] ?? self::regenerateCSRFToken();
  }

  /**
   * Validate CSRF token
   * @param string $token Token to validate
   * @return bool True if valid
   */
  public static function validateCSRFToken($token) {
    self::init();

    if (!isset($_SESSION['csrf_token'])) {
      return false;
    }

    // Check expiry
    if (isset($_SESSION['csrf_token_time'])) {
      if (time() - $_SESSION['csrf_token_time'] > self::CSRF_TOKEN_EXPIRY) {
        return false;
      }
    }

    // Timing-safe comparison
    return hash_equals($_SESSION['csrf_token'], $token);
  }

  /**
   * Rate limiting check
   * @param string $identifier Unique identifier (IP, user ID, etc.)
   * @return bool True if allowed, false if rate limited
   */
  public static function checkRateLimit($identifier = null) {
    self::init();

    if ($identifier === null) {
      $identifier = self::getClientIP();
    }

    // Check if blocked
    if ($_SESSION['rate_limit']['blocked_until'] > time()) {
      self::logSecurityEvent('rate_limit_blocked', [
        'identifier' => $identifier,
        'blocked_until' => $_SESSION['rate_limit']['blocked_until']
      ]);
      return false;
    }

    // Clean old requests
    self::cleanRateLimitData();

    // Count requests in current window
    $window_start = time() - self::RATE_LIMIT_WINDOW;
    $requests_in_window = 0;

    foreach ($_SESSION['rate_limit']['requests'] as $req) {
      if ($req['identifier'] === $identifier && $req['time'] > $window_start) {
        $requests_in_window++;
      }
    }

    // Check if exceeded
    if ($requests_in_window >= self::RATE_LIMIT_MAX_REQUESTS) {
      $_SESSION['rate_limit']['blocked_until'] = time() + self::RATE_LIMIT_BLOCK_DURATION;
      self::logSecurityEvent('rate_limit_exceeded', [
        'identifier' => $identifier,
        'requests' => $requests_in_window
      ]);
      return false;
    }

    // Add current request
    $_SESSION['rate_limit']['requests'][] = [
      'identifier' => $identifier,
      'time' => time()
    ];

    return true;
  }

  /**
   * Clean old rate limit data
   */
  private static function cleanRateLimitData() {
    if (!isset($_SESSION['rate_limit']['requests'])) {
      return;
    }

    $window_start = time() - self::RATE_LIMIT_WINDOW;
    $_SESSION['rate_limit']['requests'] = array_filter(
      $_SESSION['rate_limit']['requests'],
      function($req) use ($window_start) {
        return $req['time'] > $window_start;
      }
    );
  }

  /**
   * Get client IP address (considering proxies)
   */
  public static function getClientIP() {
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
  }

  /**
   * Sanitize input data
   * @param mixed $data Data to sanitize
   * @param string $type Type of sanitization (string, email, int, float, html, sql)
   * @return mixed Sanitized data
   */
  public static function sanitize($data, $type = 'string') {
    if (is_array($data)) {
      return array_map(function($item) use ($type) {
        return self::sanitize($item, $type);
      }, $data);
    }

    switch ($type) {
      case 'int':
        return filter_var($data, FILTER_SANITIZE_NUMBER_INT);

      case 'float':
        return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

      case 'email':
        return filter_var($data, FILTER_SANITIZE_EMAIL);

      case 'url':
        return filter_var($data, FILTER_SANITIZE_URL);

      case 'html':
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

      case 'sql':
        return addslashes($data);

      case 'string':
      default:
        $data = strip_tags($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
  }

  /**
   * Validate HTTP request method
   * @param array $allowed_methods Array of allowed methods (e.g., ['POST', 'GET'])
   * @return bool True if method is allowed
   */
  public static function validateRequestMethod($allowed_methods = ['POST']) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    return in_array(strtoupper($method), array_map('strtoupper', $allowed_methods));
  }

  /**
   * Validate that request is AJAX
   * @return bool True if AJAX request
   */
  public static function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  /**
   * Check if user is authenticated
   * @param Usuario $usuario User object to check
   * @return bool True if authenticated
   */
  public static function isAuthenticated($usuario = null) {
    if ($usuario === null) {
      $usuario = $GLOBALS['usuario'] ?? null;
    }

    if (!$usuario) {
      return false;
    }

    return $usuario->nombre !== 'Invitado' &&
           !empty($usuario->id) &&
           $usuario->estado !== 'Bloqueado';
  }

  /**
   * Check if user has required permission level
   * @param string $required_level Required user level
   * @param Usuario $usuario User object to check
   * @return bool True if authorized
   */
  public static function hasPermissionLevel($required_level, $usuario = null) {
    if ($usuario === null) {
      $usuario = $GLOBALS['usuario'] ?? null;
    }

    if (!self::isAuthenticated($usuario)) {
      return false;
    }

    $levels = [
      'Visita' => 0,
      'Cliente' => 1,
      'Repartidor' => 2,
      'Vendedor' => 3,
      'Operario' => 4,
      'Jefe de Cocina' => 5,
      'Jefe de Planta' => 6,
      'Administrador' => 7
    ];

    $user_level = $levels[$usuario->nivel] ?? 0;
    $required_level_num = $levels[$required_level] ?? 0;

    return $user_level >= $required_level_num;
  }

  /**
   * Log security event
   * @param string $event_type Type of security event
   * @param array $data Additional data to log
   */
  public static function logSecurityEvent($event_type, $data = []) {
    $log_entry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'event_type' => $event_type,
      'ip' => self::getClientIP(),
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
      'user_id' => $GLOBALS['usuario']->id ?? 'anonymous',
      'uri' => $_SERVER['REQUEST_URI'] ?? '',
      'data' => $data
    ];

    // Log to file
    $log_file = $GLOBALS['base_dir'] . '/logs/security.log';
    $log_dir = dirname($log_file);

    if (!file_exists($log_dir)) {
      mkdir($log_dir, 0755, true);
    }

    error_log(json_encode($log_entry) . "\n", 3, $log_file);

    // Also log to PHP error log for critical events
    $critical_events = ['rate_limit_exceeded', 'csrf_failed', 'unauthorized_access', 'sql_injection_attempt'];
    if (in_array($event_type, $critical_events)) {
      error_log("SECURITY: {$event_type} - IP: {$log_entry['ip']} - " . json_encode($data));
    }
  }

  /**
   * Detect potential SQL injection attempts
   * @param string $input Input to check
   * @return bool True if suspicious
   */
  public static function detectSQLInjection($input) {
    $patterns = [
      '/\bUNION\b.*\bSELECT\b/i',
      '/\bSELECT\b.*\bFROM\b/i',
      '/\bINSERT\b.*\bINTO\b/i',
      '/\bUPDATE\b.*\bSET\b/i',
      '/\bDELETE\b.*\bFROM\b/i',
      '/\bDROP\b.*\bTABLE\b/i',
      '/\bEXEC\b.*\(/i',
      '/\bOR\b.*=.*\b/i',
      '/--|#|\/\*|\*\//',
      '/\bxp_cmdshell\b/i'
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $input)) {
        self::logSecurityEvent('sql_injection_attempt', ['input' => substr($input, 0, 100)]);
        return true;
      }
    }

    return false;
  }

  /**
   * Detect potential XSS attempts
   * @param string $input Input to check
   * @return bool True if suspicious
   */
  public static function detectXSS($input) {
    $patterns = [
      '/<script\b/i',
      '/javascript:/i',
      '/on\w+\s*=/i', // onclick, onload, etc.
      '/<iframe\b/i',
      '/<object\b/i',
      '/<embed\b/i'
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $input)) {
        self::logSecurityEvent('xss_attempt', ['input' => substr($input, 0, 100)]);
        return true;
      }
    }

    return false;
  }

  /**
   * Validate and sanitize entire $_POST or $_GET array
   * @param array $data Data to validate
   * @param array $rules Validation rules (field => type)
   * @return array Sanitized data
   */
  public static function validateInput($data, $rules = []) {
    $sanitized = [];

    foreach ($data as $key => $value) {
      // Skip if empty array
      if (is_array($value) && empty($value)) {
        continue;
      }

      // Get sanitization type from rules
      $type = $rules[$key] ?? 'string';

      // Check for injection attempts
      if (is_string($value)) {
        if (self::detectSQLInjection($value)) {
          self::logSecurityEvent('input_validation_failed', [
            'field' => $key,
            'reason' => 'sql_injection'
          ]);
          continue; // Skip this field
        }

        if (self::detectXSS($value)) {
          self::logSecurityEvent('input_validation_failed', [
            'field' => $key,
            'reason' => 'xss'
          ]);
          continue; // Skip this field
        }
      }

      // Sanitize based on type
      $sanitized[$key] = self::sanitize($value, $type);
    }

    return $sanitized;
  }
}

?>
