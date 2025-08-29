# 🔐 BAGIAN 9: SECURITY & ACCESS CONTROL

## 🎯 **OVERVIEW SECURITY & ACCESS CONTROL**

Bagian ini menjelaskan sistem keamanan dan kontrol akses yang diterapkan dalam aplikasi pinjaman dan billing. Sistem ini memastikan bahwa hanya user yang berwenang yang dapat mengakses fitur dan data tertentu sesuai dengan role dan permission yang dimiliki.

---

## 🛡️ **9.1 AUTHENTICATION SYSTEM**

### **Laravel Sanctum Implementation**:
```php
/**
 * Authentication controller dengan Laravel Sanctum
 */
class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif'
            ], 403);
        }
        
        // Check if user is locked
        if ($user->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Akun terkunci karena terlalu banyak percobaan login gagal'
            ], 423);
        }
        
        // Generate token
        $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;
        
        // Log successful login
        $this->logLoginActivity($user, $request);
        
        // Reset failed login attempts
        $user->update(['failed_login_attempts' => 0]);
        
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'role']),
                'token' => $token,
                'permissions' => $user->getAllPermissions()->pluck('name')
            ]
        ]);
    }
    
    /**
     * User logout
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Log logout activity
        $this->logLogoutActivity($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
    
    /**
     * Log login activity
     */
    private function logLoginActivity($user, $request)
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'User login berhasil',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Log logout activity
     */
    private function logLogoutActivity($user)
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => 'User logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}
```

### **Password Security**:
```php
/**
 * Password security service
 */
class PasswordSecurityService
{
    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password)
    {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter';
        }
        
        // Must contain uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password harus mengandung huruf besar';
        }
        
        // Must contain lowercase
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password harus mengandung huruf kecil';
        }
        
        // Must contain number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung angka';
        }
        
        // Must contain special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung karakter khusus';
        }
        
        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password terlalu umum, gunakan password yang lebih unik';
        }
        
        return $errors;
    }
    
    /**
     * Check if password is common
     */
    private function isCommonPassword($password)
    {
        $commonPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];
        
        return in_array(strtolower($password), $commonPasswords);
    }
    
    /**
     * Hash password with additional salt
     */
    public function hashPassword($password)
    {
        // Generate unique salt
        $salt = bin2hex(random_bytes(32));
        
        // Hash with salt
        $hashedPassword = hash('sha256', $password . $salt);
        
        return [
            'hash' => $hashedPassword,
            'salt' => $salt
        ];
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash, $salt)
    {
        $hashedInput = hash('sha256', $password . $salt);
        return hash_equals($hash, $hashedInput);
    }
}
```

---

## 🔑 **9.2 ROLE-BASED ACCESS CONTROL (RBAC)**

### **Role and Permission System**:
```php
/**
 * Role and permission management
 */
class RolePermissionService
{
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $user = User::with(['roles.permissions'])->find($userId);
        
        if (!$user) {
            return [];
        }
        
        $permissions = collect();
        
        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        
        return $permissions->unique('name')->pluck('name')->toArray();
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission)
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions);
    }
    
    /**
     * Check if user has any of the permissions
     */
    public function hasAnyPermission($userId, $permissions)
    {
        $userPermissions = $this->getUserPermissions($userId);
        
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has all permissions
     */
    public function hasAllPermissions($userId, $permissions)
    {
        $userPermissions = $this->getUserPermissions($userId);
        
        foreach ($permissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($roleName)
    {
        return User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleName)
    {
        $user = User::find($userId);
        $role = Role::where('name', $roleName)->first();
        
        if (!$user || !$role) {
            return false;
        }
        
        $user->roles()->syncWithoutDetaching([$role->id]);
        
        // Log role assignment
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'assign_role',
            'description' => "Role {$roleName} diberikan kepada user {$user->name}",
            'created_at' => now()
        ]);
        
        return true;
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleName)
    {
        $user = User::find($userId);
        $role = Role::where('name', $roleName)->first();
        
        if (!$user || !$role) {
            return false;
        }
        
        $user->roles()->detach($role->id);
        
        // Log role removal
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'remove_role',
            'description' => "Role {$roleName} dihapus dari user {$user->name}",
            'created_at' => now()
        ]);
        
        return true;
    }
}
```

### **Permission Definitions**:
```php
/**
 * Permission definitions for the application
 */
class PermissionDefinitions
{
    // User Management
    const VIEW_USERS = 'view-users';
    const CREATE_USERS = 'create-users';
    const EDIT_USERS = 'edit-users';
    const DELETE_USERS = 'delete-users';
    
    // Role Management
    const VIEW_ROLES = 'view-roles';
    const CREATE_ROLES = 'create-roles';
    const EDIT_ROLES = 'edit-roles';
    const DELETE_ROLES = 'delete-roles';
    
    // Loan Management
    const VIEW_LOANS = 'view-loans';
    const CREATE_LOANS = 'create-loans';
    const EDIT_LOANS = 'edit-loans';
    const DELETE_LOANS = 'delete-loans';
    const APPROVE_LOANS = 'approve-loans';
    const REJECT_LOANS = 'reject-loans';
    
    // Billing Management
    const VIEW_BILLING = 'view-billing';
    const CREATE_BILLING = 'create-billing';
    const EDIT_BILLING = 'edit-billing';
    const DELETE_BILLING = 'delete-billing';
    const GENERATE_BILLING = 'generate-billing';
    
    // Payment Management
    const VIEW_PAYMENTS = 'view-payments';
    const CREATE_PAYMENTS = 'create-payments';
    const EDIT_PAYMENTS = 'edit-payments';
    const DELETE_PAYMENTS = 'delete-payments';
    
    // Report Management
    const VIEW_REPORTS = 'view-reports';
    const GENERATE_REPORTS = 'generate-reports';
    const EXPORT_REPORTS = 'export-reports';
    
    // System Management
    const VIEW_SYSTEM = 'view-system';
    const MANAGE_SYSTEM = 'manage-system';
    const VIEW_LOGS = 'view-logs';
    
    /**
     * Get all permissions
     */
    public static function getAllPermissions()
    {
        $reflection = new \ReflectionClass(self::class);
        return array_values($reflection->getConstants());
    }
    
    /**
     * Get permissions by category
     */
    public static function getPermissionsByCategory()
    {
        return [
            'User Management' => [
                self::VIEW_USERS,
                self::CREATE_USERS,
                self::EDIT_USERS,
                self::DELETE_USERS
            ],
            'Role Management' => [
                self::VIEW_ROLES,
                self::CREATE_ROLES,
                self::EDIT_ROLES,
                self::DELETE_ROLES
            ],
            'Loan Management' => [
                self::VIEW_LOANS,
                self::CREATE_LOANS,
                self::EDIT_LOANS,
                self::DELETE_LOANS,
                self::APPROVE_LOANS,
                self::REJECT_LOANS
            ],
            'Billing Management' => [
                self::VIEW_BILLING,
                self::CREATE_BILLING,
                self::EDIT_BILLING,
                self::DELETE_BILLING,
                self::GENERATE_BILLING
            ],
            'Payment Management' => [
                self::VIEW_PAYMENTS,
                self::CREATE_PAYMENTS,
                self::EDIT_PAYMENTS,
                self::DELETE_PAYMENTS
            ],
            'Report Management' => [
                self::VIEW_REPORTS,
                self::GENERATE_REPORTS,
                self::EXPORT_REPORTS
            ],
            'System Management' => [
                self::VIEW_SYSTEM,
                self::MANAGE_SYSTEM,
                self::VIEW_LOGS
            ]
        ];
    }
}
```

---

## 🚪 **9.3 MIDDLEWARE & GATE AUTHORIZATION**

### **Custom Middleware**:
```php
/**
 * Custom middleware untuk permission checking
 */
class CheckPermission
{
    /**
     * Handle permission check
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = auth()->user();
        
        if (!$user->hasPermission($permission)) {
            // Log unauthorized access attempt
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "User mencoba mengakses fitur yang memerlukan permission: {$permission}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini'
            ], 403);
        }
        
        return $next($request);
    }
}

/**
 * Middleware untuk role checking
 */
class CheckRole
{
    /**
     * Handle role check
     */
    public function handle($request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = auth()->user();
        
        if (!$user->hasRole($role)) {
            // Log unauthorized access attempt
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "User mencoba mengakses fitur yang memerlukan role: {$role}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki role yang diperlukan untuk mengakses fitur ini'
            ], 403);
        }
        
        return $next($request);
    }
}

/**
 * Middleware untuk data ownership
 */
class CheckDataOwnership
{
    /**
     * Handle data ownership check
     */
    public function handle($request, Closure $next, $model, $idField = 'id')
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = auth()->user();
        $dataId = $request->route($idField);
        
        // Get model instance
        $modelClass = "App\\Models\\{$model}";
        $data = $modelClass::find($dataId);
        
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        
        // Check if user owns the data or has admin role
        if ($data->user_id !== $user->id && !$user->hasRole('admin')) {
            // Log unauthorized access attempt
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_data_access',
                'description' => "User mencoba mengakses data {$model} ID {$dataId} yang bukan miliknya",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data ini'
            ], 403);
        }
        
        return $next($request);
    }
}
```

### **Gate Authorization**:
```php
/**
 * Gate authorization definitions
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services
     */
    public function boot()
    {
        $this->registerPolicies();
        
        // Define gates
        Gate::define('view-users', function ($user) {
            return $user->hasPermission('view-users');
        });
        
        Gate::define('create-users', function ($user) {
            return $user->hasPermission('create-users');
        });
        
        Gate::define('edit-users', function ($user) {
            return $user->hasPermission('edit-users');
        });
        
        Gate::define('delete-users', function ($user) {
            return $user->hasPermission('delete-users');
        });
        
        Gate::define('approve-loans', function ($user) {
            return $user->hasPermission('approve-loans');
        });
        
        Gate::define('reject-loans', function ($user) {
            return $user->hasPermission('reject-loans');
        });
        
        Gate::define('generate-billing', function ($user) {
            return $user->hasPermission('generate-billing');
        });
        
        Gate::define('view-reports', function ($user) {
            return $user->hasPermission('view-reports');
        });
        
        Gate::define('export-reports', function ($user) {
            return $user->hasPermission('export-reports');
        });
        
        Gate::define('manage-system', function ($user) {
            return $user->hasPermission('manage-system');
        });
        
        // Data ownership gates
        Gate::define('view-own-loan', function ($user, $loan) {
            return $user->id === $loan->anggota_id || $user->hasRole('admin');
        });
        
        Gate::define('edit-own-loan', function ($user, $loan) {
            return $user->id === $loan->anggota_id || $user->hasRole('admin');
        });
        
        Gate::define('view-own-billing', function ($user, $billing) {
            return $user->no_ktp === $billing->no_ktp || $user->hasRole('admin');
        });
        
        Gate::define('pay-own-billing', function ($user, $billing) {
            return $user->no_ktp === $billing->no_ktp;
        });
    }
}
```

---

## 📝 **9.4 AUDIT TRAIL & LOGGING**

### **Activity Logging System**:
```php
/**
 * Activity logging service
 */
class ActivityLogService
{
    /**
     * Log user activity
     */
    public function log($userId, $action, $description, $data = null)
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'data' => $data ? json_encode($data) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Log loan approval
     */
    public function logLoanApproval($userId, $loanId, $loanData)
    {
        return $this->log($userId, 'loan_approval', "Pinjaman ID {$loanId} disetujui", [
            'loan_id' => $loanId,
            'loan_data' => $loanData,
            'approval_date' => now()
        ]);
    }
    
    /**
     * Log loan rejection
     */
    public function logLoanRejection($userId, $loanId, $reason)
    {
        return $this->log($userId, 'loan_rejection', "Pinjaman ID {$loanId} ditolak", [
            'loan_id' => $loanId,
            'rejection_reason' => $reason,
            'rejection_date' => now()
        ]);
    }
    
    /**
     * Log billing generation
     */
    public function logBillingGeneration($userId, $month, $year, $count)
    {
        return $this->log($userId, 'billing_generation', "Generate billing untuk {$month}/{$year}", [
            'month' => $month,
            'year' => $year,
            'billing_count' => $count,
            'generation_date' => now()
        ]);
    }
    
    /**
     * Log payment
     */
    public function logPayment($userId, $paymentId, $amount, $type)
    {
        return $this->log($userId, 'payment', "Pembayaran {$type} sebesar Rp " . number_format($amount, 0, ',', '.'), [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'payment_type' => $type,
            'payment_date' => now()
        ]);
    }
    
    /**
     * Log data deletion
     */
    public function logDataDeletion($userId, $model, $modelId, $reason)
    {
        return $this->log($userId, 'data_deletion', "Data {$model} ID {$modelId} dihapus", [
            'model' => $model,
            'model_id' => $modelId,
            'deletion_reason' => $reason,
            'deletion_date' => now()
        ]);
    }
    
    /**
     * Get user activity log
     */
    public function getUserActivityLog($userId, $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get system activity log
     */
    public function getSystemActivityLog($filters = [], $limit = 100)
    {
        $query = ActivityLog::with('user');
        
        // Apply filters
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

---

## 🔒 **9.5 DATA ENCRYPTION & PROTECTION**

### **Data Encryption Service**:
```php
/**
 * Data encryption service
 */
class DataEncryptionService
{
    private $key;
    private $cipher;
    
    public function __construct()
    {
        $this->key = config('app.encryption_key');
        $this->cipher = 'AES-256-CBC';
    }
    
    /**
     * Encrypt sensitive data
     */
    public function encrypt($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decrypt($encryptedData)
    {
        if (empty($encryptedData)) {
            return $encryptedData;
        }
        
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
    
    /**
     * Encrypt KTP number
     */
    public function encryptKTP($ktp)
    {
        return $this->encrypt($ktp);
    }
    
    /**
     * Decrypt KTP number
     */
    public function decryptKTP($encryptedKTP)
    {
        return $this->decrypt($encryptedKTP);
    }
    
    /**
     * Encrypt bank account number
     */
    public function encryptBankAccount($accountNumber)
    {
        return $this->encrypt($accountNumber);
    }
    
    /**
     * Decrypt bank account number
     */
    public function decryptBankAccount($encryptedAccount)
    {
        return $this->decrypt($encryptedAccount);
    }
}
```

---

## 🚀 **KESIMPULAN BAGIAN 9**

Bagian 9 ini telah mencakup secara lengkap:

✅ **Authentication System** - Laravel Sanctum dan password security
✅ **Role-Based Access Control** - RBAC system dengan roles dan permissions
✅ **Middleware & Gate Authorization** - Custom middleware dan gate definitions
✅ **Audit Trail & Logging** - Comprehensive activity logging system
✅ **Data Encryption & Protection** - Encryption service untuk data sensitif

**Next Step**: Lanjut ke Bagian 10 untuk Testing & Quality Assurance.

