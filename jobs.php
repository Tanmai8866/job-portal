<?php
require_once 'config/session.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$location_filter = $_GET['location'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(j.title LIKE ? OR j.description LIKE ? OR u.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($type_filter)) {
    $where_conditions[] = "j.type = ?";
    $params[] = $type_filter;
}

if (!empty($location_filter)) {
    $where_conditions[] = "j.location LIKE ?";
    $params[] = "%$location_filter%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM jobs j JOIN users u ON j.employer_id = u.id $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_jobs = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_jobs / $per_page);

// Get jobs
$query = "SELECT j.*, u.name as company_name FROM jobs j 
          JOIN users u ON j.employer_id = u.id 
          $where_clause 
          ORDER BY j.created_at DESC 
          LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique job types and locations for filters
$types_query = "SELECT DISTINCT type FROM jobs ORDER BY type";
$types_stmt = $db->prepare($types_query);
$types_stmt->execute();
$job_types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

$locations_query = "SELECT DISTINCT location FROM jobs ORDER BY location";
$locations_stmt = $db->prepare($locations_query);
$locations_stmt->execute();
$job_locations = $locations_stmt->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Browse Jobs - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <h1 style="margin-bottom: 2rem; color: #1f2937;">Browse Jobs</h1>

        <!-- Search and Filters -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search jobs, companies, or keywords..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn">Search</button>
            </form>

            <div class="filters">
                <select name="type" id="type-filter" class="filter-select" onchange="applyFilters()">
                    <option value="">All Job Types</option>
                    <?php foreach ($job_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" 
                                <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="location" id="location-filter" class="filter-select" onchange="applyFilters()">
                    <option value="">All Locations</option>
                    <?php foreach ($job_locations as $location): ?>
                        <option value="<?php echo htmlspecialchars($location); ?>" 
                                <?php echo $location_filter === $location ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($location); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" onclick="clearFilters()" class="btn btn-secondary">Clear Filters</button>
            </div>
        </div>

        <!-- Results Summary -->
        <div style="margin: 2rem 0; color: #6b7280;">
            Showing <?php echo count($jobs); ?> of <?php echo $total_jobs; ?> jobs
            <?php if (!empty($search)): ?>
                for "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </div>

        <!-- Job Listings -->
        <?php if (empty($jobs)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No jobs found</h3>
                <p style="color: #9ca3af;">Try adjusting your search criteria or check back later for new opportunities.</p>
                <a href="jobs.php" class="btn" style="margin-top: 1rem;">View All Jobs</a>
            </div>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                        <div class="job-meta">
                            <span class="job-tag"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['type']))); ?></span>
                            <span class="job-tag"><?php echo htmlspecialchars($job['location']); ?></span>
                            <?php if ($job['salary']): ?>
                                <span class="job-tag">$<?php echo htmlspecialchars(number_format($job['salary'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="job-description">
                            <?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <span style="color: #6b7280; font-size: 0.875rem;">
                                Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                            </span>
                            <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin: 3rem 0;">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                           class="btn btn-secondary">← Previous</a>
                    <?php endif; ?>

                    <span style="color: #6b7280;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                           class="btn btn-secondary">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function applyFilters() {
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '';

    // Add current search term
    const search = document.getElementById('search');
    if (search && search.value) {
        const searchInput = document.createElement('input');
        searchInput.type = 'hidden';
        searchInput.name = 'search';
        searchInput.value = search.value;
        form.appendChild(searchInput);
    }

    // Add type filter
    const typeFilter = document.getElementById('type-filter');
    if (typeFilter && typeFilter.value) {
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = typeFilter.value;
        form.appendChild(typeInput);
    }

    // Add location filter
    const locationFilter = document.getElementById('location-filter');
    if (locationFilter && locationFilter.value) {
        const locationInput = document.createElement('input');
        locationInput.type = 'hidden';
        locationInput.name = 'location';
        locationInput.value = locationFilter.value;
        form.appendChild(locationInput);
    }

    document.body.appendChild(form);
    form.submit();
}

function clearFilters() {
    window.location.href = 'jobs.php';
}
</script>

<?php include 'includes/footer.php'; ?>