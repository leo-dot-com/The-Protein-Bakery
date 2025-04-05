<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51RAAW8D3fzkYXGORoJ9vvtHDRfk9vOLxKjbXCa75u3kMR3yDg7h8xLBq7ov1O4bwBcDf2wx6LOcnlJv0WOAWn2Ex002AcnRM7M');

// For JavaScript integration
define('STRIPE_PUBLIC_KEY', 'pk_test_51RAAW8D3fzkYXGORrtWgQgyeD8Kgn1C2WruXJAn1YKklFJRd6dbZ8ZgyRrt0qnkDdcNFK7pGdwIELm15rhJFmsKC000jaRvvjP');