<?php

/**
 * AjaxSecurity - Simplified security for AJAX endpoints
 *
 * Usage in AJAX endpoint:
 * $ajax = AjaxSecurity::init([
 *   'methods' => ['POST'],
 *   'csrf' => true,
 *   'auth' => true,
 *   'min_level' => 'Operario'
 * ]);
 *
 * All security checks are performed automatically.
 * If checks fail, appropriate JSON error is returned and script exits.
 */
class AjaxSecurity {

  private $config = [];
  private $validated_input = [];

  /**
   * Initialize and validate AJAX request with security checks
   *
   * @param array $config Configuration options:
   *   - methods: array of allowed HTTP methods (default: ['POST'])
   *   - csrf: bool, require CSRF token validation (default: true)
   *   - auth: bool, require authentication (default: true)
   *   - min_level: string, minimum required user level (default: null)
   *   - rate_limit: bool, enable rate limiting (default: true)
   *   - ajax_only: bool, require AJAX request (default: true)
   *   - required_params: array of required parameter names
   *   - input_rules: array of sanitization rules (field => type)
   *
   * @return AjaxSecurity Instance with validated input
   */
  public static function init($config = []) {
    $instance = new self();

    // Default configuration
    $instance->config = array_merge([
      'methods' => ['POST'],
      'csrf' => true,
      'auth' => true,
      'min_level' => null,
      'rate_limit' => true,
      'ajax_only' => false, // Set to false by default for compatibility
      'required_params' => [],
      'input_rules' => []
    ], $config);

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
    if (in_array($request_method, ['POST', 'PUT', 'PATCH']) && empty($request_data)) {
      $this->sendError('No data provided', 'no_data', 400);
    }

    // 5. Validate CSRF token (for POST/PUT/DELETE)
    if ($this->config['csrf'] && in_array($request_method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
      $csrf_token = $request_data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

      if (empty($csrf_token)) {
        Security::logSecurityEvent('csrf_token_missing', [
          'endpoint' => $_SERVER['REQUEST_URI']
        ]);
        $this->sendError('CSRF token missing', 'csrf_missing', 403);
      }

      if (!Security::validateCSRFToken($csrf_token)) {
        Security::logSecurityEvent('csrf_validation_failed', [
          'endpoint' => $_SERVER['REQUEST_URI']
        ]);
        $this->sendError('Invalid CSRF token', 'csrf_invalid', 403);
      }
    }

    // 6. Check authentication
    if ($this->config['auth']) {
      if (!Security::isAuthenticated()) {
        Security::logSecurityEvent('unauthenticated_access', [
          'endpoint' => $_SERVER['REQUEST_URI']
        ]);
        $this->sendError('Authentication required', 'auth_required', 401);
      }
    }

    // 7. Check permission level
    if ($this->config['min_level'] !== null) {
      if (!Security::hasPermissionLevel($this->config['min_level'])) {
        Security::logSecurityEvent('unauthorized_access', [
          'endpoint' => $_SERVER['REQUEST_URI'],
          'required_level' => $this->config['min_level'],
          'user_level' => $GLOBALS['usuario']->nivel ?? 'none'
        ]);
        $this->sendError('Insufficient permissions', 'unauthorized', 403);
      }
    }

    // 8. Validate required parameters
    if (!empty($this->config['required_params'])) {
      foreach ($this->config['required_params'] as $param) {
        if (!isset($request_data[$param]) || $request_data[$param] === '') {
          $this->sendError(
            "Required parameter missing: {$param}",
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
   *
   * @param string $key Optional key to get specific value
   * @param mixed $default Default value if key doesn't exist
   * @return mixed Sanitized input value or array
   */
  public function input($key = null, $default = null) {
    if ($key === null) {
      return $this->validated_input;
    }

    return $this->validated_input[$key] ?? $default;
  }

  /**
   * Get raw input value (use sparingly, prefer input())
   *
   * @param string $key Key to get
   * @param mixed $default Default value
   * @return mixed Raw input value
   */
  public function raw($key, $default = null) {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_data = $request_method === 'GET' ? $_GET : $_POST;

    return $request_data[$key] ?? $default;
  }

  /**
   * Check if input has a specific key
   *
   * @param string $key Key to check
   * @return bool True if exists
   */
  public function has($key) {
    return isset($this->validated_input[$key]);
  }

  /**
   * Get current user (shortcut)
   *
   * @return Usuario Current user object
   */
  public function user() {
    return $GLOBALS['usuario'];
  }

  /**
   * Send success JSON response and exit
   *
   * @param mixed $data Data to send
   * @param string $message Success message
   */
  public function success($data = null, $message = 'OK') {
    header('Content-Type: application/json');
    echo json_encode([
      'status' => 'OK',
      'mensaje' => $message,
      'data' => $data
    ], JSON_PRETTY_PRINT);
    exit;
  }

  /**
   * Send error JSON response and exit
   *
   * @param string $message Error message
   * @param string $error_code Error code
   * @param int $http_code HTTP status code
   */
  private function sendError($message, $error_code = 'error', $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json');

    echo json_encode([
      'status' => 'ERROR',
      'mensaje' => $message,
      'error_code' => $error_code
    ], JSON_PRETTY_PRINT);

    exit;
  }

  /**
   * Send error JSON response and exit (public method)
   *
   * @param string $message Error message
   * @param string $error_code Error code
   * @param int $http_code HTTP status code
   */
  public function error($message, $error_code = 'error', $http_code = 400) {
    $this->sendError($message, $error_code, $http_code);
  }

  /**
   * Get CSRF token (for including in responses/forms)
   *
   * @return string CSRF token
   */
  public static function getToken() {
    return Security::getCSRFToken();
  }

  /**
   * Simple AJAX endpoint wrapper with automatic error handling
   *
   * @param array $config Security configuration
   * @param callable $handler Handler function that receives AjaxSecurity instance
   */
  public static function handle($config, $handler) {
    try {
      $ajax = self::init($config);
      $handler($ajax);
    } catch (Exception $e) {
      // Log the error
      error_log("AJAX Error: " . $e->getMessage());

      // Send error response
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 'ERROR',
        'mensaje' => 'Internal server error: ' . $e->getMessage()
      ], JSON_PRETTY_PRINT);
      exit;
    }
  }
}

?>
