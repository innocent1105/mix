<?php
session_start();
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: modules/requests/view_requests.php");
    exit();
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    if (!empty($email) && !empty($password)) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['ministry_id'] = $user['ministry_id'];
            $_SESSION['user_role'] = $user['role'];

            // store ministry name
            $ministry_stmt = $pdo->prepare("SELECT name FROM ministry WHERE id = ?");
            $ministry_stmt->execute([$user['ministry_id']]);
            $ministry = $ministry_stmt->fetch();
            $_SESSION['ministry_name'] = $ministry['name'];
            
            // Log the login activity
            $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$user['id'], 'login', 'User logged in successfully']);
           

            echo $user['role'];
            $role = $user['role'];
            
            if(empty($role) || $role == " "){
                header("Location: dashboard.php");
            }

            if($role == "admin"){
                header("Location: admin_dashboard.php");
            }else{
                header("Location: dashboard.php");
            }

            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please fill in all fields";
    }
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $ministry_id = trim($_POST['ministry_id']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($name) || empty($lastname) || empty($ministry_id) || empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered";
        } else {
            // Create new user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Default role
            $full_name = $name . ' ' . $lastname;
            $stmt = $pdo->prepare("INSERT INTO user (ministry_id, name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$ministry_id, $full_name, $email, $password_hash, $role])) {
                $success = "Account created successfully. Please login.";
                // Clear form values
                unset($_POST);
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    if (!empty($email)) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id, name FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // In a real application, you would generate a reset token and send an email
            $success = "If this email exists in our system, you will receive a password reset link shortly.";
            // Log the password reset request
            $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$user['id'], 'password_reset_request', 'User requested password reset']);
        } else {
            // For security reasons, don't reveal if the email exists or not
            $success = "If this email exists in our system, you will receive a password reset link shortly.";
        }
    } else {
        $error = "Please enter your email address";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Government Data Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .auth-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .auth-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-left::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .auth-right {
            flex: 1.2;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--primary);
            font-weight: bold;
            font-size: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .auth-tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--secondary);
            position: relative;
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .auth-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .input-with-icon .form-input {
            padding-left: 3rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: var(--dark);
            width: 100%;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        .btn-link {
            color: var(--primary);
            background: none;
            padding: 0;
            font-weight: 500;
        }
        
        .btn-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .alert-icon {
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: var(--secondary);
            font-size: 0.875rem;
        }
        
        .demo-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid var(--primary);
        }
        
        .demo-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark);
        }
        
        .demo-account {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .demo-account:last-child {
            border-bottom: none;
        }
        
        .demo-label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .demo-email {
            color: var(--primary);
            font-family: monospace;
        }
        
        .demo-password {
            color: var(--danger);
            font-family: monospace;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .step.active {
            background: var(--primary);
            color: white;
        }
        
        .step-line {
            flex: 1;
            height: 2px;
            background: #e9ecef;
            margin: 0 0.5rem;
            position: relative;
            top: 14px;
            max-width: 40px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-width: 100%;
            }
            
            .auth-left {
                display: none;
            }
            
            .auth-right {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="logo">
                <div class="logo-icon">ML</div>
                <div class="logo-text">MinistryLink</div>
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Government Data Exchange Portal</h1>
            <p style="margin-bottom: 2rem; opacity: 0.9;">Securely connect and share information across government ministries and departments.</p>
            <div style="margin-top: 2rem;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i data-lucide="shield" style="margin-right: 0.75rem;"></i>
                    <span>End-to-end encryption</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i data-lucide="users" style="margin-right: 0.75rem;"></i>
                    <span>Inter-ministerial collaboration</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <i data-lucide="bar-chart" style="margin-right: 0.75rem;"></i>
                    <span>Real-time data analytics</span>
                </div>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-tabs">
                <div class="auth-tab active" data-tab="login">Login</div>
                <div class="auth-tab" data-tab="signup">Register</div>
                <div class="auth-tab" data-tab="forgot" style="display: none;">Reset Password</div>
            </div>
            
            <!-- Error/Success Messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i data-lucide="alert-circle" class="alert-icon"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle" class="alert-icon"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="" class="auth-form active" id="login-form">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-with-icon">
                        <i data-lucide="mail" class="input-icon"></i>
                        <input type="email" name="email" class="form-input" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) && !isset($_POST['signup']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-with-icon">
                        <i data-lucide="lock" class="input-icon"></i>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 0.5rem;">
                        <span>Remember me</span>
                    </label>
                    <button type="button" class="btn-link" id="show-forgot">Forgot password?</button>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span>Sign In</span>
                    <i data-lucide="arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
                
                <div class="demo-box">
                    <div class="demo-title">Demo Access Credentials</div>
                    <div class="demo-account">
                        <div class="demo-label">Admin Account:</div>
                        <div class="demo-email">admin@ministry-exchange.demo</div>
                        <div class="demo-password">password</div>
                    </div>
                    <div class="demo-account">
                        <div class="demo-label">MOE Account:</div>
                        <div class="demo-email">zulu@gmail.com</div>
                        <div class="demo-password">zulu@2025</div>
                    </div>
                    <div class="demo-account">
                        <div class="demo-label">MOH Account:</div>
                        <div class="demo-email">ezra@gmail.com</div>
                        <div class="demo-password">ezra@2025</div>
                    </div>
                    <div class="demo-account">
                        <div class="demo-label">MOFA Account:</div>
                        <div class="demo-email">ethan@gmail.com</div>
                        <div class="demo-password">ethan@2025</div>
                    </div>
                    <div class="demo-account">
                        <div class="demo-label">MOHA Account:</div>
                        <div class="demo-email">james@gmail.com</div>
                        <div class="demo-password">james@2025</div>
                    </div>
                </div>
            </form>
            
            <!-- Sign Up Form -->
            <form method="POST" action="" class="auth-form" id="signup-form">
                <input type="hidden" name="signup" value="1">
                
                <div class="step-indicator">
                    <div class="step active" data-step="1">1</div>
                    <div class="step-line"></div>
                    <div class="step" data-step="2">2</div>
                </div>
                
                <div class="form-step active" data-step="1">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <div class="input-with-icon">
                            <i data-lucide="user" class="input-icon"></i>
                            <input type="text" name="name" class="form-input" placeholder="Enter your first name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <div class="input-with-icon">
                            <i data-lucide="user" class="input-icon"></i>
                            <input type="text" name="lastname" class="form-input" placeholder="Enter your last name" required value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ministry/Department</label>
                        <div class="input-with-icon">
                            <i data-lucide="building" class="input-icon"></i>
                            <select name="ministry_id" class="form-input" required>
                                <option value="">Select your ministry/department</option>
                                <?php
                                $ministries = $pdo->query("SELECT * FROM ministry ORDER BY name");
                                while ($ministry = $ministries->fetch()) {
                                    $selected = (isset($_POST['ministry_id']) && $_POST['ministry_id'] == $ministry['id']) ? 'selected' : '';
                                    echo "<option value='{$ministry['id']}' $selected>{$ministry['name']} ({$ministry['abbreviation']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary" id="next-step">Continue</button>
                </div>
                
                <div class="form-step" data-step="2">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-with-icon">
                            <i data-lucide="mail" class="input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="Enter your work email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-with-icon">
                            <i data-lucide="lock" class="input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="Create a password" required id="signup-password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-with-icon">
                            <i data-lucide="lock" class="input-icon"></i>
                            <input type="password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                        <input type="checkbox" id="terms" required style="margin-right: 0.5rem;">
                        <label for="terms">I agree to the <a href="#" style="color: var(--primary);">Terms of Service</a> and <a href="#" style="color: var(--primary);">Privacy Policy</a></label>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" class="btn btn-secondary" id="prev-step">Back</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </div>
                
                <div class="form-footer">
                    Already have an account? <button type="button" class="btn-link" data-tab="login">Sign in</button>
                </div>
            </form>
            
            <!-- Forgot Password Form -->
            <form method="POST" action="" class="auth-form" id="forgot-form">
                <input type="hidden" name="reset_password" value="1">
                
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 60px; height: 60px; background: rgba(67, 97, 238, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i data-lucide="key" style="color: var(--primary);"></i>
                    </div>
                    <h2 style="font-weight: 600; margin-bottom: 0.5rem;">Reset Password</h2>
                    <p style="color: var(--secondary);">Enter your email address and we'll send you a reset link</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-with-icon">
                        <i data-lucide="mail" class="input-icon"></i>
                        <input type="email" name="email" class="form-input" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-bottom: 1.5rem;">
                    <span>Send Reset Link</span>
                    <i data-lucide="send" style="margin-left: 0.5rem;"></i>
                </button>
                
                <div class="form-footer">
                    Remember your password? <button type="button" class="btn-link" data-tab="login">Back to login</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Tab switching functionality
        const tabs = document.querySelectorAll('.auth-tab');
        const forms = document.querySelectorAll('.auth-form');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.getAttribute('data-tab');
                
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Show corresponding form
                forms.forEach(form => form.classList.remove('active'));
                document.getElementById(`${tabName}-form`).classList.add('active');
            });
        });
        
        // Show forgot password form
        document.getElementById('show-forgot').addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(form => form.classList.remove('active'));
            
            // Create a temporary tab for forgot password
            const forgotTab = document.querySelector('.auth-tab[data-tab="forgot"]');
            forgotTab.style.display = 'block';
            forgotTab.classList.add('active');
            document.getElementById('forgot-form').classList.add('active');
        });
        
        // Back to login from other tabs
        document.querySelectorAll('.btn-link[data-tab="login"]').forEach(btn => {
            btn.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('active');
                    if (t.getAttribute('data-tab') === 'forgot') t.style.display = 'none';
                });
                document.querySelector('.auth-tab[data-tab="login"]').classList.add('active');
                
                forms.forEach(form => form.classList.remove('active'));
                document.getElementById('login-form').classList.add('active');
            });
        });
        
        // Multi-step form functionality for signup
        const nextStepBtn = document.getElementById('next-step');
        const prevStepBtn = document.getElementById('prev-step');
        const steps = document.querySelectorAll('.form-step');
        const stepIndicators = document.querySelectorAll('.step');
        
        if (nextStepBtn) {
            nextStepBtn.addEventListener('click', () => {
                // Basic validation
                const firstName = document.querySelector('input[name="name"]');
                const lastName = document.querySelector('input[name="lastname"]');
                const ministry = document.querySelector('select[name="ministry_id"]');
                
                let isValid = true;
                
                if (!firstName.value.trim()) {
                    firstName.style.borderColor = 'var(--danger)';
                    isValid = false;
                }
                
                if (!lastName.value.trim()) {
                    lastName.style.borderColor = 'var(--danger)';
                    isValid = false;
                }
                
                if (!ministry.value) {
                    ministry.style.borderColor = 'var(--danger)';
                    isValid = false;
                }
                
                if (isValid) {
                    // Move to next step
                    steps.forEach(step => step.classList.remove('active'));
                    document.querySelector('.form-step[data-step="2"]').classList.add('active');
                    
                    stepIndicators.forEach(step => step.classList.remove('active'));
                    document.querySelector('.step[data-step="2"]').classList.add('active');
                }
            });
        }
        
        if (prevStepBtn) {
            prevStepBtn.addEventListener('click', () => {
                // Move to previous step
                steps.forEach(step => step.classList.remove('active'));
                document.querySelector('.form-step[data-step="1"]').classList.add('active');
                
                stepIndicators.forEach(step => step.classList.remove('active'));
                document.querySelector('.step[data-step="1"]').classList.add('active');
            });
        }
        
        // Form validation for signup
        const signupForm = document.getElementById('signup-form');
        if (signupForm) {
            signupForm.addEventListener('submit', function(e) {
                const password = document.getElementById('signup-password');
                const confirmPassword = document.querySelector('input[name="confirm_password"]');
                const terms = document.getElementById('terms');
                
                let isValid = true;
                
                if (password.value.length < 6) {
                    password.style.borderColor = 'var(--danger)';
                    isValid = false;
                    alert('Password must be at least 6 characters long');
                }
                
                if (password.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = 'var(--danger)';
                    isValid = false;
                    alert('Passwords do not match');
                }
                
                if (!terms.checked) {
                    isValid = false;
                    alert('You must agree to the terms and conditions');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
        
        // Check if we should show a specific form based on POST data
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])): ?>
            document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector('.auth-tab[data-tab="signup"]').classList.add('active');
            
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            document.getElementById('signup-form').classList.add('active');
            
            // Show second step if there were errors
            steps.forEach(step => step.classList.remove('active'));
            document.querySelector('.form-step[data-step="2"]').classList.add('active');
            
            stepIndicators.forEach(step => step.classList.remove('active'));
            document.querySelector('.step[data-step="2"]').classList.add('active');
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])): ?>
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('data-tab') === 'forgot') tab.style.display = 'block';
            });
            document.querySelector('.auth-tab[data-tab="forgot"]').classList.add('active');
            
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            document.getElementById('forgot-form').classList.add('active');
        <?php endif; ?>
    </script>
</body>
</html>