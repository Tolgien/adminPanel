<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit();
}

$post_id = intval($_GET['id']);
$page_title = "Blog Yazısını Düzenle";

ob_start();
?>

<div class="container-fluid">
    <!-- Başlık ve Butonlar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Blog Yazısını Düzenle</h1>
        <div>
            <a href="blog.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Listeye Dön
            </a>
        </div>
    </div>

    <!-- Blog Yazısı Formu -->
    <form id="blogPostForm">
        <input type="hidden" name="id" value="<?php echo $post_id; ?>">
        
        <div class="row">
            <!-- Sol Kolon: İçerik -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Başlık *</label>
                            <input type="text" name="baslik" id="postTitle" class="form-control form-control-lg" 
                                   placeholder="Yazı başlığını girin" required>
                            <small class="form-text text-muted" id="slugPreview"></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Özet (Meta Açıklama)</label>
                            <textarea name="ozet" id="postExcerpt" class="form-control" rows="3" 
                                      placeholder="Yazının kısa özetini girin"></textarea>
                            <small class="form-text text-muted">Bu özet SEO ve sosyal medya paylaşımlarında görünecektir.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">İçerik *</label>
                            <textarea name="icerik" id="postContent" class="form-control" rows="15" required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Ayarları -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">SEO Ayarları</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Meta Başlık</label>
                            <input type="text" name="meta_baslik" id="metaTitle" class="form-control" 
                                   placeholder="Sayfa başlığı (SEO için)">
                            <small class="form-text text-muted">Eğer boş bırakılırsa, yazı başlığı kullanılır.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Meta Açıklama</label>
                            <textarea name="meta_aciklama" id="metaDescription" class="form-control" rows="2"></textarea>
                            <small class="form-text text-muted">Eğer boş bırakılırsa, özet kullanılır.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Anahtar Kelimeler</label>
                            <input type="text" name="meta_anahtar_kelimeler" id="metaKeywords" class="form-control" 
                                   placeholder="kelime1, kelime2, kelime3">
                            <small class="form-text text-muted">Virgülle ayırarak anahtar kelimeleri girin.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Kolon: Ayarlar -->
            <div class="col-lg-4">
                <!-- Yayın Ayarları -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Yayın Ayarları</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select name="yayin_durumu" id="postStatus" class="form-select" required>
                                <option value="taslak">Taslak</option>
                                <option value="yayinda">Yayında</option>
                                <option value="beklemede">Beklemede</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori_id" class="form-select" id="categorySelect">
                                <option value="">Kategori Seçin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Okunma Süresi (dakika)</label>
                            <input type="number" name="okunma_suresi" id="readTime" class="form-control" min="1" max="60" 
                                   placeholder="Örn: 5">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Değişiklikleri Kaydet
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                                <i class="fas fa-file-alt"></i> Taslak Olarak Kaydet
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Kapak Resmi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Kapak Resmi</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3" id="coverImagePreview">
                            <i class="fas fa-image fa-4x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Kapak resmi seçilmedi</p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="uploadCoverImageBtn">
                                <i class="fas fa-upload"></i> Resim Yükle
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="removeCoverImageBtn" style="display: none;">
                                <i class="fas fa-trash"></i> Resmi Kaldır
                            </button>
                        </div>
                        
                        <input type="hidden" name="kapak_resmi" id="coverImageInput">
                        
                        <small class="form-text text-muted mt-2 d-block">
                            Önerilen boyut: 1200x630 px<br>
                            Max boyut: 2 MB
                        </small>
                    </div>
                </div>
                
                <!-- İstatistikler -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">İstatistikler</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="display-6 fw-bold" id="viewCount">0</div>
                                <small class="text-muted">Görüntülenme</small>
                            </div>
                            <div class="col-6">
                                <div class="display-6 fw-bold" id="readTimeDisplay">0</div>
                                <small class="text-muted">Dakika</small>
                            </div>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <div class="mb-1">
                                <i class="fas fa-calendar-plus"></i>
                                Oluşturulma: <span id="createdAt">-</span>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check"></i>
                                Son güncelleme: <span id="updatedAt">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Etiketler -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Etiketler</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Etiket Ekle</label>
                            <div class="input-group">
                                <input type="text" id="tagInput" class="form-control" placeholder="Etiket yazın">
                                <button class="btn btn-outline-secondary" type="button" id="addTagBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="selectedTags" class="mb-3">
                            <p class="text-muted mb-0">Henüz etiket eklenmedi</p>
                        </div>
                        
                        <div id="popularTags" class="mt-3">
                            <label class="form-label">Popüler Etiketler</label>
                            <div class="d-flex flex-wrap gap-2" id="popularTagsList">
                                <span class="badge bg-light text-dark cursor-pointer tag-badge">yükleniyor...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Resim Yükleme Modalı -->
<div class="modal fade" id="imageUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resim Yükle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Resim Seç</label>
                    <input type="file" id="imageFile" class="form-control" accept="image/*">
                    <div class="progress mt-2" style="display: none;" id="uploadProgress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="text-center" id="imagePreviewContainer" style="display: none;">
                    <img id="imagePreview" class="img-fluid rounded" style="max-height: 300px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="uploadImageBtn">Yükle</button>
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

<!-- Tinymce Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elementleri
    const blogPostForm = document.getElementById('blogPostForm');
    const postTitle = document.getElementById('postTitle');
    const slugPreview = document.getElementById('slugPreview');
    const coverImagePreview = document.getElementById('coverImagePreview');
    const coverImageInput = document.getElementById('coverImageInput');
    const uploadCoverImageBtn = document.getElementById('uploadCoverImageBtn');
    const removeCoverImageBtn = document.getElementById('removeCoverImageBtn');
    const tagInput = document.getElementById('tagInput');
    const addTagBtn = document.getElementById('addTagBtn');
    const selectedTags = document.getElementById('selectedTags');
    const popularTagsList = document.getElementById('popularTagsList');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const categorySelect = document.getElementById('categorySelect');
    
    // İstatistik elementleri
    const viewCount = document.getElementById('viewCount');
    const readTimeDisplay = document.getElementById('readTimeDisplay');
    const createdAt = document.getElementById('createdAt');
    const updatedAt = document.getElementById('updatedAt');
    
    // Seçili etiketler array
    let selectedTagsArray = [];
    
    // İlk yükleme
    loadPostData(<?php echo $post_id; ?>);
    loadCategories();
    loadPopularTags();
    initEditor();
    
    // Başlık değiştiğinde slug oluştur
    postTitle.addEventListener('input', function() {
        const title = this.value;
        const slug = createSlug(title);
        slugPreview.textContent = 'Slug: ' + slug;
    });
    
    // Yazı verilerini yükle
    function loadPostData(postId) {
        fetch(`../api/blog/posts.php?id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const post = data.data[0];
                    
                    document.getElementById('postTitle').value = post.baslik;
                    document.getElementById('postExcerpt').value = post.ozet || '';
                    document.getElementById('metaTitle').value = post.meta_baslik || '';
                    document.getElementById('metaDescription').value = post.meta_aciklama || '';
                    document.getElementById('metaKeywords').value = post.meta_anahtar_kelimeler || '';
                    document.getElementById('postStatus').value = post.yayin_durumu;
                    document.getElementById('readTime').value = post.okunma_suresi || '';
                    
                    if (post.kategori_id) {
                        document.getElementById('categorySelect').value = post.kategori_id;
                    }
                    
                    if (post.kapak_resmi) {
                        coverImageInput.value = post.kapak_resmi;
                        coverImagePreview.innerHTML = `
                            <img src="${post.kapak_resmi}" class="img-fluid rounded" style="max-height: 150px;">
                            <p class="mt-2 mb-0">Kapak resmi</p>
                        `;
                        removeCoverImageBtn.style.display = 'block';
                        uploadCoverImageBtn.textContent = '<i class="fas fa-sync"></i> Resmi Değiştir';
                    }
                    
                    if (post.etiketler) {
                        selectedTagsArray = post.etiketler.split(', ').map(tag => tag.trim());
                        renderSelectedTags();
                    }
                    
                    viewCount.textContent = post.okunma_sayisi || 0;
                    readTimeDisplay.textContent = post.okunma_suresi || 0;
                    
                    const createdDate = new Date(post.created_at);
                    const updatedDate = new Date(post.updated_at);
                    
                    createdAt.textContent = createdDate.toLocaleDateString('tr-TR');
                    updatedAt.textContent = updatedDate.toLocaleDateString('tr-TR');
                    
                    slugPreview.textContent = 'Slug: ' + post.slug;
                    
                    setTimeout(() => {
                        if (typeof tinymce !== 'undefined') {
                            tinymce.get('postContent').setContent(post.icerik || '');
                        }
                    }, 500);
                    
                } else {
                    showError('Yazı bulunamadı.');
                    setTimeout(() => {
                        window.location.href = 'blog.php';
                    }, 2000);
                }
            })
            .catch(error => {
                showError('Veriler yüklenirken hata: ' + error.message);
            });
    }
    
    // Kategorileri yükle
    function loadCategories() {
        fetch('../api/blog/categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.kategori_adi;
                        categorySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Kategoriler yüklenirken hata:', error));
    }
    
    // Popüler etiketleri yükle
    function loadPopularTags() {
        fetch('../api/blog/tags.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    popularTagsList.innerHTML = '';
                    
                    data.data.forEach(tag => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-light text-dark cursor-pointer tag-badge';
                        badge.textContent = tag.etiket_adi;
                        badge.style.cursor = 'pointer';
                        badge.title = tag.kullanım_sayisi + ' yazıda kullanılmış';
                        
                        badge.addEventListener('click', function() {
                            addTagToSelected(tag.etiket_adi);
                        });
                        
                        popularTagsList.appendChild(badge);
                    });
                }
            })
            .catch(error => console.error('Etiketler yüklenirken hata:', error));
    }
    
    // Editor'ü başlat
    function initEditor() {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#postContent',
                height: 500,
                menubar: true,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
            });
        }
    }
    
    // Etiket ekleme
    addTagBtn.addEventListener('click', function() {
        const tagText = tagInput.value.trim();
        if (tagText) {
            addTagToSelected(tagText);
            tagInput.value = '';
        }
    });
    
    tagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const tagText = this.value.trim();
            if (tagText) {
                addTagToSelected(tagText);
                this.value = '';
            }
        }
    });
    
    function addTagToSelected(tagText) {
        if (!selectedTagsArray.includes(tagText)) {
            selectedTagsArray.push(tagText);
            renderSelectedTags();
        }
    }
    
    function removeTagFromSelected(tagText) {
        const index = selectedTagsArray.indexOf(tagText);
        if (index > -1) {
            selectedTagsArray.splice(index, 1);
            renderSelectedTags();
        }
    }
    
    function renderSelectedTags() {
        selectedTags.innerHTML = '';
        
        if (selectedTagsArray.length === 0) {
            selectedTags.innerHTML = '<p class="text-muted mb-0">Henüz etiket eklenmedi</p>';
            return;
        }
        
        const tagsContainer = document.createElement('div');
        tagsContainer.className = 'd-flex flex-wrap gap-2';
        
        selectedTagsArray.forEach(tag => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-flex align-items-center';
            badge.innerHTML = `
                ${tag}
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.5rem;"></button>
            `;
            
            badge.querySelector('button').addEventListener('click', function() {
                removeTagFromSelected(tag);
            });
            
            tagsContainer.appendChild(badge);
        });
        
        selectedTags.appendChild(tagsContainer);
    }
    
    // Kapak resmi yükleme (blog-add.php'deki kodun aynısı)
    uploadCoverImageBtn.addEventListener('click', function() {
        imageUploadModal.show();
    });
    
    removeCoverImageBtn.addEventListener('click', function() {
        coverImageInput.value = '';
        coverImagePreview.innerHTML = `
            <i class="fas fa-image fa-4x text-muted mb-2"></i>
            <p class="text-muted mb-0">Kapak resmi seçilmedi</p>
        `;
        removeCoverImageBtn.style.display = 'none';
        uploadCoverImageBtn.textContent = '<i class="fas fa-upload"></i> Resim Yükle';
    });
    
    // Form gönderimi
    blogPostForm.addEventListener('submit', function(e) {
        e.preventDefault();
        savePost(false);
    });
    
    saveDraftBtn.addEventListener('click', function() {
        savePost(true);
    });
    
    function savePost(isDraft) {
        const formData = new FormData(blogPostForm);
        const data = Object.fromEntries(formData.entries());
        
        if (typeof tinymce !== 'undefined') {
            data.icerik = tinymce.get('postContent').getContent();
        }
        
        data.etiketler = selectedTagsArray;
        
        if (isDraft) {
            data.yayin_durumu = 'taslak';
        }
        
        data.okunma_suresi = data.okunma_suresi ? parseInt(data.okunma_suresi) : null;
        data.kategori_id = data.kategori_id ? parseInt(data.kategori_id) : null;
        const postId = data.id;
        delete data.id;
        
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
                showSuccess(isDraft ? 'Yazı taslak olarak kaydedildi.' : 'Yazı başarıyla güncellendi.');
                
                if (!isDraft) {
                    loadPostData(postId);
                }
            } else {
                showError('Güncelleme başarısız: ' + result.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    }
    
    // Yardımcı fonksiyonlar
    function createSlug(text) {
        text = text.toLowerCase();
        text = text.replace(/[^a-z0-9]+/g, '-');
        text = text.replace(/^-+|-+$/g, '');
        return text;
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