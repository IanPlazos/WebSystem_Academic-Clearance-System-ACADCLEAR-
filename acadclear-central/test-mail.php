<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from AcadClear with new Gmail App Password', function($msg) {
        $msg->to('christiandavepombo@gmail.com')
            ->subject('AcadClear SMTP Test - Gmail App Password');
    });
    
    echo "✓ Email sent successfully!\n";
} catch (Exception $e) {
    echo "✗ Email failed: " . $e->getMessage() . "\n";
}
