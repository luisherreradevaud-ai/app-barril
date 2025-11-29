<?php

/**
 * Security Class - Comprehensive security utilities for Barril.cl
 * Compatible con PHP 5.6+
 */
class Security {

  // Rate limiting settings (stored in session)
  const RATE_LIMIT_WINDOW = 60;
  const RATE_LIMIT_MAX_REQUESTS = 60;
  const RATE_LIMIT_BLOCK_DURATION = 300;

  // CSRF settings
  const CSRF_TOKEN_LENGTH = 32;
  const CSRF_TOKEN_EXPIRY = 3600;

  /**
   * Initialize security system
   */
  public static function init() {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
      self::regenerateCSRFToken();
    }

    if (!isset($_SESSION['rate_limit'])) {
      $_SESSION['rate_limit'] = array(
        'requests' => array(),
        'blocked_until' => 0
      );
    }

    self::cleanRateLimitData();
  }

  /**
   * Generate new CSRF token (PHP 5.6 compatible)
   */
  public static function regenerateCSRFToken() {
    // PHP 5.6 compatible random bytes
    if (function_exists('random_bytes')) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(self::CSRF_TOKEN_LENGTH));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
      $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(self::CSRF_TOKEN_LENGTH));
    } else {
      $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(self::CSRF_TOKEN_LENGTH, MCRYPT_DEV_URANDOM));
    }
    $_SESSION['csrf_token_time'] = time();
    return $_SESSION['csrf_token'];
  }

  /**
   * Get current CSRF token
   */
  public static function getCSRFToken() {
    self::init();

    if (isset($_SESSION['csrf_token_time'])) {
      if (time() - $_SESSION['csrf_token_time'] > self::CSRF_TOKEN_EXPIRY) {
        return self::regenerateCSRFToken();
      }
    }

    if (isset($_SESSION['csrf_token'])) {
      return $_SESSION['csrf_token'];
    }
    return self::regenerateCSRFToken();
  }

  /**
   * Validate CSRF token
   */
  public static function validateCSRFToken($token) {
    self::init();

    if (!isset($_SESSION['csrf_token'])) {
      return false;
    }

    if (isset($_SESSION['csrf_token_time'])) {
      if (time() - $_SESSION['csrf_token_time'] > self::CSRF_TOKEN_EXPIRY) {
        return false;
      }
    }

    if (function_exists('hash_equals')) {
      return hash_equals($_SESSION['csrf_token'], $token);
    }
    return $_SESSION['csrf_token'] === $token;
  }

  /**
   * Rate limiting check
   */
  public static function checkRateLimit($identifier = null) {
    self::init();

    if ($identifier === null) {
      $identifier = self::getClientIP();
    }

    if ($_SESSION['rate_limit']['blocked_until'] > time()) {
      self::logSecurityEvent('rate_limit_blocked', array(
        'identifier' => $identifier,
        'blocked_until' => $_SESSION['rate_limit']['blocked_until']
      ));
      return false;
    }

    self::cleanRateLimitData();

    $window_start = time() - self::RATE_LIMIT_WINDOW;
    $requests_in_window = 0;

    foreach ($_SESSION['rate_limit']['requests'] as $req) {
      if ($req['identifier'] === $identifier && $req['time'] > $window_start) {
        $requests_in_window++;
      }
    }

    if ($requests_in_window >= self::RATE_LIMIT_MAX_REQUESTS) {
      $_SESSION['rate_limit']['blocked_until'] = time() + self::RATE_LIMIT_BLOCK_DURATION;
      self::logSecurityEvent('rate_limit_exceeded', array(
        'identifier' => $identifier,
        'requests' => $requests_in_window
      ));
      return false;
    }

    $_SESSION['rate_limit']['requests'][] = array(
      'identifier' => $identifier,
      'time' => time()
    );

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
    $filtered = array();
    foreach ($_SESSION['rate_limit']['requests'] as $req) {
      if ($req['time'] > $window_start) {
        $filtered[] = $req;
      }
    }
    $_SESSION['rate_limit']['requests'] = $filtered;
  }

  /**
   * Get client IP address
   */
  public static function getClientIP() {
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      $ip = $parts[0];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
      $ip = $_SERVER['REMOTE_ADDR'];
    } else {
      $ip = 'unknown';
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
  }

  /**
   * Sanitize input data
   */
  public static function sanitize($data, $type = 'string') {
    if (is_array($data)) {
      $result = array();
      foreach ($data as $key => $item) {
        $result[$key] = self::sanitize($item, $type);
      }
      return $result;
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
   */
  public static function validateRequestMethod($allowed_methods = array('POST')) {
    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    return in_array(strtoupper($method), array_map('strtoupper', $allowed_methods));
  }

  /**
   * Validate that request is AJAX
   */
  public static function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  /**
   * Check if user is authenticated
   */
  public static function isAuthenticated($usuario = null) {
    if ($usuario === null) {
      $usuario = isset($GLOBALS['usuario']) ? $GLOBALS['usuario'] : null;
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
   */
  public static function hasPermissionLevel($required_level, $usuario = null) {
    if ($usuario === null) {
      $usuario = isset($GLOBALS['usuario']) ? $GLOBALS['usuario'] : null;
    }

    if (!self::isAuthenticated($usuario)) {
      return false;
    }

    $levels = array(
      'Visita' => 0,
      'Cliente' => 1,
      'Repartidor' => 2,
      'Vendedor' => 3,
      'Operario' => 4,
      'Jefe de Cocina' => 5,
      'Jefe de Planta' => 6,
      'Administrador' => 7
    );

    $user_level = isset($levels[$usuario->nivel]) ? $levels[$usuario->nivel] : 0;
    $required_level_num = isset($levels[$required_level]) ? $levels[$required_level] : 0;

    return $user_level >= $required_level_num;
  }

  /**
   * Log security event
   */
  public static function logSecurityEvent($event_type, $data = array()) {
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
    $user_id = isset($GLOBALS['usuario']) && isset($GLOBALS['usuario']->id) ? $GLOBALS['usuario']->id : 'anonymous';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    $log_entry = array(
      'timestamp' => date('Y-m-d H:i:s'),
      'event_type' => $event_type,
      'ip' => self::getClientIP(),
      'user_agent' => $user_agent,
      'user_id' => $user_id,
      'uri' => $uri,
      'data' => $data
    );

    $log_file = $GLOBALS['base_dir'] . '/logs/security.log';
    $log_dir = dirname($log_file);

    if (!file_exists($log_dir)) {
      mkdir($log_dir, 0755, true);
    }

    error_log(json_encode($log_entry) . "\n", 3, $log_file);

    $critical_events = array('rate_limit_exceeded', 'csrf_failed', 'unauthorized_access', 'sql_injection_attempt');
    if (in_array($event_type, $critical_events)) {
      error_log("SECURITY: " . $event_type . " - IP: " . $log_entry['ip'] . " - " . json_encode($data));
    }
  }

  /**
   * Detect potential SQL injection attempts
   */
  public static function detectSQLInjection($input) {
    $patterns = array(
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
    );

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $input)) {
        self::logSecurityEvent('sql_injection_attempt', array('input' => substr($input, 0, 100)));
        return true;
      }
    }

    return false;
  }

  /**
   * Detect potential XSS attempts
   */
  public static function detectXSS($input) {
    $patterns = array(
      '/<script\b/i',
      '/javascript:/i',
      '/on\w+\s*=/i',
      '/<iframe\b/i',
      '/<object\b/i',
      '/<embed\b/i'
    );

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $input)) {
        self::logSecurityEvent('xss_attempt', array('input' => substr($input, 0, 100)));
        return true;
      }
    }

    return false;
  }

  /**
   * Validate and sanitize entire $_POST or $_GET array
   */
  public static function validateInput($data, $rules = array()) {
    $sanitized = array();

    foreach ($data as $key => $value) {
      if (is_array($value) && empty($value)) {
        continue;
      }

      $type = isset($rules[$key]) ? $rules[$key] : 'string';

      if (is_string($value)) {
        if (self::detectSQLInjection($value)) {
          self::logSecurityEvent('input_validation_failed', array(
            'field' => $key,
            'reason' => 'sql_injection'
          ));
          continue;
        }

        if (self::detectXSS($value)) {
          self::logSecurityEvent('input_validation_failed', array(
            'field' => $key,
            'reason' => 'xss'
          ));
          continue;
        }
      }

      $sanitized[$key] = self::sanitize($value, $type);
    }

    return $sanitized;
  }
}

?>
