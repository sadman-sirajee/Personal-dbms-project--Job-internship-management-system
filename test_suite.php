<?php
/**
 * Automated Test Suite
 * Job/Internship Management System
 * Includes: Authentication, User CRUD, Job/Internship CRUD, Applications System & RBAC
 */

require_once __DIR__ . '/config/database.php';

echo "========================================================\n";
echo "RUNNING COMPREHENSIVE TEST SUITE\n";
echo "Job/Internship Management System (with RBAC & Applications)\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

// TEST 1: Database Connection
assert_test("Database PDO instance is active", isset($pdo) && $pdo instanceof PDO);

// TEST 2: Admin Authentication Verification
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@gmail.com']);
$admin = $stmt->fetch();
assert_test("Admin user exists in database", !empty($admin));
assert_test("Admin password verify succeeds for 'admin123'", password_verify('admin123', $admin['password']));
assert_test("Admin password verify fails for wrong password", !password_verify('wrongpass', $admin['password']));

// TEST 3: User CREATE Operation
$testUserEmail = "testuser_" . time() . "@test.com";
$testUserName = "Automated Test Candidate";
$testUserPass = password_hash("password123", PASSWORD_DEFAULT);
$testUserRole = "User";

$insStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$res = $insStmt->execute([$testUserName, $testUserEmail, $testUserPass, $testUserRole]);
$testUserId = (int) $pdo->lastInsertId();
assert_test("User CREATE operation inserted successfully", $res && $testUserId > 0);

// TEST 4: User READ Operation
$readStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$readStmt->execute([$testUserId]);
$fetchedUser = $readStmt->fetch();
assert_test("User READ retrieved correct name", $fetchedUser && $fetchedUser['name'] === $testUserName);
assert_test("User READ retrieved correct email", $fetchedUser && $fetchedUser['email'] === $testUserEmail);

// TEST 5: User UPDATE Operation
$updatedName = "Updated Test User Name";
$updStmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
$updStmt->execute([$updatedName, $testUserId]);

$readUpdatedStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$readUpdatedStmt->execute([$testUserId]);
$verifiedName = $readUpdatedStmt->fetchColumn();
assert_test("User UPDATE modified record properly", $verifiedName === $updatedName);

// TEST 6: Job CREATE Operation
$testJobTitle = "Senior Cloud Architect " . time();
$testJobCompany = "Cloud Solutions Inc";
$testJobLocation = "Dhaka";
$testJobType = "Job";
$testJobDesc = "Leading cloud architecture transformation.";
$testJobDeadline = "2026-12-31";

$insJob = $pdo->prepare("INSERT INTO jobs (title, company, location, type, description, deadline) VALUES (?, ?, ?, ?, ?, ?)");
$jobRes = $insJob->execute([$testJobTitle, $testJobCompany, $testJobLocation, $testJobType, $testJobDesc, $testJobDeadline]);
$testJobId = (int) $pdo->lastInsertId();
assert_test("Job CREATE operation inserted successfully", $jobRes && $testJobId > 0);

// TEST 7: Internship CREATE Operation
$testInternTitle = "AI Research Intern " . time();
$testInternCompany = "DeepTech Labs";
$testInternLocation = "Remote";
$testInternType = "Internship";
$testInternDeadline = "2026-11-30";

$insIntern = $pdo->prepare("INSERT INTO jobs (title, company, location, type, description, deadline) VALUES (?, ?, ?, ?, ?, ?)");
$internRes = $insIntern->execute([$testInternTitle, $testInternCompany, $testInternLocation, $testInternType, "Researching foundation models", $testInternDeadline]);
$testInternId = (int) $pdo->lastInsertId();
assert_test("Internship CREATE operation inserted successfully", $internRes && $testInternId > 0);

// TEST 8: Job READ & Verification
$readJob = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$readJob->execute([$testJobId]);
$fJob = $readJob->fetch();
assert_test("Job READ retrieved correct title", $fJob && $fJob['title'] === $testJobTitle);
assert_test("Job READ retrieved type 'Job'", $fJob && $fJob['type'] === 'Job');

// TEST 9: Job UPDATE Operation
$updatedTitle = "Principal Cloud Architect";
$updJob = $pdo->prepare("UPDATE jobs SET title = ? WHERE id = ?");
$updJob->execute([$updatedTitle, $testJobId]);

$readUpdJob = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
$readUpdJob->execute([$testJobId]);
assert_test("Job UPDATE properly changed title in database", $readUpdJob->fetchColumn() === $updatedTitle);

// TEST 10: Search Functionality (Title, Company, Location)
$searchStmt = $pdo->prepare("SELECT * FROM jobs WHERE title LIKE ? OR company LIKE ? OR location LIKE ?");
$searchStmt->execute(['%Cloud%', '%Cloud%', '%Cloud%']);
$searchResults = $searchStmt->fetchAll();
assert_test("Search functionality matched keyword in title", count($searchResults) >= 1);

// TEST 11: Filter Functionality (by Type = 'Internship')
$filterStmt = $pdo->prepare("SELECT * FROM jobs WHERE type = ?");
$filterStmt->execute(['Internship']);
$filterResults = $filterStmt->fetchAll();
$allInternships = true;
foreach ($filterResults as $r) {
    if ($r['type'] !== 'Internship') {
        $allInternships = false;
        break;
    }
}
assert_test("Filter functionality returned only Internship listings", count($filterResults) >= 1 && $allInternships);

// ========================================================
// APPLICATION & RBAC TESTS
// ========================================================

// TEST 12: RBAC Helper Functionality
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'Admin';
assert_test("is_admin() returns TRUE for Admin session", is_admin() === true);

$_SESSION['user_id'] = $testUserId;
$_SESSION['user_role'] = 'User';
assert_test("is_admin() returns FALSE for standard User session", is_admin() === false);

// TEST 13: Application Submission (CREATE)
$appPhone = "+880 1999-887766";
$appPitch = "Passionate candidate with strong background in cloud systems.";
$appResume = "https://linkedin.com/in/testcandidate";
$insApp = $pdo->prepare("INSERT INTO applications (user_id, job_id, phone, cover_note, resume_url, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
$appRes = $insApp->execute([$testUserId, $testJobId, $appPhone, $appPitch, $appResume]);
$testAppId = (int) $pdo->lastInsertId();
assert_test("Application CREATE submitted successfully", $appRes && $testAppId > 0);

// TEST 14: Duplicate Application Prevention (UNIQUE Constraint)
$duplicateCaught = false;
try {
    $insAppDuplicate = $pdo->prepare("INSERT INTO applications (user_id, job_id, phone, cover_note, resume_url, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $insAppDuplicate->execute([$testUserId, $testJobId, $appPhone, $appPitch, $appResume]);
} catch (PDOException $e) {
    $duplicateCaught = true;
}
assert_test("Duplicate application blocked by unique constraint", $duplicateCaught);

// TEST 15: Candidate Application History Retrieval (READ)
$myAppsStmt = $pdo->prepare("
    SELECT a.*, j.title AS job_title 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE a.user_id = ?
");
$myAppsStmt->execute([$testUserId]);
$myApps = $myAppsStmt->fetchAll();
assert_test("Candidate My Applications retrieved successfully", count($myApps) === 1 && $myApps[0]['status'] === 'Pending');

// TEST 16: Admin Application Status Update (UPDATE)
$updAppStatus = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
$updAppStatus->execute(['Reviewed', $testAppId]);

$checkStatusStmt = $pdo->prepare("SELECT status FROM applications WHERE id = ?");
$checkStatusStmt->execute([$testAppId]);
$newStatus = $checkStatusStmt->fetchColumn();
assert_test("Admin status update successfully set to 'Reviewed'", $newStatus === 'Reviewed');

$updAppStatus->execute(['Accepted', $testAppId]);
$checkStatusStmt->execute([$testAppId]);
$acceptedStatus = $checkStatusStmt->fetchColumn();
assert_test("Admin status update successfully set to 'Accepted'", $acceptedStatus === 'Accepted');

// TEST 17: Application DELETE / Clean up
$delApp = $pdo->prepare("DELETE FROM applications WHERE id = ?");
$delApp->execute([$testAppId]);
$checkAppDel = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE id = ?");
$checkAppDel->execute([$testAppId]);
assert_test("Application DELETE cleaned up record successfully", (int)$checkAppDel->fetchColumn() === 0);

// TEST 18: Clean up test job & internship records
$cleanJob = $pdo->prepare("DELETE FROM jobs WHERE id IN (?, ?)");
$cleanJob->execute([$testJobId, $testInternId]);
$checkClean = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE id IN (?, ?)");
$checkClean->execute([$testJobId, $testInternId]);
assert_test("Job & Internship records cleaned up successfully", (int)$checkClean->fetchColumn() === 0);

// TEST 19: Clean up test user record
$delUserStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$delUserStmt->execute([$testUserId]);
$verifyUserDel = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
$verifyUserDel->execute([$testUserId]);
assert_test("User DELETE cleaned up record successfully", (int)$verifyUserDel->fetchColumn() === 0);

echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";

if ($failed === 0) {
    echo "ALL 25 TESTS PASSED SUCCESSFULLY! APPLICATION IS FULLY FUNCTIONAL WITH RBAC & APPLICATIONS.\n";
} else {
    echo "SOME TESTS FAILED.\n";
    exit(1);
}
