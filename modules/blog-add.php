<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /admin/index.php');  // DÜZELTİLDİ
    exit();
}

$page_title = "Yeni Blog Yazısı";
ob_start();
?>

<div class="container-fluid">
    <!-- Başlık ve Butonlar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Yeni Blog Yazısı</h1>
        <div>
            <a href="/admin/modules/blog.php" class="btn btn-outline-secondary">  <!-- DÜZELTİLDİ -->
                <i class="fas fa-arrow-left"></i> Listeye Dön
            </a>
        </div>
    </div>

    <!-- Blog Yazısı Formu -->
    <form id="blogPostForm">
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
                            <input type="text" name="meta_baslik" class="form-control" 
                                   placeholder="Sayfa başlığı (SEO için)">
                            <small class="form-text text-muted">Eğer boş bırakılırsa, yazı başlığı kullanılır.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Meta Açıklama</label>
                            <textarea name="meta_aciklama" class="form-control" rows="2"></textarea>
                            <small class="form-text text-muted">Eğer boş bırakılırsa, özet kullanılır.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Anahtar Kelimeler</label>
                            <input type="text" name="meta_anahtar_kelimeler" class="form-control" 
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
                            <select name="yayin_durumu" class="form-select" required>
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
                            <input type="number" name="okunma_suresi" class="form-control" min="1" max="60" 
                                   placeholder="Örn: 5">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Yazıyı Kaydet
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
    
    // Modal instances
    const imageUploadModal = new bootstrap.Modal(document.getElementById('imageUploadModal'));
    const imageFile = document.getElementById('imageFile');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    
    // Seçili etiketler array
    let selectedTagsArray = [];
    
    // İlk yükleme
    loadCategories();
    loadPopularTags();
    initEditor();
    
    // Başlık değiştiğinde slug oluştur
    postTitle.addEventListener('input', function() {
        const title = this.value;
        const slug = createSlug(title);
        slugPreview.textContent = 'Slug: ' + slug;
    });
    
    // Kategorileri yükle
    function loadCategories() {
        fetch('/admin/api/blog/categories.php')  // DÜZELTİLDİ
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
        fetch('/admin/api/blog/tags.php')  // DÜZELTİLDİ
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
    
    // Kapak resmi yükleme
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
    
    // Resim önizleme
    imageFile.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                showError('Lütfen sadece resim dosyası seçin.');
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                showError('Resim boyutu 2MB\'dan küçük olmalıdır.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Resim yükleme
    uploadImageBtn.addEventListener('click', function() {
        const file = imageFile.files[0];
        if (!file) {
            showError('Lütfen bir resim seçin.');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('folder', 'blog');
        
        uploadProgress.style.display = 'block';
        uploadProgress.querySelector('.progress-bar').style.width = '0%';
        
        fetch('/admin/api/upload.php', {  // DÜZELTİLDİ
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                coverImageInput.value = data.file_path;
                coverImagePreview.innerHTML = `
                    <img src="${data.file_path}" class="img-fluid rounded" style="max-height: 150px;">
                    <p class="mt-2 mb-0">${file.name}</p>
                `;
                removeCoverImageBtn.style.display = 'block';
                uploadCoverImageBtn.textContent = '<i class="fas fa-sync"></i> Resmi Değiştir';
                
                imageUploadModal.hide();
                imageFile.value = '';
                imagePreviewContainer.style.display = 'none';
                uploadProgress.style.display = 'none';
                
                showSuccess('Resim başarıyla yüklendi.');
            } else {
                showError('Resim yüklenemedi: ' + data.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
            uploadProgress.style.display = 'none';
        });
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
        // Form verilerini topla
        const formData = new FormData(blogPostForm);
        const data = Object.fromEntries(formData.entries());
        
        // Tinymce içeriğini al
        if (typeof tinymce !== 'undefined') {
            data.icerik = tinymce.get('postContent').getContent();
        }
        
        // Etiketleri ekle
        data.etiketler = selectedTagsArray;
        
        // Taslak ise durumu güncelle
        if (isDraft) {
            data.yayin_durumu = 'taslak';
        }
        
        // Sayısal değerleri convert et
        data.okunma_suresi = data.okunma_suresi ? parseInt(data.okunma_suresi) : null;
        data.kategori_id = data.kategori_id ? parseInt(data.kategori_id) : null;
        
        fetch('/admin/api/blog/posts.php', {  // DÜZELTİLDİ
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess(isDraft ? 'Yazı taslak olarak kaydedildi.' : 'Yazı başarıyla yayınlandı.');
                
                if (!isDraft) {
                    setTimeout(() => {
                        window.location.href = '/admin/modules/blog.php';  // DÜZELTİLDİ
                    }, 1500);
                }
            } else {
                showError('Kayıt başarısız: ' + result.error);
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
    
    // Etiket badge'lerine hover efekti
    document.addEventListener('mouseover', function(e) {
        if (e.target.classList.contains('tag-badge')) {
            e.target.style.opacity = '0.8';
        }
    });
    
    document.addEventListener('mouseout', function(e) {
        if (e.target.classList.contains('tag-badge')) {
            e.target.style.opacity = '1';
        }
    });
});
</script>