<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired - AcadClear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .suspended-card {
            max-width: 500px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .suspended-icon {
            font-size: 80px;
            color: #e74a3b;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
        }
    </style>
</head>
<body>
    <div class="suspended-card">
        <div class="suspended-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Subscription Expired</h2>
        <p class="text-muted mt-3">
            Your university's subscription has expired. Please contact your administrator to renew.
        </p>
        <hr>
        <p class="text-muted small">
            <i class="fas fa-envelope"></i> support@acadclear.com
        </p>
        <a href="mailto:support@acadclear.com" class="btn btn-primary mt-3">
            Contact Support
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>