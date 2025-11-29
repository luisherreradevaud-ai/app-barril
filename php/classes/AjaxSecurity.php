<?php

/**
 * AjaxSecurity - Simplified security for AJAX endpoints
 * Compatible con PHP 5.6+
 */
class AjaxSecurity {

  private $config = array();
  private $validated_input = array();

  /**
   * Initialize and validate AJAX request with security checks
   */
  public static function init($config = array()) {
    $instance = new self();

    // Default configuration
    $defaults = array(
      'methods' => array('POST'),
      'csrf' => true,
      'auth' => true,
      'min_level' => null,
      'rate_limit' => true,
      'ajax_only' => false,
      'required_params' => array(),
      'input_rules' => array()
    );
    $instance->config = array_merge($defaults, $config);

    // Perform security checks
    $instance->performSecurityChecks();

    return $instance;
  }

  /**
   * Perform all configured security checks
   */
  private function performSecurityChecks() {
    // 1. Check if request is AJAX (if required)
    if ($this->config['ajax_only'] && !Security::isAjaxRequest()) {
      $this->sendError('Invalid request type', 'ajax_required', 400);
    }

    // 2. Validate HTTP method
    if (!Security::validateRequestMethod($this->config['methods'])) {
      $this->sendError(
        'Invalid request method. Allowed: ' . implode(', ', $this->config['methods']),
        'invalid_method',
        405
      );
    }

    // 3. Check rate limiting
    if ($this->config['rate_limit']) {
      if (!Security::checkRateLimit()) {
        $this->sendError(
          'Too many requests. Please try again later.',
          'rate_limit_exceeded',
          429
        );
      }
    }

    // 4. Get request data based on method
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_data = $request_method === 'GET' ? $_GET : $_POST;

    // Check if request has data (only for POST/PUT/PATCH)
    if (in_array($request_method, array('POST', 'PUT', 'PATCH')) && empty($request_data)) {
      $this->sendError('No data provided', 'no_data', 400);
    }

    // 5. Validate CSRF token (for POST/PUT/DELETE)
    if ($this->config['csrf'] && in_array($request_method, array('POST', 'PUT', 'DELETE', 'PATCH'))) {
      $csrf_token = '';
      if (isset($request_data['csrf_token'])) {
        $csrf_token = $request_data['csrf_token'];
      } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
      }

      if (empty($csrf_token)) {
        Security::logSecurityEvent('csrf_token_missing', array(
          'endpoint' => $_SERVER['REQUEST_URI']
        ));
        $this->sendError('CSRF token missing', 'csrf_missing', 403);
      }

      if (!Security::validateCSRFToken($csrf_token)) {
        Security::logSecurityEvent('csrf_validation_failed', array(
          'endpoint' => $_SERVER['REQUEST_URI']
        ));
        $this->sendError('Invalid CSRF token', 'csrf_invalid', 403);
      }
    }

    // 6. Check authentication
    if ($this->config['auth']) {
      if (!Security::isAuthenticated()) {
        Security::logSecurityEvent('unauthenticated_access', array(
          'endpoint' => $_SERVER['REQUEST_URI']
        ));
        $this->sendError('Authentication required', 'auth_required', 401);
      }
    }

    // 7. Check permission level
    if ($this->config['min_level'] !== null) {
      if (!Security::hasPermissionLevel($this->config['min_level'])) {
        $user_level = isset($GLOBALS['usuario']) && isset($GLOBALS['usuario']->nivel) ? $GLOBALS['usuario']->nivel : 'none';
        Security::logSecurityEvent('unauthorized_access', array(
          'endpoint' => $_SERVER['REQUEST_URI'],
          'required_level' => $this->config['min_level'],
          'user_level' => $user_level
        ));
        $this->sendError('Insufficient permissions', 'unauthorized', 403);
      }
    }

    // 8. Validate required parameters
    if (!empty($this->config['required_params'])) {
      foreach ($this->config['required_params'] as $param) {
        if (!isset($request_data[$param]) || $request_data[$param] === '') {
          $this->sendError(
            "Required parameter missing: " . $param,
            'missing_parameter',
            400
          );
        }
      }
    }

    // 9. Sanitize and validate input
    $this->validated_input = Security::validateInput(
      $request_data,
      $this->config['input_rules']
    );
  }

  /**
   * Get validated and sanitized input
   */
  public function input($key = null, $default = null) {
    if ($key === null) {
      return $this->validated_input;
    }

    return isset($this->validated_input[$key]) ? $this->validated_input[$key] : $default;
  }

  /**
   * Get raw input value
   */
  public function raw($key, $default = null) {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_data = $request_method === 'GET' ? $_GET : $_POST;

    return isset($request_data[$key]) ? $request_data[$key] : $default;
  }

  /**
   * Check if input has a specific key
   */
  public function has($key) {
    return isset($this->validated_input[$key]);
  }

  /**
   * Get current user
   */
  public function user() {
    return $GLOBALS['usuario'];
  }

  /**
   * Send success JSON response and exit
   */
  public function success($data = null, $message = 'OK') {
    header('Content-Type: application/json');
    echo json_encode(array(
      'status' => 'OK',
      'mensaje' => $message,
      'data' => $data
    ), JSON_PRETTY_PRINT);
    exit;
  }

  /**
   * Send error JSON response and exit
   */
  private function sendError($message, $error_code = 'error', $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json');

    echo json_encode(array(
      'status' => 'ERROR',
      'mensaje' => $message,
      'error_code' => $error_code
    ), JSON_PRETTY_PRINT);

    exit;
  }

  /**
   * Send error JSON response (public method)
   */
  public function error($message, $error_code = 'error', $http_code = 400) {
    $this->sendError($message, $error_code, $http_code);
  }

  /**
   * Get CSRF token
   */
  public static function getToken() {
    return Security::getCSRFToken();
  }

  /**
   * Simple AJAX endpoint wrapper with automatic error handling
   */
  public static function handle($config, $handler) {
    try {
      $ajax = self::init($config);
      $handler($ajax);
    } catch (Exception $e) {
      error_log("AJAX Error: " . $e->getMessage());

      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(array(
        'status' => 'ERROR',
        'mensaje' => 'Internal server error: ' . $e->getMessage()
      ), JSON_PRETTY_PRINT);
      exit;
    }
  }
}

?>
