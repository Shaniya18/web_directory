<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Fiji Web Directory</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .staff-login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .staff-login-container h1 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #34495e;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: left;
        }
        .logo {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .security-notice {
            margin-top: 1.5rem;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.8rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="staff-login-container">
        <div class="logo">🔒</div>
        <h1>Staff Portal Access</h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>Staff ID</label>
                <input type="text" name="username" placeholder="Enter your staff ID" required>
            </div>
            
            <div class="form-group">
                <label>Access Code</label>
                <input type="password" name="password" placeholder="Enter access code" required>
            </div>
            
            <button type="submit" class="btn">Access System</button>
        </form>
        
        <div class="security-notice">
            ⚠️ Authorized personnel only. All access attempts are monitored and logged.
        </div>
    </div>
</body>
</html>