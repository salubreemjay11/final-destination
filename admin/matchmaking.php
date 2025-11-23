<?php
$pageTitle = 'Matchmaking - Orphanfare';
require_once 'includes/top-header.php'; 

// Get foster ID from URL
if (!isset($_GET['foster_id'])) {
    header('Location: foster-info.php?error=invalid_foster');
    exit();
}

$fosterId = $_GET['foster_id'];

try {
    // Get foster parent details
    $stmt = $pdo->prepare("SELECT * FROM foster_parents WHERE foster_id = ?");
    $stmt->execute([$fosterId]);
    $foster = $stmt->fetch();
    
    if (!$foster) {
        header('Location: foster-info.php?error=foster_not_found');
        exit();
    }
    
    // Get all children for matching
    $childrenStmt = $pdo->prepare("SELECT * FROM children WHERE status = 'In Care' ORDER BY created_at DESC");
    $childrenStmt->execute();
    $children = $childrenStmt->fetchAll();
    
    // Calculate compatibility scores for each child
    $matches = [];
    foreach ($children as $child) {
        $compatibilityScore = calculateCompatibility($foster, $child);
        $matchReasons = getMatchReasons($foster, $child);
        
        if ($compatibilityScore > 0) {
            $matches[] = [
                'child' => $child,
                'score' => $compatibilityScore,
                'status' => getCompatibilityStatus($compatibilityScore),
                'reasons' => $matchReasons
            ];
        }
    }
    
    // Sort matches by compatibility score (highest first)
    usort($matches, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
} catch (Exception $e) {
    error_log("Matchmaking error: " . $e->getMessage());
    header('Location: foster-details.php?foster_id=' . $fosterId . '&error=load_failed');
    exit();
}

// Function to calculate compatibility score
function calculateCompatibility($foster, $child) {
    $score = 0;
    
    // Age preference matching
    if (!empty($foster['age_preference'])) {
        $agePref = $foster['age_preference'];
        if (strpos($agePref, '-') !== false) {
            list($minAge, $maxAge) = explode('-', str_replace('years', '', $agePref));
            $minAge = intval(trim($minAge));
            $maxAge = intval(trim($maxAge));
            
            if ($child['age'] >= $minAge && $child['age'] <= $maxAge) {
                $score += 30;
            }
        }
    }
    
    // Gender preference matching
    if (!empty($foster['gender_preference']) && $foster['gender_preference'] !== 'No Preference') {
        if ($foster['gender_preference'] === $child['gender']) {
            $score += 25;
        }
    } else {
        // No gender preference gets a base score
        $score += 15;
    }
    
    // Experience level consideration
    if (!empty($foster['experience_level'])) {
        switch($foster['experience_level']) {
            case 'First-time':
                // First-time adopters might prefer younger children
                if ($child['age'] <= 10) $score += 15;
                break;
            case 'Experienced':
                // Experienced adopters can handle various ages
                $score += 10;
                break;
            case 'Fostered-before':
                // Those who fostered before might prefer similar age ranges
                $score += 20;
                break;
        }
    }
    
    // Personality and interests (simplified matching)
    if (!empty($foster['interests']) && !empty($child['interests'])) {
        $fosterInterests = array_map('strtolower', array_map('trim', explode(',', $foster['interests'])));
        $childInterests = array_map('strtolower', array_map('trim', explode(',', $child['interests'])));
        
        $commonInterests = array_intersect($fosterInterests, $childInterests);
        if (count($commonInterests) > 0) {
            $score += count($commonInterests) * 5;
        }
    }
    
    // Capacity check
    if ($foster['current_children'] < $foster['capacity']) {
        $score += 10;
    }
    
    return min($score, 100); // Cap at 100
}

// Function to get match reasons
function getMatchReasons($foster, $child) {
    $reasons = [];
    
    // Age preference
    if (!empty($foster['age_preference'])) {
        $agePref = $foster['age_preference'];
        if (strpos($agePref, '-') !== false) {
            list($minAge, $maxAge) = explode('-', str_replace('years', '', $agePref));
            $minAge = intval(trim($minAge));
            $maxAge = intval(trim($maxAge));
            
            if ($child['age'] >= $minAge && $child['age'] <= $maxAge) {
                $reasons[] = "Age preference match ({$foster['age_preference']})";
            }
        }
    }
    
    // Gender preference
    if (!empty($foster['gender_preference']) && $foster['gender_preference'] !== 'No Preference') {
        if ($foster['gender_preference'] === $child['gender']) {
            $reasons[] = "Gender preference match";
        }
    }
    
    // Interests matching
    if (!empty($foster['interests']) && !empty($child['interests'])) {
        $fosterInterests = array_map('strtolower', array_map('trim', explode(',', $foster['interests'])));
        $childInterests = array_map('strtolower', array_map('trim', explode(',', $child['interests'])));
        
        $commonInterests = array_intersect($fosterInterests, $childInterests);
        if (count($commonInterests) > 0) {
            $reasons[] = "Shared interests: " . implode(', ', $commonInterests);
        }
    }
    
    // Capacity availability
    if ($foster['current_children'] < $foster['capacity']) {
        $reasons[] = "Available capacity";
    }
    
    return $reasons;
}

// Function to get compatibility status
function getCompatibilityStatus($score) {
    if ($score >= 80) return "Excellent";
    if ($score >= 60) return "Good";
    if ($score >= 40) return "Moderate";
    if ($score >= 20) return "Fair";
    return "Low";
}

// Handle meeting request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_meeting'])) {
    $childId = $_POST['child_id'];
    
    try {
        // Create meeting request record
        $stmt = $pdo->prepare("
            INSERT INTO meeting_requests (foster_id, child_id, requested_by, status, requested_at) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        
        $result = $stmt->execute([
            $fosterId,
            $childId,
            $currentUser['id']
        ]);
        
        if ($result) {
            logActivity($currentUser['id'], 'Meeting Requested', 'meeting_requests', $pdo->lastInsertId());
            $success = "Meeting request sent successfully!";
        } else {
            $error = "Failed to send meeting request. Please try again.";
        }
        
    } catch (Exception $e) {
        error_log("Meeting request error: " . $e->getMessage());
        $error = "An error occurred while sending the meeting request.";
    }
}
?>

<main class="main-content">
    <div class="content">
        <!-- Left Sidebar - Foster List -->
        <div class="sidebar-foster-list">
            <div class="foster-management-header">
                <h2 class="section-title">Foster Management</h2>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.location.href='new-foster.php'" style="width: 100%;">
                        Add New Adopter
                    </button>
                    <button class="btn btn-secondary" onclick="window.location.href='foster-details.php?foster_id=<?php echo $fosterId; ?>'" style="width: 100%;">
                        Back to Profile
                    </button>
                    <button class="btn btn-secondary" onclick="window.location.href='foster-info.php'" style="width: 100%;">
                        Back to List
                    </button>
                </div>
            </div>
            
            <div class="foster-list-container">
                <h3 class="subsection-title">All Foster Parents</h3>
                <div class="foster-items">
                    <?php 
                    $allFostersStmt = $pdo->prepare("SELECT foster_id, name, status FROM foster_parents ORDER BY created_at DESC");
                    $allFostersStmt->execute();
                    $allFosters = $allFostersStmt->fetchAll();
                    
                    foreach ($allFosters as $fosterItem): ?>
                        <div class="foster-item <?php echo $fosterItem['foster_id'] === $fosterId ? 'active' : ''; ?>" 
                             onclick="window.location.href='matchmaking.php?foster_id=<?php echo $fosterItem['foster_id']; ?>'">
                            <div class="foster-name"><?php echo htmlspecialchars($fosterItem['name']); ?></div>
                            <div class="foster-status">
                                <span class="status-badge <?php echo getStatusBadgeClass($fosterItem['status']); ?>">
                                    <?php echo htmlspecialchars(getStatusDisplay($fosterItem['status'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content - Matchmaking -->
        <div class="matchmaking-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Matchmaking: <?php echo htmlspecialchars($foster['name']); ?></h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Adopter Profile Summary -->
            <div class="profile-header">
                <h2>Adopter Profile: <?php echo htmlspecialchars($foster['name']); ?></h2>
                <div class="profile-info">
                    <div class="profile-item">
                        <div class="profile-label">Age Preference</div>
                        <div class="profile-value"><?php echo htmlspecialchars($foster['age_preference'] ?? 'Not specified'); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Gender Preference</div>
                        <div class="profile-value"><?php echo htmlspecialchars($foster['gender_preference'] ?? 'No preference'); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Interests</div>
                        <div class="profile-value"><?php echo htmlspecialchars($foster['interests'] ?? 'Not specified'); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Experience Level</div>
                        <div class="profile-value"><?php echo htmlspecialchars($foster['experience_level'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label>Sort by:</label>
                    <select id="sortFilter">
                        <option value="score">Compatibility Score ▼</option>
                        <option value="age">Age</option>
                        <option value="name">Name</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Age Filter:</label>
                    <select id="ageFilter">
                        <option value="all">All Ages</option>
                        <option value="0-5">0-5 years</option>
                        <option value="6-10">6-10 years</option>
                        <option value="11-15">11-15 years</option>
                        <option value="16+">16+ years</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Gender Filter:</label>
                    <select id="genderFilter">
                        <option value="all">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <!-- Matches Info -->
            <div class="matches-info"><?php echo count($matches); ?> compatible matches found</div>

            <!-- Matches Grid -->
            <div class="cards-grid" id="matchesGrid">
                <?php if (empty($matches)): ?>
                    <div class="no-matches">
                        <h3>No compatible matches found</h3>
                        <p>Try adjusting the foster parent's preferences or check back later for new children.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($matches as $match): 
                        $child = $match['child'];
                        $initials = getInitials($child['name']);
                    ?>
                        <div class="card" data-age="<?php echo $child['age']; ?>" data-gender="<?php echo $child['gender']; ?>" data-score="<?php echo $match['score']; ?>" data-name="<?php echo htmlspecialchars($child['name']); ?>">
                            <div class="compatibility">
                                <div class="compatibility-label">Compatibility</div>
                                <div class="compatibility-status <?php echo strtolower($match['status']); ?>">
                                    <?php echo $match['status']; ?> (<?php echo $match['score']; ?>%)
                                </div>
                            </div>
                            <div class="score-indicator">
                                <div class="score-bar">
                                    <div class="score-fill <?php echo strtolower($match['status']); ?>" style="width: <?php echo $match['score']; ?>%"></div>
                                </div>
                                <div class="score-text"><?php echo $match['score']; ?>%</div>
                            </div>
                            <div class="card-header">
                                <div class="child-photo">
                                    <?php 
                                    $photoPath = $child['photo_path'] ?? '';
                                    $hasPhoto = false;
                                    $finalPhotoPath = '';
                                    
                                    if (!empty($photoPath)) {
                                        // Check common storage locations
                                        $possiblePaths = [
                                            $photoPath, // Original path from database
                                            '../uploads/children/' . $photoPath, // Your uploads/children directory
                                            'uploads/children/' . $photoPath, // Relative path
                                            '../' . $photoPath, // One level up
                                            $photoPath // Original path
                                        ];
                                        
                                        foreach ($possiblePaths as $testPath) {
                                            if (file_exists($testPath) && is_file($testPath)) {
                                                $hasPhoto = true;
                                                $finalPhotoPath = $testPath;
                                                break;
                                            }
                                        }
                                        
                                        // Also check if it's just a filename without path
                                        if (!$hasPhoto) {
                                            $justFilename = basename($photoPath);
                                            $testPaths = [
                                                '../uploads/children/' . $justFilename,
                                                'uploads/children/' . $justFilename,
                                                '../images/children/' . $justFilename,
                                                'images/children/' . $justFilename
                                            ];
                                            
                                            foreach ($testPaths as $testPath) {
                                                if (file_exists($testPath) && is_file($testPath)) {
                                                    $hasPhoto = true;
                                                    $finalPhotoPath = $testPath;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    
                                    if ($hasPhoto): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($finalPhotoPath); ?>" 
                                            alt="<?php echo htmlspecialchars($child['name']); ?>" 
                                            class="child-image"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="avatar" style="background: <?php echo getAvatarColor($child['name']); ?>; display: none;">
                                            <?php echo $initials; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Fallback to avatar if no photo -->
                                        <div class="avatar" style="background: <?php echo getAvatarColor($child['name']); ?>;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <?php if (!empty($photoPath)): ?>
                                            <!-- Debug info - visible in page source -->
                                            <!-- Photo path found in DB but file not accessible: <?php echo htmlspecialchars($photoPath); ?> -->
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="card-info">
                                    <h3><?php echo htmlspecialchars($child['name']); ?></h3>
                                    <div class="card-details"><?php echo htmlspecialchars($child['child_id']); ?></div>
                                    <div class="card-details">
                                        Age: <?php echo $child['age']; ?> | 
                                        Gender: <?php echo $child['gender']; ?> | 
                                        Status: <?php echo htmlspecialchars($child['status']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="match-reasons-section">
                                <div class="match-reasons-title">Why this match?</div>
                                <div class="match-reasons">
                                    <?php foreach ($match['reasons'] as $reason): ?>
                                        <p><?php echo htmlspecialchars($reason); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-secondary" onclick="navigateToChildManagement('<?php echo $child['child_id']; ?>')">
                                    View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
/* Enhanced Matchmaking Styles */
.matchmaking-content {
    flex: 1;
    padding: 20px;
}


/* Profile Header */
.dark-theme .profile-header {
    background: var(--section-bg, #1e1e1e);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    border: 1px solid var(--section-border, #3a3a3a);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.light-theme .profile-header {
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.profile-header h2 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--input-color, #ffffff);
}

.light-theme .profile-header h2 {
    color: #1e293b;
}

.profile-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.profile-item {
    text-align: left;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.dark-theme .profile-item {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
}

.light-theme .profile-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.profile-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.dark-theme .profile-label {
    font-size: 12px;
    opacity: 0.8;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: var(--label-color, #b8c5ff);
    font-weight: 600;
    letter-spacing: 0.5px;
}

.light-theme .profile-label {
    font-size: 12px;
    opacity: 0.8;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: #0E7490;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.dark-theme .profile-value {
    font-size: 15px;
    font-weight: 600;
    color: var(--input-color, #ffffff);
}

.light-theme .profile-value {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.main-content {
    display: grid;
    grid-template-rows: 1fr 1fr;
    margin-left: 0;
}

/* Enhanced Filters */
.filters {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    align-items: end;
    flex-wrap: wrap;
    padding: 20px;
    border-radius: 8px;
}

.dark-theme .filters {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
}

.light-theme .filters {
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.dark-theme .filter-group label {
    font-size: 13px;
    color: var(--label-color, #b8c5ff);
    font-weight: 600;
}

.light-theme .filter-group label {
    font-size: 13px;
    color: #0E7490;
    font-weight: 600;
}

.dark-theme .filter-group select {
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid var(--input-border, #3a3a3a);
    background: var(--input-bg, #2a2a2a);
    color: var(--input-color, #ffffff);
    cursor: pointer;
    font-size: 14px;
    min-width: 150px;
    transition: all 0.3s ease;
}

.light-theme .filter-group select {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    cursor: pointer;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 14px;
    min-width: 150px;
    transition: all 0.3s ease;
}

.filter-group select:hover {
    border-color: #3b82f6;
}

.filter-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Matches Info */
.matches-info {
    text-align: right;
    font-size: 15px;
    margin-bottom: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
}

.dark-theme .matches-info {
    background: #2a2a2a;
    color: var(--label-color, #b8c5ff);
    border: 1px solid #3a3a3a;
}

.light-theme .matches-info {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
}

/* Enhanced Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.dark-theme .card {
    background: var(--section-bg, #1e1e1e);
    border-radius: 12px;
    padding: 25px;
    position: relative;
    border: 1px solid var(--section-border, #3a3a3a);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.light-theme .card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 25px;
    position: relative;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border-radius: 12px;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Card Header */
.card-header {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: flex-start;
}

.avatar {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.content {
    display: flex;
}

.child-image {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #3a3a3a;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.card-info {
    flex: 1;
    margin-top: 15px;
}

.card-info h3 {
    font-size: 20px;
    margin-bottom: 8px;
    color: var(--input-color, #ffffff);
    font-weight: 600;
}

.light-theme .card-info h3 {
    color: #1e293b;
}

.dark-theme .card-details {
    font-size: 13px;
    opacity: 0.9;
    margin: 4px 0;
    color: var(--label-color, #b8c5ff);
    line-height: 1.4;
}

.light-theme .card-details {
    color: #475569;
    font-size: 13px;
    opacity: 0.9;
    margin: 4px 0;
    line-height: 1.4;
}

/* Enhanced Compatibility */
.compatibility {
    position: absolute;
    top: 25px;
    right: 25px;
    text-align: right;
    font-size: 12px;
}

.dark-theme .compatibility-label {
    opacity: 0.8;
    color: var(--label-color, #b8c5ff);
    margin-bottom: 4px;
    font-weight: 600;
    margin-top: -15px;
}

.light-theme .compatibility-label {
    opacity: 0.8;
    color: #0E7490;
    margin-bottom: 4px;
    font-weight: 600;
    margin-top: -15px;
}

.compatibility-status {
    font-weight: 700;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 20px;
    background: rgba(0, 0, 0, 0.2);
}

.compatibility-status.excellent { 
    color: #10b981; 
    background: rgba(16, 185, 129, 0.1);
    margin-top: 25px;
}
.compatibility-status.good { 
    color: #84cc16; 
    background: rgba(132, 204, 22, 0.1);
    margin-top: 25px;
}
.compatibility-status.moderate { 
    color: #f59e0b; 
    background: rgba(245, 158, 11, 0.1);
    margin-top: 25px;
}
.compatibility-status.fair { 
    color: #f97316; 
    background: rgba(249, 115, 22, 0.1);
    margin-top: 25px;
}
.compatibility-status.low { 
    color: #ef4444; 
    background: rgba(239, 68, 68, 0.1);
    margin-top: 25px;
}

/* Enhanced Match Reasons */
.match-reasons-section {
    margin: 20px 0;
    padding: 15px;
    border-radius: 8px;
}

.dark-theme .match-reasons-section {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
}

.light-theme .match-reasons-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.match-reasons-title {
    font-weight: 700;
    margin-bottom: 12px;
    font-size: 14px;
    color: var(--input-color, #ffffff);
}

.light-theme .match-reasons-title {
    color: #1e293b;
}

.match-reasons {
    font-size: 13px;
}

.match-reasons p {
    margin: 8px 0;
    padding-left: 18px;
    position: relative;
    color: var(--input-color, #ffffff);
    line-height: 1.5;
}

.light-theme .match-reasons p {
    color: #475569;
}

.match-reasons p::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
    font-size: 14px;
}

/* Enhanced Card Actions */
.card-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #047857, #065f46);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
}

.light-theme .btn-secondary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.dark-theme .btn-secondary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.light-theme .btn-secondary:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.dark-theme .btn-secondary:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

/* No Matches State */
.no-matches {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 40px;
    border-radius: 12px;
    border: 2px dashed;
}

.dark-theme .no-matches {
    background: var(--section-bg, #1e1e1e);
    border-color: var(--section-border, #3a3a3a);
}

.light-theme .no-matches {
    background: #ffffff;
    border-color: #e2e8f0;
}

.no-matches h3 {
    color: var(--input-color, #ffffff);
    margin-bottom: 15px;
    font-size: 20px;
    font-weight: 600;
}

.light-theme .no-matches h3 {
    color: #1e293b;
}

.no-matches p {
    color: var(--label-color, #b8c5ff);
    font-size: 15px;
    line-height: 1.5;
}

.light-theme .no-matches p {
    color: #64748b;
}

/* Score Indicator */
.score-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.score-bar {
    flex: 1;
    height: 6px;
    background: #3a3a3a;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 5px;
}

.light-theme .score-bar {
    background: #e2e8f0;
}

.score-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease;
}

.score-fill.excellent { background: linear-gradient(90deg, #10b981, #34d399); ;}
.score-fill.good { background: linear-gradient(90deg, #84cc16, #a3e635); }
.score-fill.moderate { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.score-fill.fair { background: linear-gradient(90deg, #f97316, #fb923c); }
.score-fill.low { background: linear-gradient(90deg, #ef4444, #f87171); }

.score-text {
    font-size: 12px;
    font-weight: 600;
    min-width: 40px;
    text-align: right;
}

.dark-theme .score-text {
    color: var(--label-color, #b8c5ff);
}

.light-theme .score-text {
    color: #475569;
}

/* Sidebar Foster List Styles */
.sidebar-foster-list {
    background: var(--section-bg, #1e1e1e);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid var(--section-border, #3a3a3a);
    height: fit-content;
    display: block;
    top: 100px;

}



.light-theme .sidebar-foster-list {
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
}

.foster-management-header {
    margin-bottom: 25px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--input-color, #ffffff);
}

.light-theme .section-title {
    color: #1e293b;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.subsection-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--label-color, #b8c5ff);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.light-theme .subsection-title {
    color: #64748b;
}

.foster-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.foster-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.dark-theme .foster-item {
    background: #2a2a2a;
    border-color: #3a3a3a;
}

.light-theme .foster-item {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.foster-item:hover {
    transform: translateX(5px);
    border-color: #3b82f6;
}

.foster-item.active {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.foster-name {
    font-weight: 500;
    color: var(--input-color, #ffffff);
    font-size: 14px;
}

.light-theme .foster-name {
    color: #1e293b;
}

.foster-status {
    display: flex;
    align-items: center;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active { background: #d4edda; color: #155724; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-progress { background: #fff3cd; color: #856404; }
.status-mild { background: #e2e3e5; color: #383d41; }
.status-common { background: #f8d7da; color: #721c24; }

/* Alerts */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--section-border, #3a3a3a);
}

.light-theme .page-header {
    border-bottom: 1px solid #e2e8f0;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--input-color, #ffffff);
    margin: 0;
}

.light-theme .page-title {
    color: #1e293b;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .cards-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
    
    .sidebar-foster-list {
        width: 280px;
    }
}

@media (max-width: 768px) {
    .content {
        flex-direction: column;
    }
    
    .sidebar-foster-list {
        width: 100%;
        height: auto;
        position: static;
        border-right: none;
        border-bottom: 1px solid var(--section-border, #3a3a3a);
    }
    
    .light-theme .sidebar-foster-list {
        border-bottom: 1px solid #e2e8f0;
    }
    
    .matchmaking-content {
        padding: 15px;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .profile-info {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .filters {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-group select {
        min-width: auto;
        width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .compatibility {
        position: static;
        text-align: center;
        margin-top: 10px;
    }
    
    .card-actions {
        flex-direction: column;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .profile-header {
        padding: 20px;
    }
    
    .card {
        padding: 20px;
    }
    
    .avatar, .child-image {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }
    
    .sidebar-foster-list {
        padding: 15px;
    }
}

/* Animation for card appearance */
@keyframes cardAppear {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: cardAppear 0.5s ease forwards;
}

/* Stagger animation for multiple cards */
.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }
.card:nth-child(5) { animation-delay: 0.5s; }
.card:nth-child(6) { animation-delay: 0.6s; }
</style>

<script>
// Enhanced Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const sortFilter = document.getElementById('sortFilter');
    const ageFilter = document.getElementById('ageFilter');
    const genderFilter = document.getElementById('genderFilter');
    const matchesGrid = document.getElementById('matchesGrid');
    const cards = Array.from(matchesGrid.querySelectorAll('.card'));
    
    function filterAndSortMatches() {
        const ageValue = ageFilter.value;
        const genderValue = genderFilter.value;
        const sortValue = sortFilter.value;
        
        let filteredCards = cards.filter(card => {
            const age = parseInt(card.dataset.age);
            const gender = card.dataset.gender;
            
            // Age filter
            if (ageValue !== 'all') {
                if (ageValue === '0-5' && (age < 0 || age > 5)) return false;
                if (ageValue === '6-10' && (age < 6 || age > 10)) return false;
                if (ageValue === '11-15' && (age < 11 || age > 15)) return false;
                if (ageValue === '16+' && age < 16) return false;
            }
            
            // Gender filter
            if (genderValue !== 'all' && gender !== genderValue) return false;
            
            return true;
        });
        
        // Sort cards
        filteredCards.sort((a, b) => {
            switch(sortValue) {
                case 'age':
                    return parseInt(a.dataset.age) - parseInt(b.dataset.age);
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'score':
                default:
                    return parseInt(b.dataset.score) - parseInt(a.dataset.score);
            }
        });
        
        // Update matches count
        document.querySelector('.matches-info').textContent = filteredCards.length + ' compatible matches found';
        
        // Re-append cards in sorted order with animation
        matchesGrid.innerHTML = '';
        filteredCards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
            matchesGrid.appendChild(card);
        });
    }
    
    // Add smooth transitions
    sortFilter.addEventListener('change', function() {
        matchesGrid.style.opacity = '0.7';
        setTimeout(filterAndSortMatches, 300);
        setTimeout(() => { matchesGrid.style.opacity = '1'; }, 300);
    });
    
    ageFilter.addEventListener('change', filterAndSortMatches);
    genderFilter.addEventListener('change', filterAndSortMatches);
    
    // Add hover effects to cards
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

function navigateToChildManagement(childId) {
    window.location.href = 'child-management.php?view_child=' + encodeURIComponent(childId);
}
</script>

<?php require_once 'includes/footer.php'; ?>