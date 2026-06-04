# 📚 How to Borrow Books - Student Guide

## ✅ Fixed Issues
1. ✅ Warning message gone after linking
2. ✅ Students can now borrow books directly
3. ✅ Complete borrow workflow implemented

---

## 🎯 Quick Start

### For Students:
1. **Login** at `test_login.php`
2. Click **"Browse Books"** in sidebar
3. Find a book you want
4. Click **"Borrow Now"** or **"View Details" → "Borrow This Book"**
5. Confirm → Done! ✅

---

## 📖 Step-by-Step Borrowing

### Method 1: Quick Borrow (From Card)
```
1. Go to Browse Books page
2. Find available book (green "X Available" badge)
3. Click "Borrow Now" button directly
4. Confirm dialog appears
5. Click OK
6. Success! Book borrowed
```

### Method 2: Detailed View (From Modal)
```
1. Go to Browse Books page
2. Click "View Details" on any book
3. Read book information
4. Click "Borrow This Book" button
5. Confirm dialog appears
6. Click OK
7. Success! Book borrowed
```

---

## 🔍 Visual Indicators

### Book Availability Status:

| Badge Color | Text | Meaning | Action |
|------------|------|---------|--------|
| 🟢 Green | "3 Available" | Can borrow | Click "Borrow Now" |
| 🔴 Red | "Not Available" | All borrowed | Wait for return |
| 🟠 Orange | "Account Not Linked" | Contact librarian | Cannot borrow yet |

### Borrow Button States:

| Button | State | Meaning |
|--------|-------|---------|
| 🟢 **"Borrow Now"** | Enabled | Click to borrow |
| ⚫ **"Not Available"** | Disabled | Book unavailable |
| 🟠 **"Account Not Linked"** | Disabled | Contact librarian |

---

## ⚙️ Borrowing Rules

### Limits:
- **📚 Max Books**: 3 books at a time
- **⏰ Borrowing Period**: 14 days
- **📅 Due Date**: Auto-calculated (today + 14 days)

### Restrictions:
- ❌ Cannot borrow same book twice
- ❌ Cannot exceed 3 books limit
- ❌ Cannot borrow unavailable books
- ❌ Must have linked student account

---

## 📊 After Borrowing

### Check Your Borrowings:
```
Student Dashboard → "My Borrowings"
```

You'll see:
- ✅ Book title & cover
- ✅ Borrow date
- ✅ Due date
- ✅ Days remaining
- ⚠️ Overdue warning (if late)

### On Dashboard:
- Shows currently borrowed books
- Displays due dates
- Warns about overdue books
- Shows available books to browse

---

## 🚨 Common Issues & Solutions

### Issue 1: "Account Not Linked" Warning
**Solution**: Contact your librarian to link your account
- They go to: Librarian Dashboard → Students
- Click "Link Account" next to your name
- You'll get login credentials

### Issue 2: "Already Borrowed This Book"
**Solution**: You cannot borrow the same book twice
- Return it first, then borrow again
- Or choose a different book

### Issue 3: "Maximum Borrowing Limit Reached"
**Solution**: You already have 3 books borrowed
- Return at least 1 book first
- Then you can borrow more

### Issue 4: "Not Available" Badge
**Solution**: All copies are currently borrowed
- Check back later
- Or ask librarian when it will be available

---

## 💡 Pro Tips

### Search & Filter:
- 🔍 **Search** by title, ISBN, or author
- 📂 **Filter** by category
- 🔄 **Reset** to see all books

### Best Practices:
- ✅ Note your due dates
- ✅ Return books on time (avoid fines)
- ✅ Browse before your current books are due
- ✅ Check "My Borrowings" regularly

### Quick Access:
- Sidebar: **"Browse Books"** → All available books
- Sidebar: **"My Borrowings"** → Your borrowed books
- Dashboard: **Quick view** of current borrowings

---

## 📅 Important Dates

### When You Borrow:
- **Borrow Date**: Today's date
- **Due Date**: 14 days from today
- **Return By**: Due date to avoid fines

### Example:
```
Borrowed: June 5, 2026
Due Date: June 19, 2026
Return by: June 19, 2026 (or earlier)
```

---

## 🎨 What You'll See

### Browse Books Page:
```
┌─────────────────────────────────────────┐
│ 📚 Browse Books                         │
├─────────────────────────────────────────┤
│ ℹ️ How to Borrow: Click "View Details" │
│ on any book, then click "Borrow This   │
│ Book" button. Due: 14 days             │
├─────────────────────────────────────────┤
│ Search: [_____] Category: [All ▼] 🔍   │
├─────────────────────────────────────────┤
│                                         │
│ ┌───────┐  ┌───────┐  ┌───────┐      │
│ │[Cover]│  │[Cover]│  │[Cover]│      │
│ │ Title │  │ Title │  │ Title │      │
│ │Author │  │Author │  │Author │      │
│ │🟢 3 Av│  │🔴 Not │  │🟢 1 Av│      │
│ │[View ]│  │[View ]│  │[View ]│      │
│ │[Borrow]│  │[---] │  │[Borrow]│      │
│ └───────┘  └───────┘  └───────┘      │
└─────────────────────────────────────────┘
```

### Book Details Modal:
```
┌─────────────────────────────────────────┐
│ Book Details                        [×] │
├─────────────────────────────────────────┤
│ ┌────────┐  Title: The Great Book     │
│ │ Cover  │  Author: John Doe          │
│ │ Image  │  Category: Fiction         │
│ │        │  ISBN: 123-456-789         │
│ │        │  Availability: 🟢 3 copies │
│ └────────┘                             │
│                                         │
│ Description: An amazing book about...  │
├─────────────────────────────────────────┤
│ [Close] [📚 Borrow This Book]          │
└─────────────────────────────────────────┘
```

---

## 🧪 Test It Now!

### Quick Test:
1. Login: `test_login.php` → Click "Student"
2. Click "Browse Books" in sidebar
3. Pick any book with green badge
4. Click "Borrow Now"
5. Confirm
6. See success message! ✅
7. Check "My Borrowings" to see your book

---

## 📞 Need Help?

### For Students:
- Contact your librarian for account linking
- Check "My Borrowings" for due dates
- Return books on time

### For Librarians:
- Link student accounts: Students page → "Link Account"
- Manage borrowings: Borrowings page
- Process returns: Returns page

---

**Status**: ✅ Fully Working!  
**Last Updated**: June 5, 2026  
**Test URL**: `http://localhost/Library%20ms%20php/library-ms-php/test_login.php`
