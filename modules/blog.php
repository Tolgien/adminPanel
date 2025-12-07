<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$page_title = "Blog Yönetimi";
ob_start();
?>

<div class="container-fluid">
    <!-- Başlık ve Butonlar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Blog Yazıları</h1>
        <div>
            <a href="blog-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Yazı
            </a>
            <a href="blog-categories.php" class="btn btn-outline-secondary">
                <i class="fas fa-folder"></i> Kategoriler
            </a>
            <a href="blog-tags.php" class="btn btn-outline-secondary">
                <i class="fas fa-tags"></i> Etiketler
            </a>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Yayın Durumu</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">Tümü</option>
                        <option value="taslak">Taslak</option>
                        <option value="yayinda">Yayında</option>
                        <option value="beklemede">Beklemede</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Arama</label>
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Başlık, içerik veya yazar ara...">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-secondary w-100" id="resetFilters">
                        <i class="fas fa-redo"></i> Sıfırla
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Yazıları Tablosu -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="blogTable">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th>Yazar</th>
                            <th>Durum</th>
                            <th>Okunma</th>
                            <th>Tarih</th>
                            <th width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="blogTableBody">
                        <tr id="loadingRow">
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Yükleniyor...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Sayfalama -->
            <nav aria-label="Sayfalama" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yazıyı Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu yazıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
                <input type="hidden" id="deletePostId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Sil</button>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı Düzenle Modalı -->
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hızlı Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editPostId">
                
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" id="editTitle" class="form-control" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select id="editCategory" class="form-select">
                            <option value="">Kategori Seçin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durum</label>
                        <select id="editStatus" class="form-select">
                            <option value="taslak">Taslak</option>
                            <option value="yayinda">Yayında</option>
                            <option value="beklemede">Beklemede</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="saveQuickEdit">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/header.php';
?>

<div class="container-fluid">
    <?php 
    if (file_exists('../includes/sidebar.php')) {
        require_once '../includes/sidebar.php';
    }
    ?>
    
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>

<?php 
require_once '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const limit = 10;
    let currentFilters = {
        category_id: '',
        status: '',
        search: ''
    };

    const blogTableBody = document.getElementById('blogTableBody');
    const pagination = document.getElementById('pagination');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const resetFilters = document.getElementById('resetFilters');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const quickEditModal = new bootstrap.Modal(document.getElementById('quickEditModal'));

    loadCategories();
    loadBlogPosts();

    categoryFilter.addEventListener('change', function() {
        currentFilters.category_id = this.value;
        currentPage = 1;
        loadBlogPosts();
    });

    statusFilter.addEventListener('change', function() {
        currentFilters.status = this.value;
        currentPage = 1;
        loadBlogPosts();
    });

    searchBtn.addEventListener('click', function() {
        currentFilters.search = searchInput.value;
        currentPage = 1;
        loadBlogPosts();
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentFilters.search = this.value;
            currentPage = 1;
            loadBlogPosts();
        }
    });

    resetFilters.addEventListener('click', function() {
        categoryFilter.value = '';
        statusFilter.value = '';
        searchInput.value = '';
        currentFilters = { category_id: '', status: '', search: '' };
        currentPage = 1;
        loadBlogPosts();
    });

    function loadCategories() {
        fetch('/admin/api/blog/categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categories = data.data;
                    const categoryFilter = document.getElementById('categoryFilter');
                    const editCategory = document.getElementById('editCategory');
                    
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.kategori_adi;
                        categoryFilter.appendChild(option);
                        
                        const editOption = option.cloneNode(true);
                        editCategory.appendChild(editOption);
                    });
                }
            })
            .catch(error => console.error('Kategoriler yüklenirken hata:', error));
    }

    function loadBlogPosts() {
        showLoading(true);
        
        let url = `../api/blog/posts.php?page=${currentPage}&limit=${limit}`;
        
        if (currentFilters.category_id) {
            url += `&category_id=${currentFilters.category_id}`;
        }
        if (currentFilters.status) {
            url += `&status=${currentFilters.status}`;
        }
        if (currentFilters.search) {
            url += `&search=${encodeURIComponent(currentFilters.search)}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    renderBlogTable(data.data);
                    renderPagination(data.pagination);
                } else {
                    showError('Veriler yüklenirken bir hata oluştu.');
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Sunucu hatası: ' + error.message);
            });
    }

    function renderBlogTable(posts) {
        blogTableBody.innerHTML = '';
        
        if (posts.length === 0) {
            blogTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Henüz blog yazısı bulunmamaktadır.</p>
                        <a href="blog-add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> İlk Yazınızı Ekleyin
                        </a>
                    </td>
                </tr>
            `;
            return;
        }
        
        posts.forEach(post => {
            const row = document.createElement('tr');
            
            let statusBadge = '';
            switch(post.yayin_durumu) {
                case 'yayinda':
                    statusBadge = '<span class="badge bg-success">Yayında</span>';
                    break;
                case 'taslak':
                    statusBadge = '<span class="badge bg-warning">Taslak</span>';
                    break;
                case 'beklemede':
                    statusBadge = '<span class="badge bg-secondary">Beklemede</span>';
                    break;
            }
            
            const date = new Date(post.created_at);
            const formattedDate = date.toLocaleDateString('tr-TR');
            
            row.innerHTML = `
                <td>${post.id}</td>
                <td>
                    <strong>${post.baslik}</strong>
                    ${post.etiketler ? `<br><small class="text-muted">${post.etiketler}</small>` : ''}
                </td>
                <td>${post.kategori_adi || '-'}</td>
                <td>${post.yazar_adi || '-'}</td>
                <td>${statusBadge}</td>
                <td>${post.okunma_sayisi}</td>
                <td>${formattedDate}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="blog-edit.php?id=${post.id}" class="btn btn-outline-primary" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-outline-secondary quick-edit-btn" 
                                data-id="${post.id}"
                                data-title="${post.baslik}"
                                data-category="${post.kategori_id || ''}"
                                data-status="${post.yayin_durumu}"
                                title="Hızlı Düzenle">
                            <i class="fas fa-bolt"></i>
                        </button>
                        <a href="#" class="btn btn-outline-info" target="_blank" title="Önizleme">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-outline-danger delete-btn" 
                                data-id="${post.id}"
                                data-title="${post.baslik}"
                                title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            blogTableBody.appendChild(row);
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const postTitle = this.getAttribute('data-title');
                
                document.getElementById('deletePostId').value = postId;
                document.querySelector('#deleteModal .modal-body p').textContent = 
                    `"${postTitle}" başlıklı yazıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`;
                
                deleteModal.show();
            });
        });
        
        document.querySelectorAll('.quick-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const postTitle = this.getAttribute('data-title');
                const categoryId = this.getAttribute('data-category');
                const status = this.getAttribute('data-status');
                
                document.getElementById('editPostId').value = postId;
                document.getElementById('editTitle').value = postTitle;
                document.getElementById('editCategory').value = categoryId;
                document.getElementById('editStatus').value = status;
                
                quickEditModal.show();
            });
        });
    }

    function renderPagination(pagination) {
        pagination.innerHTML = '';
        
        const totalPages = pagination.pages;
        
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" ${currentPage !== 1 ? 'onclick="changePage(' + (currentPage - 1) + ')"' : ''}>
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
        pagination.appendChild(prevLi);
        
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const li = document.createElement('li');
                li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
                pagination.appendChild(li);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = `<span class="page-link">...</span>`;
                pagination.appendChild(li);
            }
        }
        
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" ${currentPage !== totalPages ? 'onclick="changePage(' + (currentPage + 1) + ')"' : ''}>
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
        pagination.appendChild(nextLi);
    }

    window.changePage = function(page) {
        currentPage = page;
        loadBlogPosts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    document.getElementById('confirmDelete').addEventListener('click', function() {
        const postId = document.getElementById('deletePostId').value;
        
        fetch(`../api/blog/posts.php?id=${postId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteModal.hide();
                showSuccess('Yazı başarıyla silindi.');
                loadBlogPosts();
            } else {
                showError('Silme işlemi başarısız: ' + data.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });

    document.getElementById('saveQuickEdit').addEventListener('click', function() {
        const postId = document.getElementById('editPostId').value;
        const title = document.getElementById('editTitle').value;
        const categoryId = document.getElementById('editCategory').value;
        const status = document.getElementById('editStatus').value;
        
        if (!title.trim()) {
            showError('Başlık boş olamaz.');
            return;
        }
        
        const data = {
            baslik: title,
            kategori_id: categoryId || null,
            yayin_durumu: status
        };
        
        fetch(`../api/blog/posts.php?id=${postId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                quickEditModal.hide();
                showSuccess('Yazı başarıyla güncellendi.');
                loadBlogPosts();
            } else {
                showError('Güncelleme başarısız: ' + result.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });

    function showLoading(show) {
        if (show) {
            blogTableBody.innerHTML = `
                <tr id="loadingRow">
                    <td colspan="8" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: message
        });
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Başarılı',
            text: message,
            timer: 2000
        });
    }
});
</script>