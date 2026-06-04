<?php
/**
 * Books Management Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Manage Books';
$book = new Book();
$category = new Category();
$author = new Author();
$publisher = new Publisher();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $bookCover = 'default-book.jpg';
                
                // Handle file upload
                if (isset($_FILES['book_cover']) && $_FILES['book_cover']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['book_cover'], BOOK_COVER_PATH);
                    if ($upload['success']) {
                        $bookCover = $upload['filename'];
                    }
                }
                
                $data = [
                    'isbn' => sanitize($_POST['isbn']),
                    'title' => sanitize($_POST['title']),
                    'author_id' => (int)$_POST['author_id'],
                    'category_id' => (int)$_POST['category_id'],
                    'publisher_id' => (int)$_POST['publisher_id'],
                    'quantity' => (int)$_POST['quantity'],
                    'book_cover' => $bookCover,
                    'shelf_number' => sanitize($_POST['shelf_number']),
                    'description' => sanitize($_POST['description'])
                ];
                
                $result = $book->create($data);
                
                if ($result['success']) {
                    setFlash('success', 'Book added successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
                
            case 'edit':
                $data = [
                    'isbn' => sanitize($_POST['isbn']),
                    'title' => sanitize($_POST['title']),
                    'author_id' => (int)$_POST['author_id'],
                    'category_id' => (int)$_POST['category_id'],
                    'publisher_id' => (int)$_POST['publisher_id'],
                    'quantity' => (int)$_POST['quantity'],
                    'shelf_number' => sanitize($_POST['shelf_number']),
                    'description' => sanitize($_POST['description'])
                ];
                
                // Handle file upload
                if (isset($_FILES['book_cover']) && $_FILES['book_cover']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['book_cover'], BOOK_COVER_PATH);
                    if ($upload['success']) {
                        $data['book_cover'] = $upload['filename'];
                    }
                }
                
                $result = $book->update($_POST['book_id'], $data);
                
                if ($result['success']) {
                    setFlash('success', 'Book updated successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
                
            case 'delete':
                $result = $book->delete($_POST['book_id']);
                
                if ($result['success']) {
                    setFlash('success', 'Book deleted successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
        }
        
        redirect('books.php');
    }
}

// Get all books with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;

$filters = [];
if ($search) $filters['search'] = $search;
if ($categoryFilter) $filters['category_id'] = $categoryFilter;

$totalBooks = $book->count($filters);
$pagination = paginate($page, $totalBooks);
$books = $book->getAll($pagination['limit'], $pagination['offset'], $filters);

// Get all categories, authors, and publishers for dropdowns
$categories = $category->getAll();
$authors = $author->getAll();
$publishers = $publisher->getAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-book"></i> Manage Books
    </h1>
    <ul class="breadcrumb">
        <li>Home</li>
        <li>Books</li>
    </ul>
</div>

<!-- Filter and Search -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="" class="form-row" style="align-items: flex-end;">
            <div class="form-group" style="flex: 2;">
                <label class="form-label">Search</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by title, ISBN, or author..."
                       value="<?= e($search) ?>">
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
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="books.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Books Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Books (<?= $totalBooks ?>)</h3>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addBookModal')">
            <i class="fas fa-plus"></i> Add New Book
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table id="booksTable">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No books found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $bk): ?>
                            <tr>
                                <td>
                                    <img src="<?= BASE_URL ?>uploads/books/<?= e($bk['book_cover']) ?>" 
                                         class="book-cover-sm" 
                                         alt="<?= e($bk['title']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                                </td>
                                <td><?= e($bk['isbn']) ?></td>
                                <td><strong><?= e($bk['title']) ?></strong></td>
                                <td><?= e($bk['author_name']) ?></td>
                                <td><span class="badge badge-primary"><?= e($bk['category_name']) ?></span></td>
                                <td><?= $bk['quantity'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $bk['available_copies'] > 0 ? 'success' : 'danger' ?>">
                                        <?= $bk['available_copies'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary"
                                                onclick='editBook(<?= json_encode($bk) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirmDelete('Are you sure you want to delete this book?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="book_id" value="<?= $bk['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav style="margin-top: 1rem;">
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
    </div>
</div>

<!-- Add Book Modal -->
<div id="addBookModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Book</h3>
            <button type="button" class="modal-close" onclick="closeModal('addBookModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ISBN *</label>
                        <input type="text" name="isbn" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Author *</label>
                        <select name="author_id" class="form-control" required>
                            <option value="">Select Author</option>
                            <?php foreach ($authors as $auth): ?>
                                <option value="<?= $auth['id'] ?>"><?= e($auth['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Publisher *</label>
                        <select name="publisher_id" class="form-control" required>
                            <option value="">Select Publisher</option>
                            <?php foreach ($publishers as $pub): ?>
                                <option value="<?= $pub['id'] ?>"><?= e($pub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shelf Number</label>
                        <input type="text" name="shelf_number" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Book Cover</label>
                        <input type="file" name="book_cover" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addBookModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Book</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Book Modal -->
<div id="editBookModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Book</h3>
            <button type="button" class="modal-close" onclick="closeModal('editBookModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="book_id" id="edit_book_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ISBN *</label>
                        <input type="text" name="isbn" id="edit_isbn" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Author *</label>
                        <select name="author_id" id="edit_author_id" class="form-control" required>
                            <option value="">Select Author</option>
                            <?php foreach ($authors as $auth): ?>
                                <option value="<?= $auth['id'] ?>"><?= e($auth['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" id="edit_category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Publisher *</label>
                        <select name="publisher_id" id="edit_publisher_id" class="form-control" required>
                            <option value="">Select Publisher</option>
                            <?php foreach ($publishers as $pub): ?>
                                <option value="<?= $pub['id'] ?>"><?= e($pub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shelf Number</label>
                        <input type="text" name="shelf_number" id="edit_shelf_number" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Book Cover</label>
                        <input type="file" name="book_cover" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editBookModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Book</button>
            </div>
        </form>
    </div>
</div>

<?php
$customJS = '
<script>
function editBook(book) {
    document.getElementById("edit_book_id").value = book.id;
    document.getElementById("edit_isbn").value = book.isbn;
    document.getElementById("edit_title").value = book.title;
    document.getElementById("edit_author_id").value = book.author_id;
    document.getElementById("edit_category_id").value = book.category_id;
    document.getElementById("edit_publisher_id").value = book.publisher_id;
    document.getElementById("edit_quantity").value = book.quantity;
    document.getElementById("edit_shelf_number").value = book.shelf_number || "";
    document.getElementById("edit_description").value = book.description || "";
    
    openModal("editBookModal");
}
</script>
';

include '../../includes/footer.php';
?>
