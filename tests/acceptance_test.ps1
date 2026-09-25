# IRONCORE Gym Management System - Comprehensive Final Acceptance & Security Test Suite
# Portable, repeatable verification of Portals, Routes, APIs, Role Boundaries,
# Trainer Ownership Isolation, Member Isolation, CSRF, Data Integrity, and Baseline Reset.

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpBin = if (Get-Command "php" -ErrorAction SilentlyContinue) { "php" } elseif (Test-Path "$env:SystemDrive\xampp\php\php.exe") { "$env:SystemDrive\xampp\php\php.exe" } else { "php" }
$baseUrl = "http://localhost:8000"
$script:errors = @()

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

# Initial Baseline Reset to Guarantee Deterministic State
Write-Host "`n--- Initializing Baseline Database ---" -ForegroundColor Yellow
& $phpBin "$projectRoot/tests/reset_database.php"
Assert-Condition ($LASTEXITCODE -eq 0) "Pre-test database reset succeeded"

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
# 2. Member Public Registration Flow
# =========================================================================
Write-Host "`n--- 2. Testing Member Self-Registration Flow ---" -ForegroundColor Yellow
$regSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$regRandom = (Get-Random -Minimum 1000 -Maximum 9999)
$regUsername = "reguser_$regRandom"
$regEmail = "reguser_$regRandom@example.com"

try {
    $regPage = Invoke-WebRequest -Uri "$baseUrl/register.php" -WebSession $regSession -UseBasicParsing
    $csrfMatch = [regex]::Match($regPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $regCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $regBody = @{
        full_name = "Self Registered Athlete"
        username = $regUsername
        email = $regEmail
        phone = "+91 91234 56789"
        password = "Password@123"
        csrf_token = $regCsrf
    }

    $regSubmit = Invoke-WebRequest -Uri "$baseUrl/register.php" -Method Post -Body $regBody -WebSession $regSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Assert-Condition ($regSubmit.StatusCode -eq 302 -or $regSubmit.Headers.Location -like "*/member/index.php*") "POST /register.php redirects to member hub"

    # Verify session is established and member hub loads
    $regHub = Invoke-WebRequest -Uri "$baseUrl/member/index.php" -WebSession $regSession -UseBasicParsing
    Assert-Condition ($regHub.StatusCode -eq 200 -and $regHub.Content.Contains("Performance Hub")) "New member auto-authenticated and lands on Performance Hub"
} catch {
    Assert-Condition $false "Member self-registration failed: $_"
}

# =========================================================================
# 3. Dual-Identity Authentication: Username vs Email
# =========================================================================
Write-Host "`n--- 3. Testing Dual-Identity Authentication (Username & Email) ---" -ForegroundColor Yellow
$identities = @(
    @{ identity = "admin"; password = "Admin@123"; role = "admin"; name = "Admin by Username" },
    @{ identity = "admin@ironcore.com"; password = "Admin@123"; role = "admin"; name = "Admin by Email" },
    @{ identity = "neeraj.demo"; password = "Trainer@123"; role = "trainer"; name = "Trainer by Username" },
    @{ identity = "neeraj.demo@ironcore.com"; password = "Trainer@123"; role = "trainer"; name = "Trainer by Email" },
    @{ identity = "alex.rivera"; password = "Member@123"; role = "member"; name = "Member by Username" },
    @{ identity = "alex@gmail.com"; password = "Member@123"; role = "member"; name = "Member by Email" }
)

foreach ($idTest in $identities) {
    try {
        $sess = New-Object Microsoft.PowerShell.Commands.WebRequestSession
        $lPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $sess -UseBasicParsing
        $cMatch = [regex]::Match($lPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
        $csrfVal = if ($cMatch.Success) { $cMatch.Groups[1].Value } else { "" }

        $lBody = @{
            identity = $idTest.identity
            password = $idTest.password
            csrf_token = $csrfVal
        }
        $lRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $lBody -WebSession $sess -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
        $targetLoc = $lRes.Headers.Location
        $expectedPath = "/$($idTest.role)/index.php"
        Assert-Condition ($targetLoc -like "*$expectedPath*") "Login $($idTest.name) redirects to $expectedPath"
    } catch {
        Assert-Condition $false "Login $($idTest.name) failed: $_"
    }
}

# =========================================================================
# 4. Admin Portal & Management Modules
# =========================================================================
Write-Host "`n--- 4. Testing Admin Portal & Module Views ---" -ForegroundColor Yellow
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
try {
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($loginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $adminLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $loginBody = @{
        identity = "admin@ironcore.com"
        password = "Admin@123"
        csrf_token = $adminLoginCsrf
    }
    $loginRes = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $loginBody -WebSession $adminSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue

    $dashRes = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $adminSession -UseBasicParsing
    Assert-Condition ($dashRes.StatusCode -eq 200 -and $dashRes.Content.Contains("OPERATIONAL CONTROL CENTER")) "Admin Login & Dashboard Accessible"
} catch {
    Assert-Condition $false "Admin authentication flow failed: $_"
}

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

# Admin Telemetry APIs
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
# 5. Trainer Portal & Ownership Isolation
# =========================================================================
Write-Host "`n--- 5. Testing Trainer Portal & Ownership Security ---" -ForegroundColor Yellow
$trainerSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    $tLoginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $trainerSession -UseBasicParsing
    $csrfMatch = [regex]::Match($tLoginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $tLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $tLoginBody = @{
        identity = "neeraj.demo@ironcore.com"
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

    # Trainer Assign Workout Routine for Assigned Athlete (Member 1 = Alex Rivera, assigned to Neeraj)
    $workoutBody = @{
        csrf_token = $trainerCsrf
        member_id = "1"
        title = "Athletic Conditioning Protocol"
        goal = "Power & Agility"
        difficulty = "Intermediate"
        description = "4-week progressive athletic conditioning protocol"
    }
    $woAssignRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_workout_plan" -Method Post -Body $workoutBody -WebSession $trainerSession -UseBasicParsing
    $woAssignJson = $woAssignRes.Content | ConvertFrom-Json
    $trainerPlanId = $woAssignJson.id
    Assert-Condition ($woAssignJson.success -eq $true -and $trainerPlanId -gt 0) "POST /api.php?action=create_workout_plan assigns workout to assigned athlete"

    # Admin creates an unassigned member for cross-trainer testing
    $membersPage = Invoke-WebRequest -Uri "$baseUrl/admin/members.php" -WebSession $adminSession -UseBasicParsing
    $csrfMatch = [regex]::Match($membersPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $adminCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    # Create unassigned member
    $unassignedEmail = "unassigned_" + (Get-Random) + "@example.com"
    $createUnassignedBody = @{
        csrf_token = $adminCsrf
        name = "Unassigned Athlete"
        username = "unassigned_" + (Get-Random)
        email = $unassignedEmail
        phone = "+91 99999 88888"
        status = "active"
        password = "Member@123"
    }
    $unassignedRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_member" -Method Post -Body $createUnassignedBody -WebSession $adminSession -UseBasicParsing
    $unassignedJson = $unassignedRes.Content | ConvertFrom-Json
    $unassignedMemberId = $unassignedJson.id

    # CROSS-TRAINER SECURITY TEST:
    # Trainer attempts to assign workout to Unassigned Athlete (should be 403 Forbidden!)
    $crossWorkoutBody = @{
        csrf_token = $trainerCsrf
        member_id = "$unassignedMemberId"
        title = "Unauthorized Cross-Workout"
        goal = "Strength"
        difficulty = "Advanced"
    }
    $crossWoRes = $null
    try {
        $crossWoRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_workout_plan" -Method Post -Body $crossWorkoutBody -WebSession $trainerSession -UseBasicParsing
    } catch {
        $crossWoRes = $_.Exception.Response
    }
    $crossCode = if ($crossWoRes -ne $null) { [int]$crossWoRes.StatusCode } else { 0 }
    Assert-Condition ($crossCode -eq 403) "Trainer blocked with 403 when assigning workout to non-client athlete (Got: $crossCode)"

} catch {
    Assert-Condition $false "Trainer portal tests failed: $_"
}

# =========================================================================
# 6. Member Portal & Self-Service
# =========================================================================
Write-Host "`n--- 6. Testing Member Portal & Self-Service ---" -ForegroundColor Yellow
$memberSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    $mLoginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $memberSession -UseBasicParsing
    $csrfMatch = [regex]::Match($mLoginPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $mLoginCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $mLoginBody = @{
        identity = "alex@gmail.com"
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
        weight_kg = "79.2"
        body_fat_pct = "17.8"
        chest_cm = "104.0"
        waist_cm = "81.5"
        biceps_cm = "36.5"
        notes = "Acceptance test automated progress tracking entry."
    }
    $progressRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=member_add_progress" -Method Post -Body $progressBody -WebSession $memberSession -UseBasicParsing
    $progressJson = $progressRes.Content | ConvertFrom-Json
    Assert-Condition ($progressJson.success -eq $true -and $progressJson.id -gt 0) "POST /api.php?action=member_add_progress logs body metrics"
} catch {
    Assert-Condition $false "Member self-service failed: $_"
}

# =========================================================================
# 7. Comprehensive Security & Boundary Audit (Phase 20)
# =========================================================================
Write-Host "`n--- 7. Testing Comprehensive Security, CSRF & Role Boundaries (Phase 20) ---" -ForegroundColor Yellow

# Helper to catch HTTP error responses and return status code
function Invoke-ApiExpectStatus($uri, $body, $session, $method = "Post") {
    try {
        if ($method -eq "Post") {
            $r = Invoke-WebRequest -Uri $uri -Method Post -Body $body -WebSession $session -UseBasicParsing
        } else {
            $r = Invoke-WebRequest -Uri $uri -Method Get -WebSession $session -UseBasicParsing
        }
        return [int]$r.StatusCode
    } catch {
        if ($_.Exception.Response) {
            return [int]$_.Exception.Response.StatusCode
        }
        return 500
    }
}

# 1. Unauthenticated access blocked
$unauthSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$unauthAdmin = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $unauthSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Assert-Condition ($unauthAdmin.StatusCode -eq 302) "Unauthenticated user blocked from /admin/index.php (Redirect)"

$unauthApiStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=members" @{} $unauthSession "Get"
Assert-Condition ($unauthApiStatus -eq 401) "Unauthenticated API request returns 401 Unauthorized"

# 2. Member -> Admin blocked
$memberAdminStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=create_member" @{ csrf_token = $memberCsrf } $memberSession "Post"
Assert-Condition ($memberAdminStatus -eq 403) "Member blocked from admin API action (403 Forbidden)"

# 3. Member -> Trainer view blocked
$memberTrainer = Invoke-WebRequest -Uri "$baseUrl/trainer/index.php" -WebSession $memberSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Assert-Condition ($memberTrainer.StatusCode -eq 302) "Member blocked from trainer portal (Redirect)"

# 4. Trainer -> Admin view blocked
$trainerAdmin = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $trainerSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Assert-Condition ($trainerAdmin.StatusCode -eq 302) "Trainer blocked from admin portal (Redirect)"

$trainerAdminApi = Invoke-ApiExpectStatus "$baseUrl/api.php?action=create_payment" @{ csrf_token = $trainerCsrf; member_id = "1"; amount = "100" } $trainerSession "Post"
Assert-Condition ($trainerAdminApi -eq 403) "Trainer blocked from admin API action (403 Forbidden)"

# 5. Trainer -> Non-assigned athlete operations blocked (403)
$crossTrainerPlan = Invoke-ApiExpectStatus "$baseUrl/api.php?action=create_workout_plan" @{
    csrf_token = $trainerCsrf
    member_id = "9999"
    title = "Unauthorized Workout"
} $trainerSession "Post"
Assert-Condition ($crossTrainerPlan -eq 403) "Trainer blocked from assigning workout to non-assigned athlete (403 Forbidden)"

$crossTrainerCheckin = Invoke-ApiExpectStatus "$baseUrl/api.php?action=check_in" @{
    csrf_token = $trainerCsrf
    member_id = "9999"
} $trainerSession "Post"
Assert-Condition ($crossTrainerCheckin -eq 403) "Trainer blocked from checking in non-assigned athlete (403 Forbidden)"

# 6. Trainer -> Non-assigned workout operations blocked (403)
$crossWorkoutDelete = Invoke-ApiExpectStatus "$baseUrl/api.php?action=delete_workout" @{
    csrf_token = $trainerCsrf
    id = "9999"
} $trainerSession "Post"
Assert-Condition ($crossWorkoutDelete -eq 403) "Trainer blocked from deleting non-owned workout (403 Forbidden)"

$crossWorkoutExercise = Invoke-ApiExpectStatus "$baseUrl/api.php?action=add_workout_exercise" @{
    csrf_token = $trainerCsrf
    plan_id = "9999"
    exercise_id = "1"
} $trainerSession "Post"
Assert-Condition ($crossWorkoutExercise -eq 403) "Trainer blocked from modifying non-owned workout exercises (403 Forbidden)"

# 7. Trainer -> Non-assigned attendance operations blocked (403)
$crossAttCheckout = Invoke-ApiExpectStatus "$baseUrl/api.php?action=check_out" @{
    csrf_token = $trainerCsrf
    attendance_id = "9999"
} $trainerSession "Post"
Assert-Condition ($crossAttCheckout -eq 403) "Trainer blocked from checking out non-owned attendance (403 Forbidden)"

$crossAttDelete = Invoke-ApiExpectStatus "$baseUrl/api.php?action=delete_attendance" @{
    csrf_token = $trainerCsrf
    id = "9999"
} $trainerSession "Post"
Assert-Condition ($crossAttDelete -eq 403) "Trainer blocked from deleting non-owned attendance (403 Forbidden)"

# 8. Member -> Another member's workout protocol blocked (404/403)
$crossMemberWorkout = Invoke-ApiExpectStatus "$baseUrl/api.php?action=workout_details&id=9999" @{} $memberSession "Get"
Assert-Condition ($crossMemberWorkout -eq 404 -or $crossMemberWorkout -eq 403) "Member cannot access unauthorized workout protocol (Got: $crossMemberWorkout)"

# 9. Invalid CSRF Token
$invalidCsrfStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=create_member" @{
    csrf_token = "invalid_token_xyz"
    first_name = "Tamper"
    last_name = "User"
    email = "tamper@example.com"
} $adminSession "Post"
Assert-Condition ($invalidCsrfStatus -eq 419) "Invalid CSRF token returns 419 Authentication Timeout"

# 10. Missing CSRF Token
$missingCsrfStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=create_member" @{
    first_name = "Tamper"
    last_name = "User"
    email = "tamper@example.com"
} $adminSession "Post"
Assert-Condition ($missingCsrfStatus -eq 419) "Missing CSRF token returns 419 Authentication Timeout"

# 11. Invalid Non-Numeric IDs
$invalidIdStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=workout_details&id=abc" @{} $adminSession "Get"
Assert-Condition ($invalidIdStatus -eq 404 -or $invalidIdStatus -eq 400) "Non-numeric ID safely handled (Got: $invalidIdStatus)"

# 12. Nonexistent Records
$nonexistentStatus = Invoke-ApiExpectStatus "$baseUrl/api.php?action=workout_details&id=999999" @{} $adminSession "Get"
Assert-Condition ($nonexistentStatus -eq 404) "Nonexistent record lookup returns 404 Not Found"

# 13. Suspended User Authentication Blocked
try {
    # Member Alex Rivera is user_id 8 in seed
    $suspBody = @{
        csrf_token = $adminCsrf
        user_id = "8"
        status = "suspended"
    }
    Invoke-WebRequest -Uri "$baseUrl/api.php?action=set_user_status" -Method Post -Body $suspBody -WebSession $adminSession -UseBasicParsing | Out-Null

    # Attempt login as suspended member using clean session
    $suspSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $suspPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -WebSession $suspSession -UseBasicParsing
    $csrfMatch = [regex]::Match($suspPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $sCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $suspSubmit = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body @{
        identity = "alex.rivera"
        password = "Member@123"
        csrf_token = $sCsrf
    } -WebSession $suspSession -UseBasicParsing
    Assert-Condition ($suspSubmit.Content -like "*suspended*") "Suspended user cannot authenticate and receives suspension alert"

    # Restore user to active
    $activeBody = @{
        csrf_token = $adminCsrf
        user_id = "8"
        status = "active"
    }
    Invoke-WebRequest -Uri "$baseUrl/api.php?action=set_user_status" -Method Post -Body $activeBody -WebSession $adminSession -UseBasicParsing | Out-Null
} catch {
    Assert-Condition $false "Suspended user test failed: $_"
}

# 14. Duplicate Username Rejection
try {
    $dupUserSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $dupUserPage = Invoke-WebRequest -Uri "$baseUrl/register.php" -WebSession $dupUserSession -UseBasicParsing
    $csrfMatch = [regex]::Match($dupUserPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $tCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $dupUserBody = @{
        full_name = "Duplicate Username Test"
        username = "admin"
        email = "unique_email_123@example.com"
        password = "Password@123"
        csrf_token = $tCsrf
    }
    $dupUserRes = Invoke-WebRequest -Uri "$baseUrl/register.php" -Method Post -Body $dupUserBody -WebSession $dupUserSession -UseBasicParsing
    Assert-Condition ($dupUserRes.Content -like "*username is already taken*") "Registration rejects duplicate username"
} catch {
    Assert-Condition $false "Duplicate username test failed: $_"
}

# 15. Duplicate Email Rejection
try {
    $dupEmailSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $dupEmailPage = Invoke-WebRequest -Uri "$baseUrl/register.php" -WebSession $dupEmailSession -UseBasicParsing
    $csrfMatch = [regex]::Match($dupEmailPage.Content, 'name="csrf_token"\s+value="([^"]+)"')
    $eCsrf = if ($csrfMatch.Success) { $csrfMatch.Groups[1].Value } else { "" }

    $dupEmailBody = @{
        full_name = "Duplicate Email Test"
        username = "unique_user_987"
        email = "alex@gmail.com"
        password = "Password@123"
        csrf_token = $eCsrf
    }
    $dupEmailRes = Invoke-WebRequest -Uri "$baseUrl/register.php" -Method Post -Body $dupEmailBody -WebSession $dupEmailSession -UseBasicParsing
    Assert-Condition ($dupEmailRes.Content -like "*already exists*") "Registration rejects duplicate email"
} catch {
    Assert-Condition $false "Duplicate email test failed: $_"
}

# =========================================================================
# 8. Business Logic & Relational Integrity
# =========================================================================
Write-Host "`n--- 8. Testing Business & Data Integrity ---" -ForegroundColor Yellow
try {
    # 1. Duplicate Attendance Check-In on same day
    $dupAttendanceBody = @{
        csrf_token = $adminCsrf
        member_id = "1"
    }
    $dupAttRes = $null
    try {
        $dupAttRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=check_in" -Method Post -Body $dupAttendanceBody -WebSession $adminSession -UseBasicParsing
    } catch {
        $dupAttRes = $_.Exception.Response
    }
    $dupJson = if ($dupAttRes -is [System.Net.HttpWebResponse]) {
        $reader = New-Object System.IO.StreamReader($dupAttRes.GetResponseStream())
        $reader.ReadToEnd() | ConvertFrom-Json
    } else {
        $dupAttRes.Content | ConvertFrom-Json
    }
    Assert-Condition ($dupJson.success -eq $false -or $dupJson.message -like "*already*") "Duplicate same-day check-in is rejected"

    # 2. Payment Subscription Ownership Integrity
    $badPaymentBody = @{
        csrf_token = $adminCsrf
        subscription_id = "1"
        member_id = "999"
        amount = "1999.00"
        payment_method = "Card"
    }
    $badPayRes = $null
    try {
        $badPayRes = Invoke-WebRequest -Uri "$baseUrl/api.php?action=create_payment" -Method Post -Body $badPaymentBody -WebSession $adminSession -UseBasicParsing
    } catch {
        $badPayRes = $_.Exception.Response
    }
    $badPayJson = if ($badPayRes -is [System.Net.HttpWebResponse]) {
        $reader = New-Object System.IO.StreamReader($badPayRes.GetResponseStream())
        $reader.ReadToEnd() | ConvertFrom-Json
    } else {
        $badPayRes.Content | ConvertFrom-Json
    }
    Assert-Condition ($badPayJson.success -eq $false) "Payment rejected when subscription belongs to different member"

} catch {
    Assert-Condition $false "Data integrity test failed: $_"
}

# =========================================================================
# 9. Restoring Database Baseline Seed
# =========================================================================
Write-Host "`n--- 9. Restoring Database Baseline Seed ---" -ForegroundColor Yellow
try {
    & $phpBin "$projectRoot/tests/reset_database.php"
    Assert-Condition ($LASTEXITCODE -eq 0) "Database reset to pristine 1-record baseline"
} catch {
    Assert-Condition $false "Baseline restore failed: $_"
}

# =========================================================================
# Summary
# =========================================================================
Write-Host "`n====================================================" -ForegroundColor Cyan
if ($script:errors.Count -eq 0) {
    Write-Host " ALL ACCEPTANCE & SECURITY TESTS PASSED! (0 Failures)" -ForegroundColor Green
    Write-Host "====================================================" -ForegroundColor Cyan
    exit 0
} else {
    Write-Host " ACCEPTANCE TESTS FAILED WITH $($script:errors.Count) ERRORS:" -ForegroundColor Red
    foreach ($err in $script:errors) {
        Write-Host "  - $err" -ForegroundColor Red
    }
    Write-Host "====================================================" -ForegroundColor Cyan
    exit 1
}
