<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule was broken - an empty invoice was posted, a payment was
 * over-allocated, a settled document was voided. Controllers catch this and
 * turn it into a session('error') flash; it is never a 500.
 */
class DomainException extends RuntimeException {}
