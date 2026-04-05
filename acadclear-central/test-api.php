
<?php
// Simple API test script

$baseUrl = 'http://localhost:8001/api';

echo "=== Testing Central App API ===\n\n";

// Test 1: Health check
echo "Test 1: Health Check\n";
$response = file_get_contents($baseUrl . '/tenants/health');
echo "Response: " . $response . "\n\n";

// Test 2: Test endpoint
echo "Test 2: Test Endpoint\n";
$response = file_get_contents($baseUrl . '/test');
echo "Response: " . $response . "\n\n";

// Test 3: Tenant status (replace with your actual tenant slug)
$slug = 'test-university'; // Change this to your tenant slug
echo "Test 3: Tenant Status for '$slug'\n";
$response = file_get_contents($baseUrl . '/tenants/' . $slug . '/status');
echo "Response: " . $response . "\n\n";

echo "=== Test Complete ===\n";