# 🗺️ Budget App Sitemap (v1)
*A structured, purpose‑driven map of every core page in the budgeting app.*

---

## 1. Dashboard
**Route:** `/dashboard`

### Purpose
The Dashboard gives the user a high‑level overview of their financial month. It acts as the “home” of the app — a quick snapshot of spending, income, and upcoming activity. It should feel fast, visual, and informative.

### Key Features
- Monthly income vs expense summary  
- Category breakdown chart  
- Upcoming recurring transactions  
- Quick actions for common tasks  

### Components
- `SummaryCard` (income, expenses, net)  
- `CategoryBreakdownChart`  
- `UpcomingRecurringList`  
- `QuickActions` (Add Transaction, Add Category, Add Recurring Rule)  

---

## 2. Transactions
**Route:** `/transactions`

### Purpose
This page is the user’s main workspace for viewing, editing, and managing their transactions. It should support filtering by month and provide fast access to editing tools.

### Key Features
- Month selector  
- Full transaction list  
- Edit transaction modal  
- Delete confirmation  
- Add transaction button  

### Sub‑Routes
- `/transactions/create` — Create transaction form  
- `/transactions/:id/edit` — Edit transaction form or modal  

### Components
- `TransactionList`  
- `TransactionRow`  
- `EditTransaction` (modal or inline)  
- `CreateTransactionForm`  
- `MonthSelector`  
- `Button`  

---

## 3. Categories
**Route:** `/categories`

### Purpose
This page allows users to manage the structure of their budget — the categories that transactions belong to. It should feel organized and visually clear, especially with your icon system.

### Key Features
- Grouped category list (Income / Expense)  
- Add category  
- Edit category  
- Delete category  

### Sub‑Routes
- `/categories/create`  
- `/categories/:id/edit`  

### Components
- `CategoryList`  
- `CategoryRow`  
- `CategoryForm`  
- `IconPicker`  
- `Button`  

---

## 4. Recurring Rules
**Route:** `/recurring-rules`

### Purpose
This page manages recurring financial events like subscriptions, bills, and salary. It should clearly communicate frequency, next occurrence, and linked transactions.

### Key Features
- List of recurring rules  
- Show next occurrence  
- Show frequency + interval  
- Optional: show generated transactions  
- Add / Edit / Delete rule  

### Sub‑Routes
- `/recurring-rules/create`  
- `/recurring-rules/:id/edit`  

### Components
- `RecurringRuleList`  
- `RecurringRuleRow`  
- `RecurringRuleForm`  
- `FrequencySelector`  
- `IntervalSelector`  
- `Button`  

---

## 5. Settings
**Route:** `/settings`

### Purpose
A simple page for user preferences and account‑level configuration. This is where your UI kit can shine with clean, intentional design.

### Key Features
- Profile settings  
- Currency selection  
- Theme selection  
- Logout  

### Components
- `ProfileForm`  
- `CurrencySelector`  
- `ThemeSelector`  
- `Button`  