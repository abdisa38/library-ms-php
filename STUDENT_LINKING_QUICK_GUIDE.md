# 🔗 Student Account Linking - Quick Guide

## What You'll See

### In the Students Table:
A new column called **"User Account"** shows the linking status:

```
┌──────────────┬─────────────────┬────────────────┬──────────────────┐
│ Student ID   │ Name            │ Email          │ User Account     │
├──────────────┼─────────────────┼────────────────┼──────────────────┤
│ STD001       │ John Smith      │ john@mail.com  │ ✅ john (Linked) │
│ STD002       │ Jane Doe        │ jane@mail.com  │ [Link Account]   │ ← Click this!
└──────────────┴─────────────────┴────────────────┴──────────────────┘
```

---

## 🚀 How to Link (3 Clicks!)

### 1️⃣ Click the Orange Button
   - Find student without linked account
   - Click "**Link Account**" button

### 2️⃣ Review & Confirm
   - Modal shows student details
   - Shows default credentials
   - Click "**Create Account & Link**"

### 3️⃣ Done! ✅
   - Success message shows username
   - Student can now login!

---

## 📋 What Gets Created

When you click "Link Account", the system creates:

| Field      | Value                                    |
|------------|------------------------------------------|
| Username   | Auto-generated from email (john@... → john) |
| Password   | `student123`                             |
| Role       | Student                                  |
| Email      | Same as student profile                  |
| Full Name  | Same as student profile                  |

---

## 🎯 After Linking

### Student Can:
✅ Login at: `test_login.php`  
✅ Browse books  
✅ View borrowing history  
✅ Update profile  
✅ See due dates & fines  

### Student Dashboard Shows:
- Currently borrowed books
- Overdue warnings
- Available books to borrow
- Personal statistics

---

## 💡 Pro Tips

### For Librarians:
- Link accounts right after adding new students
- Keep student email addresses accurate
- Share login credentials securely

### Student Login Methods:
1. **Easy Login**: Click "Student" button at `test_login.php`
2. **Manual Login**: Enter username/password at `index.php`
3. **Reset Password**: Students can change default password in profile

---

## 🔍 Quick Check

### Is Account Already Linked?
- **Green Badge** = Already linked ✅
- **Orange Button** = Not linked yet ⚠️

### Can't Find Student?
- Use the search bar at top
- Search by name, ID, or email

---

## ⚡ Testing the Feature

1. Login as librarian: `librarian/librarian`
2. Go to Students page
3. Look for "Link Account" button
4. Click it and create account
5. Logout and login as the new student
6. Check student dashboard works!

---

## 📞 Common Questions

**Q: What if student email changes?**  
A: Update student email first, then the system will match by new email

**Q: Can I unlink accounts?**  
A: Currently no - but you can delete the user account from Admin → Users

**Q: Do all students need accounts?**  
A: No! Only students who want to login and use online features

**Q: What's the default password?**  
A: `student123` - Students should change it after first login

---

**Status**: ✅ Ready to use!  
**Location**: Librarian Dashboard → Students
