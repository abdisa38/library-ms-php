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
$db = getDB();

// Get student profile
$stmt = $db->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$_SESSION['email']]);
$studentInfo = $stmt->fetch();

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    if (!$studentInfo) {
        setFlash('danger', 'Your account is not linked to a student profile. Please contact the librarian.');
    } else {
        $bookId = (int)$_POST['book_id'];
        
        // Check if book is available
        $stmt = $db->prepare("SELECT available_copies FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        $bookData = $stmt->fetch();
        
        if ($bookData['available_copies'] <= 0) {
            setFlash('danger', 'This book is not available for borrowing.');
        } else {
            // Check if student already borrowed this book
            $stmt = $db->prepare("SELECT id FROM borrowings WHERE student_id = ? AND book_id = ? AND status = 'borrowed'");
            $stmt->execute([$studentInfo['id'], $bookId]);
            
            if ($stmt->fetch()) {
                setFlash('warning', 'You have already borrowed this book.');
            } else {
                // Check borrowing limit
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE student_id = ? AND status = 'borrowed'");
                $stmt->execute([$studentInfo['id']]);
                $currentBorrowed = $stmt->fetch()['total'];
                $maxBooks = 3; // Default limit
                
                if ($currentBorrowed >= $maxBooks) {
                    setFlash('warning', "You have reached the maximum borrowing limit ({$maxBooks} books). Please return a book first.");
                } else {
                    // Create borrowing record
                    $borrowDate = date('Y-m-d');
                    $dueDate = date('Y-m-d', strtotime('+14 days')); // 14 days borrowing period
                    
                    $stmt = $db->prepare("
                        INSERT INTO borrowings (student_id, book_id, borrow_date, due_date, status, created_by)
                        VALUES (?, ?, ?, ?, 'borrowed', ?)
                    ");
                    $stmt->execute([$studentInfo['id'], $bookId, $borrowDate, $dueDate, $_SESSION['user_id']]);
                    
                    // Update book available copies
                    $stmt = $db->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
                    $stmt->execute([$bookId]);
                    
                    setFlash('success', 'Book borrowed successfully! Due date: ' . date('M d, Y', strtotime($dueDate)));
                }
            }
        }
    }
    redirect('books.php');
}

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
                            <?php if ($studentInfo && $bk['available_copies'] > 0): ?>
                                <form method="POST" style="margin-top: 0.5rem;" onsubmit="return confirm('Do you want to borrow this book?')">
                                    <input type="hidden" name="action" value="borrow">
                                    <input type="hidden" name="book_id" value="<?= $bk['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-book-reader"></i> Borrow Now
                                    </button>
                                </form>
                            <?php elseif (!$studentInfo): ?>
                                <button class="btn btn-warning btn-sm w-100" disabled style="margin-top: 0.5rem;">
                                    <i class="fas fa-exclamation-triangle"></i> Account Not Linked
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm w-100" disabled style="margin-top: 0.5rem;">
                                    <i class="fas fa-times"></i> Not Available
                                </button>
                            <?php endif; ?>
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
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Book Details</h3>
            <button class="modal-close" onclick="closeModal('bookModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem;">
                <div>
                    <img id="modalCover" src="" alt="Book Cover" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                </div>
                <div>
                    <h3 id="modalBookTitle" style="margin-bottom: 1rem;"></h3>
                    <p><strong><i class="fas fa-user"></i> Author:</strong> <span id="modalAuthor"></span></p>
                    <p><strong><i class="fas fa-layer-group"></i> Category:</strong> <span id="modalCategory"></span></p>
                    <p><strong><i class="fas fa-building"></i> Publisher:</strong> <span id="modalPublisher"></span></p>
                    <p><strong><i class="fas fa-barcode"></i> ISBN:</strong> <span id="modalISBN"></span></p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Shelf:</strong> <span id="modalShelf"></span></p>
                    <p><strong><i class="fas fa-check-circle"></i> Availability:</strong> <span id="modalAvailability"></span></p>
                    <div style="margin-top: 1rem;">
                        <strong><i class="fas fa-align-left"></i> Description:</strong>
                        <p id="modalDescription" style="margin-top: 0.5rem; line-height: 1.6; color: #666;"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('bookModal')">Close</button>
            <form method="POST" id="borrowForm" style="display: inline;" onsubmit="return confirm('Do you want to borrow this book? Due date will be 14 days from today.')">
                <input type="hidden" name="action" value="borrow">
                <input type="hidden" name="book_id" id="modalBookId">
                <button type="submit" id="borrowBtn" class="btn btn-success">
                    <i class="fas fa-book-reader"></i> Borrow This Book
                </button>
            </form>
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
    document.getElementById("modalBookId").value = book.id;
    
    const availability = book.available_copies > 0 
        ? \'<span class="badge badge-success">\' + book.available_copies + \' copies available</span>\'
        : \'<span class="badge badge-danger">Not available</span>\';
    document.getElementById("modalAvailability").innerHTML = availability;
    
    // Show/hide borrow button based on availability and student profile
    const borrowBtn = document.getElementById("borrowBtn");
    const borrowForm = document.getElementById("borrowForm");
    
    ' . ($studentInfo ? 'const hasStudentProfile = true;' : 'const hasStudentProfile = false;') . '
    
    if (!hasStudentProfile) {
        borrowForm.style.display = "none";
    } else if (book.available_copies <= 0) {
        borrowBtn.disabled = true;
        borrowBtn.innerHTML = \'<i class="fas fa-times"></i> Not Available\';
        borrowBtn.className = "btn btn-secondary";
    } else {
        borrowForm.style.display = "inline";
        borrowBtn.disabled = false;
        borrowBtn.innerHTML = \'<i class="fas fa-book-reader"></i> Borrow This Book\';
        borrowBtn.className = "btn btn-success";
    }
    
    openModal("bookModal");
}
</script>
';

include '../../includes/footer.php';
?>
