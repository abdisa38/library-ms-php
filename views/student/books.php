<?php
/**
 * Student - Browse Books
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('student');

$pageTitle = 'Browse Books';
$book = new Book();
$category = new Category();

// Get filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;

$filters = ['search' => $search, 'category_id' => $categoryFilter];

$totalBooks = $book->count($filters);
$pagination = paginate($page, $totalBooks);
$books = $book->getAll($pagination['limit'], $pagination['offset'], $filters);
$categories = $category->getAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-book"></i> Browse Books
    </h1>
</div>

<!-- Search & Filter -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row" style="align-items: flex-end;">
            <div class="form-group" style="flex: 2;">
                <label class="form-label">Search Books</label>
                <input type="text" name="search" class="form-control" placeholder="Search by title, ISBN, or author..." value="<?= e($search) ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a href="books.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Books Grid -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Books (<?= $totalBooks ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($books)): ?>
            <div style="text-align: center; padding: 3rem;">
                <i class="fas fa-book" style="font-size: 4rem; color: #ccc;"></i>
                <h3 style="margin-top: 1rem;">No books found</h3>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
                <?php foreach ($books as $bk): ?>
                    <div class="card" style="overflow: hidden;">
                        <img src="<?= BASE_URL ?>uploads/books/<?= e($bk['book_cover']) ?>" 
                             alt="<?= e($bk['title']) ?>"
                             style="width: 100%; height: 280px; object-fit: cover;"
                             onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                        <div style="padding: 1rem;">
                            <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; min-height: 50px;">
                                <?= e($bk['title']) ?>
                            </h4>
                            <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-user"></i> <?= e($bk['author_name']) ?>
                            </p>
                            <p style="font-size: 0.8rem; margin-bottom: 0.75rem;">
                                <span class="badge badge-primary"><?= e($bk['category_name']) ?></span><br>
                                <span class="badge badge-<?= $bk['available_copies'] > 0 ? 'success' : 'danger' ?>" style="margin-top: 0.5rem;">
                                    <?= $bk['available_copies'] > 0 ? $bk['available_copies'] . ' Available' : 'Not Available' ?>
                                </span>
                            </p>
                            <button onclick="viewBook(<?= htmlspecialchars(json_encode($bk)) ?>)" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav style="margin-top: 2rem;">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="<?= $i === $page ? 'active' : '' ?>">
                                <?php if ($i === $page): ?>
                                    <span><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Book Details Modal -->
<div id="bookModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Book Details</h3>
            <button class="modal-close" onclick="closeModal('bookModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem;">
                <div>
                    <img id="modalCover" src="" alt="Book Cover" style="width: 100%; border-radius: 8px;">
                </div>
                <div>
                    <h3 id="modalBookTitle" style="margin-bottom: 1rem;"></h3>
                    <p><strong>Author:</strong> <span id="modalAuthor"></span></p>
                    <p><strong>Category:</strong> <span id="modalCategory"></span></p>
                    <p><strong>Publisher:</strong> <span id="modalPublisher"></span></p>
                    <p><strong>ISBN:</strong> <span id="modalISBN"></span></p>
                    <p><strong>Shelf:</strong> <span id="modalShelf"></span></p>
                    <p><strong>Availability:</strong> <span id="modalAvailability"></span></p>
                    <div style="margin-top: 1rem;">
                        <strong>Description:</strong>
                        <p id="modalDescription" style="margin-top: 0.5rem; line-height: 1.6;"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('bookModal')">Close</button>
        </div>
    </div>
</div>

<?php
$customJS = '
<script>
function viewBook(book) {
    document.getElementById("modalTitle").textContent = book.title;
    document.getElementById("modalBookTitle").textContent = book.title;
    document.getElementById("modalAuthor").textContent = book.author_name;
    document.getElementById("modalCategory").textContent = book.category_name;
    document.getElementById("modalPublisher").textContent = book.publisher_name;
    document.getElementById("modalISBN").textContent = book.isbn;
    document.getElementById("modalShelf").textContent = book.shelf_number || "N/A";
    document.getElementById("modalDescription").textContent = book.description || "No description available";
    document.getElementById("modalCover").src = "' . BASE_URL . 'uploads/books/" + book.book_cover;
    
    const availability = book.available_copies > 0 
        ? \'<span class="badge badge-success">\' + book.available_copies + \' copies available</span>\'
        : \'<span class="badge badge-danger">Not available</span>\';
    document.getElementById("modalAvailability").innerHTML = availability;
    
    openModal("bookModal");
}
</script>
';

include '../../includes/footer.php';
?>
