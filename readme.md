
## 🚀 Installation
# Member Directory – WordPress Plugin

A custom WordPress plugin that provides a **Member Directory** with team associations, single member pages, and contact submissions.  
This plugin was built as an assignment project to demonstrate **WordPress plugin development best practices**.

## 🚀 Installation

1. Clone the repository:
   ```bash
   git clone https://mahbub500/yourusername/member-directory.git
   cd cloud-server-managment
   ```

2. Install dependencies:
   ```bash
   composer update
   ```

3. Activate the plugin from the WordPress dashboard.

---

---

## Features

### Member Management
- Custom post type for **Members** with fields:
  - First Name
  - Last Name
  - Email (unique per member)
  - Profile Image
  - Cover Image
  - Address
  - Favorite Color (color picker)
  - Status (Active / Draft)
- Ensures duplicate emails are not allowed.

### Team Management
- Custom post data save for **Teams** with fields:
  - Name
  - Short Description
- Members can be associated with multiple teams.

### Relationships
- A member can belong to one or more teams.
- Teams display their related members.

---

### Chat list
-  Admin can see chat list of between user.

---

## Frontend

### Single Member Page
- Short code added wtih `[team_dashboard]`.
- URL structure: `/first-name_last-name`
- Only **Active** members are publicly visible.
- Displays all member fields in a clean layout.
- Includes a **Cahat system** team member can tack each other:
  - Full Name
  - Email
  - Message  
- On form submission:
  - Stores the submission in the database.
  - Submissions are viewable from the admin dashboard in **chat list** screen in the admin.


---

## Admin Features
- Manage Members and Teams from the WordPress dashboard.
- View submissions directly from each Member’s edit screen.
---