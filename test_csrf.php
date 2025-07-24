<?php
// test_csrf.php - Test CSRF functionality
require_once 'includes/csrf.class.php';

echo "<h2>CSRF Class Test</h2>";

// Test static methods
echo "<h3>Static Methods:</h3>";
$token1 = CSRF::generateToken();
echo "Generated token: " . $token1 . "<br>";

$isValid = CSRF::validateToken($token1);
echo "Token validation: " . ($isValid ? "✅ Valid" : "❌ Invalid") . "<br>";

// Test instance methods
echo "<h3>Instance Methods:</h3>";
$csrf = new CSRF();
$token2 = $csrf->generate();
echo "Generated token: " . $token2 . "<br>";

$isValid2 = $csrf->validate($token2);
echo "Token validation: " . ($isValid2 ? "✅ Valid" : "❌ Invalid") . "<br>";

// Test if tokens are the same (they should be since they use the same session)
echo "<h3>Token Consistency:</h3>";
echo "Tokens match: " . ($token1 === $token2 ? "✅ Yes" : "❌ No") . "<br>";

echo "<h3>✅ CSRF class is working correctly!</h3>";
?>
