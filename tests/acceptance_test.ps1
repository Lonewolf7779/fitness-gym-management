# IRONCORE Gym Management System - Acceptance Test Suite
# Tests All Roles, Endpoints, APIs, Role Security, and Data Integrity

$baseUrl = "http://localhost:8000"
$errors = @()

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " IRONCORE GYM MANAGEMENT SYSTEM - ACCEPTANCE TESTS " -ForegroundColor Cyan
Write-Host "====================================================" -ForegroundColor Cyan

function Assert-Condition($condition, $testName) {
    if ($condition) {
        Write-Host " [PASS] $testName" -ForegroundColor Green
    } else {
        Write-Host " [FAIL] $testName" -ForegroundColor Red
        $script:errors += $testName
    }
}

# 1. Public Pages Availability
Write-Host "`n--- 1. Testing Public Frontend Pages ---" -ForegroundColor Yellow
$publicPages = @(
    "/index.php",
    "/login.php",
    "/register.php"
)

foreach ($page in $publicPages) {
    try {
        $res = Invoke-WebRequest -Uri "$baseUrl$page" -Method Get -UseBasicParsing
        Assert-Condition ($res.StatusCode -eq 200) "GET $page returns 200 OK"
    } catch {
        Assert-Condition $false "GET $page failed: $_"
    }
}

# 2. Admin Authentication Flow
Write-Host "`n--- 2. Testing Admin Authentication & Dashboard ---" -ForegroundColor Yellow
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    # Get Login page to obtain initial CSRF token
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($loginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $adminLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Login as Admin
    $loginBody = @{
        email = "admin@ironcore.com"
        password = "Admin@123"
        csrf_token = $adminLoginCsrf
    }
    $loginRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $loginBody -WebSession $adminSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue

    # Check redirect or session
    $dashRes = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $adminSession -UseBasicParsing
    Assert-Condition ($dashRes.StatusCode -eq 200 -and $dashRes.Content.Contains("OPERATIONAL CONTROL CENTER")) "Admin Login & Dashboard Accessible"
} catch {
    Assert-Condition $false "Admin authentication flow failed: $_"
}

# 3. Admin API Endpoints & Charts
Write-Host "`n--- 3. Testing Admin APIs & Metrics ---" -ForegroundColor Yellow
try {
    $statsRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=dashboard_stats" -WebSession $adminSession -UseBasicParsing
    $stats = $statsRes.Content | ConvertFrom-Json
    Assert-Condition ($stats.success -eq $true -and $stats.data.total_members -gt 0) "GET /api.php?action=dashboard_stats returns valid metrics"

    $revRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=chart_revenue" -WebSession $adminSession -UseBasicParsing
    $rev = $revRes.Content | ConvertFrom-Json
    Assert-Condition ($rev.success -eq $true -and $rev.data.labels.Count -eq 8) "GET /api.php?action=chart_revenue returns 8 months data"

    $attRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=chart_attendance" -WebSession $adminSession -UseBasicParsing
    $att = $attRes.Content | ConvertFrom-Json
    Assert-Condition ($att.success -eq $true -and $att.data.labels.Count -eq 7) "GET /api.php?action=chart_attendance returns 7 days data"
} catch {
    Assert-Condition $false "Admin API endpoints failed: $_"
}

# 4. Admin Member Management Flow (Create & Fetch)
Write-Host "`n--- 4. Testing Member CRUD Operations ---" -ForegroundColor Yellow
$testEmail = "testathlete_" + (Get-Random) + "@example.com"
$createdMemberId = 0

try {
    # Fetch members list page
    $membersPage = Invoke-WebRequest -Uri "$baseUrl/admin/members.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($membersPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $membersCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Create Member via API
    $createBody = @{
        csrf_token = $membersCsrf
        name = "Test Athlete Unit"
        email = $testEmail
        phone = "+91 99999 11111"
        plan_id = "2"
        status = "active"
        start_date = (Get-Date).ToString("yyyy-MM-dd")
        password = "Member@123"
    }

    $createRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_member" -Method Post -Body $createBody -WebSession $adminSession -UseBasicParsing
    $createJson = $createRes.Content | ConvertFrom-Json
    $createdMemberId = $createJson.id
    Assert-Condition ($createJson.success -eq $true -and $createdMemberId -gt 0) "POST /api.php?action=create_member successfully creates member"

    # Verify Member exists via API search
    $searchRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=members&q=Test+Athlete" -WebSession $adminSession -UseBasicParsing
    $searchJson = $searchRes.Content | ConvertFrom-Json
    Assert-Condition ($searchJson.success -eq $true -and $searchJson.data.Count -ge 1) "GET /api.php?action=members searches and finds new member"

    # Update Member
    $updateBody = @{
        csrf_token = $membersCsrf
        id = "$createdMemberId"
        name = "Test Athlete Updated"
        email = $testEmail
        phone = "+91 99999 22222"
        plan_id = "3"
        status = "active"
        emergency_contact = "+91 99999 00000"
    }
    $updateRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=update_member" -Method Post -Body $updateBody -WebSession $adminSession -UseBasicParsing
    $updateJson = $updateRes.Content | ConvertFrom-Json
    Assert-Condition ($updateJson.success -eq $true) "POST /api.php?action=update_member successfully updates member"
} catch {
    Assert-Condition $false "Member CRUD operations failed: $_"
}

# 5. Admin Trainer & Plan Operations
Write-Host "`n--- 5. Testing Trainer & Plan Management ---" -ForegroundColor Yellow
try {
    # Fetch Trainers
    $trainersRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=trainers" -WebSession $adminSession -UseBasicParsing
    $trainersJson = $trainersRes.Content | ConvertFrom-Json
    Assert-Condition ($trainersJson.success -eq $true -and $trainersJson.data.Count -gt 0) "GET /api.php?action=trainers returns trainer roster"

    # Fetch Plans
    $plansRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=plans" -WebSession $adminSession -UseBasicParsing
    $plansJson = $plansRes.Content | ConvertFrom-Json
    Assert-Condition ($plansJson.success -eq $true -and $plansJson.data.Count -ge 3) "GET /api.php?action=plans returns membership plans"
} catch {
    Assert-Condition $false "Trainer and plan operations failed: $_"
}

# 6. Admin Attendance & Payments
Write-Host "`n--- 6. Testing Attendance & Payment Records ---" -ForegroundColor Yellow
try {
    $attRecordsRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=attendance" -WebSession $adminSession -UseBasicParsing
    $attRecords = $attRecordsRes.Content | ConvertFrom-Json
    Assert-Condition ($attRecords.success -eq $true) "GET /api.php?action=attendance returns valid attendance records"

    $payRecordsRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=payments" -WebSession $adminSession -UseBasicParsing
    $payRecords = $payRecordsRes.Content | ConvertFrom-Json
    Assert-Condition ($payRecords.success -eq $true -and $payRecords.data.Count -gt 0) "GET /api.php?action=payments returns payment transactions"
} catch {
    Assert-Condition $false "Attendance and payment operations failed: $_"
}

# 7. Trainer Authentication & Portal
Write-Host "`n--- 7. Testing Trainer Portal ---" -ForegroundColor Yellow
$trainerSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    $tLoginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $trainerSession -UseBasicParsing
    $csrfMatch = [regex]::Match($tLoginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $tLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $tLoginBody = @{
        email = "marcus@ironcore.com"
        password = "Trainer@123"
        csrf_token = $tLoginCsrf
    }
    $tLoginRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $tLoginBody -WebSession $trainerSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue

    $trainerDash = Invoke-WebRequest -Uri "$baseUrl/trainer/index.php" -WebSession $trainerSession -UseBasicParsing
    Assert-Condition ($trainerDash.StatusCode -eq 200 -and $trainerDash.Content.Contains("TRAINER ATHLETE HUB")) "Trainer Login & Dashboard Accessible"
    Assert-Condition ($trainerDash.Content.Contains("Assigned Clients") -and $trainerDash.Content.Contains("Sessions This Week")) "Trainer Metrics Visible"

    # Extract Trainer CSRF
    $csrfMatch = [regex]::Match($trainerDash.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $trainerCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Trainer Workout Plan Creation
    $workoutBody = @{
        csrf_token = $trainerCsrf
        member_id = "1"
        trainer_id = "1"
        title = "Acceptance Test Hypertrophy Plan"
        goal = "Strength & Endurance"
        start_date = (Get-Date).ToString("yyyy-MM-dd")
    }
    $workoutRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_workout" -Method Post -Body $workoutBody -WebSession $trainerSession -UseBasicParsing
    $workoutJson = $workoutRes.Content | ConvertFrom-Json
    Assert-Condition ($workoutJson.success -eq $true -and $workoutJson.id -gt 0) "POST /api.php?action=create_workout assigns workout plan"
} catch {
    Assert-Condition $false "Trainer portal tests failed: $_"
}

# 8. Member Authentication & Portal
Write-Host "`n--- 8. Testing Member Portal & Self-Service ---" -ForegroundColor Yellow
$memberSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    $mLoginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $memberSession -UseBasicParsing
    $csrfMatch = [regex]::Match($mLoginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $mLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $mLoginBody = @{
        email = "alex@gmail.com"
        password = "Member@123"
        csrf_token = $mLoginCsrf
    }
    $mLoginRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $mLoginBody -WebSession $memberSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue

    $memberDash = Invoke-WebRequest -Uri "$baseUrl/member/index.php" -WebSession $memberSession -UseBasicParsing
    Assert-Condition ($memberDash.StatusCode -eq 200 -and $memberDash.Content.Contains("ATHLETE PERFORMANCE HUB")) "Member Login & Dashboard Accessible"
    Assert-Condition ($memberDash.Content.Contains("Days Remaining") -and $memberDash.Content.Contains("Active Streak")) "Member Subscription & Streak Visible"

    # Extract Member CSRF
    $csrfMatch = [regex]::Match($memberDash.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $memberCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Member Log Progress
    $progressBody = @{
        csrf_token = $memberCsrf
        log_date = (Get-Date).ToString("yyyy-MM-dd")
        weight_kg = "78.5"
        body_fat_pct = "14.2"
        chest_cm = "104"
        waist_cm = "82"
        biceps_cm = "38"
        notes = "Acceptance test verified log"
    }
    $progressRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=member_add_progress" -Method Post -Body $progressBody -WebSession $memberSession -UseBasicParsing
    $progressJson = $progressRes.Content | ConvertFrom-Json
    Assert-Condition ($progressJson.success -eq $true -and $progressJson.id -gt 0) "POST /api.php?action=member_add_progress records body metrics"
} catch {
    Assert-Condition $false "Member portal tests failed: $_"
}

# 9. Admin Settings & Reports
Write-Host "`n--- 9. Testing Admin Settings & Reports ---" -ForegroundColor Yellow
try {
    # Reports API
    $repRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=reports" -WebSession $adminSession -UseBasicParsing
    $repJson = $repRes.Content | ConvertFrom-Json
    Assert-Condition ($repJson.success -eq $true -and $repJson.data.members -ne $null) "GET /api.php?action=reports returns business metrics"

    # Settings Form Post
    $settingsPage = Invoke-WebRequest -Uri "$baseUrl/admin/settings.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($settingsPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $settingsCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $settingsBody = @{
        csrf_token = $settingsCsrf
        gym_name = "IRONCORE ELITE FITNESS"
        gym_email = "support@ironcore.com"
        gym_phone = "+91 98765 00000"
        gym_address = "42 Ironcore Boulevard, Mumbai"
        currency = "INR"
        currency_symbol = "₹"
        tax_rate = "18"
        operating_hours = "06:00 - 22:00"
    }
    $setPostRes = Invoke-WebRequest -Uri "$baseUrl/admin/settings.php" -Method Post -Body $settingsBody -WebSession $adminSession -UseBasicParsing
    Assert-Condition ($setPostRes.StatusCode -eq 200 -and $setPostRes.Content.Contains("IRONCORE ELITE FITNESS")) "POST /admin/settings.php persists gym configuration"
} catch {
    Assert-Condition $false "Admin settings and reports tests failed: $_"
}

# 10. Role-Based Security Enforcement
Write-Host "`n--- 10. Testing Role Security & Access Control ---" -ForegroundColor Yellow
try {
    # Member trying to access Admin dashboard -> should redirect / block
    $memberAccessAdmin = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $memberSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Assert-Condition ($memberAccessAdmin.StatusCode -ne 200 -or $memberAccessAdmin.Content.Contains("Access Denied") -or $memberAccessAdmin.Headers.Location -ne $null) "Member is blocked from /admin/index.php"

    # Member trying to call Admin API -> should return 403 Forbidden
    $memberApiCall = $null
    try {
        $memberApiCall = Invoke-WebRequest -Uri "$baseUrl/api.php?action=dashboard_stats" -WebSession $memberSession -UseBasicParsing
    } catch {
        $memberApiCall = $_.Exception.Response
    }
    $code = if ($memberApiCall -ne $null) { [int]$memberApiCall.StatusCode } else { 0 }
    Assert-Condition ($code -eq 403) "Member API call to admin action returns 403 Forbidden (Got: $code)"
} catch {
    Assert-Condition $false "Security enforcement test failed: $_"
}

# Summary
Write-Host "`n====================================================" -ForegroundColor Cyan
if ($errors.Count -eq 0) {
    Write-Host " ALL ACCEPTANCE TESTS PASSED SUCCESSFULLY! (0 Failures)" -ForegroundColor Green
    Write-Host "====================================================" -ForegroundColor Cyan
    exit 0
} else {
    Write-Host " ACCEPTANCE TESTS FAILED WITH $($errors.Count) ERRORS:" -ForegroundColor Red
    foreach ($err in $errors) {
        Write-Host "  - $err" -ForegroundColor Red
    }
    Write-Host "====================================================" -ForegroundColor Cyan
    exit 1
}
