<?php

namespace App\Exceptions;

use RuntimeException;

/** A page could not be fetched or read. The message is safe to show to the admin. */
class PageFetchException extends RuntimeException {}
