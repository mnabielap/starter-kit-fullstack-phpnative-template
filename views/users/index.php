<?php use App\Utils\Helper; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">User Management</h5>
                    <a href="<?php echo Helper::baseUrl('users/create'); ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> Create New User
                    </a>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-6">
                            <div class="search-box">
                                <input type="text" class="form-control search" id="searchName" placeholder="Search by name...">
                                <i class="bi bi-search search-icon"></i>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <div>
                                <select class="form-select" id="filterRole">
                                    <option value="">All Roles</option>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <div>
                                <select class="form-select" id="sortBy">
                                    <option value="created_at:desc">Newest</option>
                                    <option value="created_at:asc">Oldest</option>
                                    <option value="name:asc">Name (A-Z)</option>
                                    <option value="name:desc">Name (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xxl-1 col-sm-4">
                            <button type="button" class="btn btn-primary w-100" onclick="resetPageAndLoad()">
                                <i class="bi bi-funnel align-bottom me-1"></i> Filter
                            </button>
                        </div>
                         <div class="col-xxl-1 col-sm-4">
                            <button type="button" class="btn btn-light w-100" onclick="clearFilters()">
                                Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
                            <tr>
                                <td colspan="6" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="row align-items-center mt-4">
                    <div class="col-sm">
                        <div class="text-muted text-center text-sm-start">
                            Showing <span class="fw-semibold" id="pageStart">0</span> to <span class="fw-semibold" id="pageEnd">0</span> of <span class="fw-semibold" id="totalResults">0</span> Results
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <ul class="pagination pagination-separated pagination-sm justify-content-center justify-content-sm-end mb-0">
                            <li class="page-item disabled" id="prevBtn">
                                <a href="javascript:void(0);" class="page-link" onclick="changePage(-1)">Previous</a>
                            </li>
                            <li class="page-item active">
                                <a href="javascript:void(0);" class="page-link" id="currentPageDisplay">1</a>
                            </li>
                            <li class="page-item disabled" id="nextBtn">
                                <a href="javascript:void(0);" class="page-link" onclick="changePage(1)">Next</a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    // State Management
    let currentPage = 1;
    const limit = 10;
    let totalPages = 1;

    // Helper to format date
    function formatDate(dateString) {
        if(!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            day: 'numeric', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    // Reset page to 1 when filtering
    function resetPageAndLoad() {
        currentPage = 1;
        loadUsers();
    }

    // Clear all filters
    function clearFilters() {
        document.getElementById('searchName').value = '';
        document.getElementById('filterRole').value = '';
        document.getElementById('sortBy').value = 'created_at:desc';
        resetPageAndLoad();
    }

    // Change Page
    function changePage(delta) {
        const newPage = currentPage + delta;
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            loadUsers();
        }
    }

    // Main Fetch Function
    async function loadUsers() {
        // 1. Collect Values
        const name = document.getElementById('searchName').value;
        const role = document.getElementById('filterRole').value;
        const sortBy = document.getElementById('sortBy').value;

        // 2. Build Query String
        const params = new URLSearchParams({
            page: currentPage,
            limit: limit,
            sortBy: sortBy
        });

        if (name) params.append('name', name);
        if (role) params.append('role', role);

        try {
            // 3. Fetch API
            const response = await API.fetch(`/v1/users?${params.toString()}`);
            const data = await response.json();

            if (response.ok) {
                const tbody = document.querySelector('#usersTable tbody');
                tbody.innerHTML = '';
                
                // Update Pagination Metadata
                totalPages = data.totalPages;
                const start = (data.page - 1) * data.limit + 1;
                const end = Math.min(start + data.limit - 1, data.totalResults);
                
                document.getElementById('pageStart').innerText = data.totalResults > 0 ? start : 0;
                document.getElementById('pageEnd').innerText = data.totalResults > 0 ? end : 0;
                document.getElementById('totalResults').innerText = data.totalResults;
                document.getElementById('currentPageDisplay').innerText = data.page;

                // Update Pagination Buttons
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                
                if (data.page <= 1) prevBtn.classList.add('disabled'); else prevBtn.classList.remove('disabled');
                if (data.page >= data.totalPages) nextBtn.classList.add('disabled'); else nextBtn.classList.remove('disabled');

                // Render Table Rows
                if (data.results.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5">No users found matching your criteria.</td></tr>';
                    return;
                }

                data.results.forEach(user => {
                    const editUrl = `${API.baseUrl}/users/edit?id=${user.id}`;
                    
                    const tr = `
                        <tr>
                            <td><a href="#" class="fw-medium">#${user.id}</a></td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td><span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-success'} text-uppercase">${user.role}</span></td>
                            <td>${formatDate(user.created_at)}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="${editUrl}" class="btn btn-sm btn-soft-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-soft-danger" onclick="deleteUser('${user.id}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += tr;
                });
            } else {
                if(data.code === 403) {
                     document.querySelector('.card-body').innerHTML = '<div class="alert alert-warning">Access Denied: Admin role required.</div>';
                } else {
                    console.error("Failed to load users", data);
                }
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            const response = await API.fetch(`/v1/users/${userId}`, {
                method: 'DELETE'
            });

            if (response.ok || response.status === 204) {
                loadUsers(); // Refresh data
            } else {
                const data = await response.json();
                alert('Failed to delete: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred while deleting.');
        }
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
    });
</script>

<style>
.search-box { position: relative; }
.search-box .search { padding-left: 40px; }
.search-box .search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #878a99;
}
</style>
<?php $script = ob_get_clean(); ?>