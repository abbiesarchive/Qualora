<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a Gemini call fails or returns something unusable.
 * The message is safe to show to users; technical detail goes to the log.
 */
class GeminiException extends RuntimeException {}
