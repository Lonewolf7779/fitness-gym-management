# IRONCORE Gym Management System - Comprehensive Acceptance Test Suite
# Tests All Roles, Portals, All Routes, APIs, Role Security, and Live Dynamic Data Integrity

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

# =========================================================================
# 1. Public Frontend Pages
# =========================================================================
Write-Host "`n--- 1. Testing Public Frontend Pages ---" -ForegroundColor Yellow
$publicPages = @("/index.php", "/login.php", "/register.php")
foreach ($page in $publicPages) {
    try {
        $res = Invoke-WebRequest -Uri "$baseUrl$page" -Method Get -UseBasicParsing
        Assert-Condition ($res.StatusCode -eq 200) "GET $page returns 200 OK"
    } catch {
        Assert-Condition $false "GET $page failed: $_"
    }
}

# =========================================================================
# 2. Admin Authentication & Dashboard
# =========================================================================
Write-Host "`n--- 2. Testing Admin Authentication & Dashboard ---" -ForegroundColor Yellow
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($loginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $adminLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $loginBody = @{
        email = "admin@ironcore.com"
        password = "Admin@123"
        csrf_token = $adminLoginCsrf
    }
    $loginRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $loginBody -WebSession $adminSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue

    $dashRes = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $adminSession -UseBasicParsing
    Assert-Condition ($dashRes.StatusCode -eq 200 -and $dashRes.Content.Contains("OPERATIONAL CONTROL CENTER")) "Admin Login & Dashboard Accessible"
} catch {
    Assert-Condition $false "Admin authentication flow failed: $_"
}

# =========================================================================
# 3. Admin All Management Module Views
# =========================================================================
Write-Host "`n--- 3. Testing Admin Module Views ---" -ForegroundColor Yellow
$adminViews = @(
    @{ path = "/admin/members.php"; match = "MEMBER DIRECTORY" },
    @{ path = "/admin/trainers.php"; match = "TRAINER" },
    @{ path = "/admin/memberships.php"; match = "MEMBERSHIP PLANS" },
    @{ path = "/admin/attendance.php"; match = "ATTENDANCE CONTROL CENTER" },
    @{ path = "/admin/payments.php"; match = "FINANCIAL" },
    @{ path = "/admin/workouts.php"; match = "WORKOUT MANAGEMENT CENTER" },
    @{ path = "/admin/reports.php"; match = "EXECUTIVE INTELLIGENCE" },
    @{ path = "/admin/settings.php"; match = "SYSTEM SETTINGS" }
)

foreach ($view in $adminViews) {
    try {
        $res = Invoke-WebRequest -Uri "$baseUrl$($view.path)" -WebSession $adminSession -UseBasicParsing
        Assert-Condition ($res.StatusCode -eq 200 -and $res.Content.Contains($view.match)) "GET $($view.path) renders correctly"
    } catch {
        Assert-Condition $false "GET $($view.path) failed: $_"
    }
}

# =========================================================================
# 4. Admin APIs & Telemetry
# =========================================================================
Write-Host "`n--- 4. Testing Admin APIs & Telemetry ---" -ForegroundColor Yellow
try {
    $statsRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=dashboard_stats" -WebSession $adminSession -UseBasicParsing
    $stats = $statsRes.Content | ConvertFrom-Json
    Assert-Condition ($stats.success -eq $true -and $stats.data.total_members -ge 1) "GET /api.php?action=dashboard_stats returns valid live metrics"

    $woStatsRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=workout_stats" -WebSession $adminSession -UseBasicParsing
    $woStats = $woStatsRes.Content | ConvertFrom-Json
    Assert-Condition ($woStats.success -eq $true -and $woStats.data.total_programs -ge 1) "GET /api.php?action=workout_stats returns valid workout metrics"

    $repRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=reports" -WebSession $adminSession -UseBasicParsing
    $rep = $repRes.Content | ConvertFrom-Json
    Assert-Condition ($rep.success -eq $true -and $rep.data.members -ge 1) "GET /api.php?action=reports returns valid intelligence metrics"
} catch {
    Assert-Condition $false "Admin API endpoints failed: $_"
}

# =========================================================================
# 5. Admin Dynamic Member CRUD & Attendance Flow
# =========================================================================
Write-Host "`n--- 5. Testing Member CRUD & Check-In Flow ---" -ForegroundColor Yellow
$testEmail = "testathlete_" + (Get-Random) + "@example.com"
$createdMemberId = 0

try {
    # Obtain CSRF
    $membersPage = Invoke-WebRequest -Uri "$baseUrl/admin/members.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($membersPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $membersCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Get available plan
    $plansRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=plans" -WebSession $adminSession -UseBasicParsing
    $plansJson = $plansRes.Content | ConvertFrom-Json
    $planId = if ($plansJson.data.Count -gt 0) { $plansJson.data[0].id } else { 1 }

    # Create Member
    $createBody = @{
        csrf_token = $membersCsrf
        name = "Test Dynamic Athlete"
        email = $testEmail
        phone = "+91 99887 76655"
        plan_id = "$planId"
        status = "active"
        start_date = (Get-Date).ToString("yyyy-MM-dd")
        password = "Member@123"
    }
    $createRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_member" -Method Post -Body $createBody -WebSession $adminSession -UseBasicParsing
    $createJson = $createRes.Content | ConvertFrom-Json
    $createdMemberId = $createJson.id
    Assert-Condition ($createJson.success -eq $true -and $createdMemberId -gt 0) "POST /api.php?action=create_member creates new member"

    # Search Member
    $searchRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=members&q=Test+Dynamic" -WebSession $adminSession -UseBasicParsing
    $searchJson = $searchRes.Content | ConvertFrom-Json
    Assert-Condition ($searchJson.success -eq $true -and $searchJson.data.Count -ge 1) "GET /api.php?action=members finds newly created member"

    # Check In Member
    $checkInBody = @{
        csrf_token = $membersCsrf
        member_id = "$createdMemberId"
        status = "present"
    }
    $checkInRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=check_in" -Method Post -Body $checkInBody -WebSession $adminSession -UseBasicParsing
    $checkInJson = $checkInRes.Content | ConvertFrom-Json
    Assert-Condition ($checkInJson.success -eq $true) "POST /api.php?action=check_in records athlete check-in"

    # Check Out Member
    $checkOutBody = @{
        csrf_token = $membersCsrf
        member_id = "$createdMemberId"
    }
    $checkOutRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=check_out_member" -Method Post -Body $checkOutBody -WebSession $adminSession -UseBasicParsing
    $checkOutJson = $checkOutRes.Content | ConvertFrom-Json
    Assert-Condition ($checkOutJson.success -eq $true) "POST /api.php?action=check_out_member checks out athlete"
} catch {
    Assert-Condition $false "Member CRUD and attendance flow failed: $_"
}

# =========================================================================
# 6. Trainer Authentication & Portal Views
# =========================================================================
Write-Host "`n--- 6. Testing Trainer Portal & Program Management ---" -ForegroundColor Yellow
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

    # Trainer Views
    $trainerViews = @(
        @{ path = "/trainer/index.php"; match = "TRAINER ATHLETE HUB" },
        @{ path = "/trainer/members.php"; match = "MY ATHLETES ROSTER" },
        @{ path = "/trainer/workouts.php"; match = "WORKOUT PROGRAMS" },
        @{ path = "/trainer/profile.php"; match = "COACH PROFILE" }
    )

    foreach ($tv in $trainerViews) {
        $res = Invoke-WebRequest -Uri "$baseUrl$($tv.path)" -WebSession $trainerSession -UseBasicParsing
        Assert-Condition ($res.StatusCode -eq 200 -and $res.Content.Contains($tv.match)) "GET $($tv.path) renders correctly"
    }

    # Extract Trainer CSRF
    $trainerProfilePage = Invoke-WebRequest -Uri "$baseUrl/trainer/profile.php" -WebSession $trainerSession -UseBasicParsing
    $csrfMatch = [regex]::Match($trainerProfilePage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $trainerCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Trainer Assign Workout Routine
    $workoutBody = @{
        csrf_token = $trainerCsrf
        member_id = "1"
        title = "Dynamic Hypertrophy Protocol"
        goal = "Hypertrophy"
        difficulty = "Intermediate"
        description = "4-week progressive overload protocol"
    }
    $woAssignRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_workout_plan" -Method Post -Body $workoutBody -WebSession $trainerSession -UseBasicParsing
    $woAssignJson = $woAssignRes.Content | ConvertFrom-Json
    Assert-Condition ($woAssignJson.success -eq $true -and $woAssignJson.id -gt 0) "POST /api.php?action=create_workout_plan assigns workout protocol"
} catch {
    Assert-Condition $false "Trainer portal tests failed: $_"
}

# =========================================================================
# 7. Member Authentication & Portal Views
# =========================================================================
Write-Host "`n--- 7. Testing Member Portal & Self-Service ---" -ForegroundColor Yellow
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

    # Member Views
    $memberViews = @(
        @{ path = "/member/index.php"; match = "ATHLETE PERFORMANCE HUB" },
        @{ path = "/member/workout.php"; match = "MY WORKOUT PROTOCOL" },
        @{ path = "/member/progress.php"; match = "BODY COMPOSITION" },
        @{ path = "/member/attendance.php"; match = "ATTENDANCE" },
        @{ path = "/member/profile.php"; match = "ATHLETE PROFILE" }
    )

    foreach ($mv in $memberViews) {
        $res = Invoke-WebRequest -Uri "$baseUrl$($mv.path)" -WebSession $memberSession -UseBasicParsing
        Assert-Condition ($res.StatusCode -eq 200 -and $res.Content.Contains($mv.match)) "GET $($mv.path) renders correctly"
    }

    # Extract Member CSRF
    $memberProfilePage = Invoke-WebRequest -Uri "$baseUrl/member/profile.php" -WebSession $memberSession -UseBasicParsing
    $csrfMatch = [regex]::Match($memberProfilePage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $memberCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Member Self Check-In
    $selfCheckinBody = @{
        csrf_token = $memberCsrf
    }
    $selfCheckinRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=member_self_checkin" -Method Post -Body $selfCheckinBody -WebSession $memberSession -UseBasicParsing
    $selfCheckinJson = $selfCheckinRes.Content | ConvertFrom-Json
    Assert-Condition ($selfCheckinJson.success -eq $true) "POST /api.php?action=member_self_checkin records member turnstile entry"

    # Member Log Progress
    $progressBody = @{
        csrf_token = $memberCsrf
        log_date = (Get-Date).ToString("yyyy-MM-dd")
        weight_kg = "78.2"
        body_fat_pct = "14.0"
        chest_cm = "104.5"
        waist_cm = "81.5"
        arms_cm = "38.5"
        notes = "Acceptance test verified biometric checkpoint"
    }
    $progressRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=member_add_progress" -Method Post -Body $progressBody -WebSession $memberSession -UseBasicParsing
    $progressJson = $progressRes.Content | ConvertFrom-Json
    Assert-Condition ($progressJson.success -eq $true -and $progressJson.id -gt 0) "POST /api.php?action=member_add_progress logs body metrics"
} catch {
    Assert-Condition $false "Member portal tests failed: $_"
}

# =========================================================================
# 8. Role Security & Access Control Enforcement
# =========================================================================
Write-Host "`n--- 8. Testing Role Security & Access Control ---" -ForegroundColor Yellow
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

    # Unauthenticated trying to access /admin/index.php -> blocked
    $unauthSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $unauthAccess = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $unauthSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Assert-Condition ($unauthAccess.StatusCode -ne 200 -or $unauthAccess.Headers.Location -ne $null) "Unauthenticated user blocked from /admin/index.php"
} catch {
    Assert-Condition $false "Security enforcement test failed: $_"
}

# =========================================================================
# Final Summary
# =========================================================================
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
