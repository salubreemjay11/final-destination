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
                <div class="action-buttons" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    <button class="btn-primary" onclick="window.location.href='new-foster.php'" style="width: 100%;">
                        Add New Adopter
                    </button>
                    <button class="btn-secondary" onclick="window.location.href='foster-details.php?foster_id=<?php echo $fosterId; ?>'" style="width: 100%;">
                        Back to Profile
                    </button>
                    <button class="btn-secondary" onclick="window.location.href='foster-info.php'" style="width: 100%;">
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
                <h1 class="page-title">🤝 Matchmaking: <?php echo htmlspecialchars($foster['name']); ?></h1>
                <div class="header-actions">
                    <span class="current-status">
                        AI Compatibility Analysis Active
                    </span>
                </div>
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
                            <div style="font-weight: bold; margin-bottom: 8px;">Why this match?</div>
                            <div class="match-reasons">
                                <?php foreach ($match['reasons'] as $reason): ?>
                                    <p><?php echo htmlspecialchars($reason); ?></p>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-actions">
                                <form method="POST" style="display: inline; width: 100%;">
                                    <input type="hidden" name="child_id" value="<?php echo $child['child_id']; ?>">
                                    
                                </form>
                                <button class="btn btn-secondary" onclick="navigateToChildManagement('<?php     echo $child['child_id']; ?>')">
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
/* Matchmaking specific styles that integrate with admin/common.css */
.matchmaking-content {
    flex: 1;
    padding: 20px;
}

.dark-theme .profile-header {
    background: var(--section-bg, #1e1e1e);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid var(--section-border, #3a3a3a);
}

.light-theme .profile-header {
    
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.profile-info {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    flex-wrap: wrap;
    gap: 15px;
}

.profile-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
}

.dark-theme .profile-label {
    font-size: 11px;
    opacity: 0.7;
    text-transform: uppercase;
    margin-bottom: 5px;
    color: var(--label-color, #b8c5ff);
}

.light-theme .profile-label {
    font-size: 11px;
    opacity: 0.7;
    text-transform: uppercase;
    margin-bottom: 5px;
    color: #0E7490;
}

.dark-theme .profile-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--input-color, #ffffff);
}

.light-theme .profile-value {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.filters {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.dark-theme .filter-group label {
    font-size: 14px;
    color: var(--label-color, #b8c5ff);
}

.light-theme .filter-group label {
    color: #0E7490;
}

.dark-theme .filter-group select {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid var(--input-border, #3a3a3a);
    background: var(--input-bg, #2a2a2a);
    color: var(--input-color, #ffffff);
    cursor: pointer;
}

.light-theme .filter-group select {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    cursor: pointer;
    padding: 8px 12px;
}

.matches-info {
    text-align: right;
    font-size: 14px;
    margin-bottom: 15px;
    color: var(--label-color, #b8c5ff);
}

.light-theme .matches-info {
    text-align: right;
    font-size: 14px;
    margin-bottom: 15px;
    color: #1e293b;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.dark-theme .card {
    background: var(--section-bg, #1e1e1e);
    border-radius: 8px;
    padding: 20px;
    position: relative;
    border: 1px solid var(--section-border, #3a3a3a);
    transition: transform 0.3s, box-shadow 0.3s;
}

.light-theme .card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 20px;
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.card-header {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.avatar {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    color: white;
}

.card-info h3 {
    font-size: 18px;
    margin-bottom: 5px;
    color: var(--input-color, #ffffff);
}

.dark-theme .card-details {
    font-size: 12px;
    opacity: 0.8;
    margin: 2px 0;
    color: var(--label-color, #b8c5ff);
}

.light-theme .card-details {
    color: #475569;
    font-size: 12px;
    opacity: 0.8;
    margin: 2px 0;
}

.compatibility {
    position: absolute;
    top: 20px;
    right: 20px;
    text-align: right;
    font-size: 11px;
}

.dark-theme .compatibility-label {
    opacity: 0.7;
    color: var(--label-color, #b8c5ff);
}

.light-theme .compatibility-label {
    opacity: 0.7;
    color: #0E7490;
}

.compatibility-status {
    
    font-weight: bold;
}

.compatibility-status.excellent { color: #4caf50; }
.compatibility-status.good { color: #8bc34a; }
.compatibility-status.moderate { color: #ffc107; }
.compatibility-status.fair { color: #ff9800; }
.compatibility-status.low { color: #f44336; }

.match-reasons {
    margin: 15px 0;
    font-size: 12px;
}

.match-reasons p {
    margin: 5px 0;
    padding-left: 15px;
    position: relative;
    color: var(--input-color, #ffffff);
}

.light-theme .match-reasons p {
    margin: 5px 0;
    padding-left: 15px;
    position: relative;
    color: var(--input-color, black);
}

.match-reasons p::before {
    content: "•";
    position: absolute;
    left: 0;
    color: #3b82f6;
}

.card-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary {
    background: #1976d2;
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #059669, #047857);
}

.light-theme .btn-secondary {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
}

.dark-theme .btn-secondary {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.light-theme .btn-secondary:hover {
    background: #047857;
}

.dark-theme .btn-secondary:hover {
    background: #2563eb;
}
.no-matches {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    background: var(--section-bg, #1e1e1e);
    border-radius: 8px;
    border: 1px solid var(--section-border, #3a3a3a);
}

.no-matches h3 {
    color: var(--input-color, #ffffff);
    margin-bottom: 10px;
}

.no-matches p {
    color: var(--label-color, #b8c5ff);
}

@media (max-width: 768px) {
    .profile-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .profile-item {
        text-align: left;
        width: 100%;
    }
    
    .filters {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Filter functionality
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
        
        // Re-append cards in sorted order
        matchesGrid.innerHTML = '';
        filteredCards.forEach(card => matchesGrid.appendChild(card));
    }
    
    sortFilter.addEventListener('change', filterAndSortMatches);
    ageFilter.addEventListener('change', filterAndSortMatches);
    genderFilter.addEventListener('change', filterAndSortMatches);
});

function viewChildDetails(childId) {
    window.open('child-details.php?child_id=' + childId, '_blank');
}
function navigateToChildManagement(childId) {
    // Navigate to child management page with the child ID as parameter
    window.location.href = 'child-management.php?view_child=' + encodeURIComponent(childId);
}
</script>

<?php require_once 'includes/footer.php'; ?>