<?php
session_start();
include("../config/db.php");
$error = "";
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields!";
    } else {
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password' AND status='Active'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Activity log
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action_type) VALUES (?, 'Login Success')");
            $log_stmt->bind_param("i", $user['user_id']);
            $log_stmt->execute();
            $log_stmt->close();

            header("Location: ../dashboard/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digital Mini-Mart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e2937 100%);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: "";
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            top: -300px;
            left: -300px;
            border-radius: 50%;
            z-index: 0;
        }
body::after {
            content: "";
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.12) 0%, transparent 70%);
            bottom: -200px;
            right: -200px;
            border-radius: 50%;
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }
        .login-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            transition: all 0.4s ease;
        }
        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.6);
        }
        .logo-section {
            margin-bottom: 35px;
        }
        .logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #3b82f6, #f97316);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .logo i {
            font-size: 42px;
            color: white;
        }
        .system-title {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .system-subtitle {
            color: #94a3b8;
            font-size: 15px;
            font-weight: 500;
        }
        .error-box {
            background: rgba(248, 113, 113, 0.15);
            border: 1px solid rgba(248, 113, 113, 0.4);
            color: #f87171;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14.5px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 18px;
        }
        .login-form input {
            width: 100%;
            padding: 16px 18px 16px 52px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(51, 65, 85, 0.6);
            color: #f1f5f9;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .login-form input:focus {
            outline: none;
            border-color: #3b82f6;
            background: rgba(51, 65, 85, 0.9);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .login-form input::placeholder {
            color: #94a3b8;
        }
        .login-btn {
            padding: 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(90deg, #3b82f6, #f97316);
            color: white;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        }
        .footer {
            margin-top: 35px;
            color: #64748b;
            font-size: 13.5px;
        }
        .footer a {
            color: #60a5fa;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 25px;
            }
            .system-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1 class="system-title">Digital Mini-Mart</h1>
                <p class="system-subtitle">Inventory Management System</p>
            </div>
<?php if (!empty($error)): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form class="login-form" method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           name="username" 
                           placeholder="Username" 
                           required 
                           autocomplete="username">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           name="password" 
                           placeholder="Password" 
                           required 
                           autocomplete="current-password">
                </div>
                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            <div class="footer">
                © <?php echo date("Y"); ?> Digital Mini-Mart • Nairobi, Kenya
            </div>
        </div>
    </div>
</body>
</html>