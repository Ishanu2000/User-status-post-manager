# User Status Post Manager

Redirect WordPress users to their assigned post after login. Allow admins to assign posts per user and restrict access so each user can only view their own content. Hide member-only posts from public views and enhance the member post experience with custom behavior.

## 🔑 Features

- 🔄 Redirect subscriber to their assigned post after login
- 👤 Assign specific post to each user (via meta box in post editor)
- 🔐 Restrict post access to assigned user only
- 📰 Hide `member` category posts from news or archive pages
- 🧼 Remove sidebar on posts in `member` category
- 🔓 Show logout button on member posts
- 🚫 Block subscribers from editing any posts via admin panel

## 📦 Installation

1. Download or clone this repository.
2. Upload the folder to your WordPress site under `/wp-content/plugins/`.
3. Activate the plugin from your WordPress admin panel.
4. Edit any post and assign it to a subscriber using the “Assign Post to User” meta box.

## 🚀 Usage

- **Login Redirect:** When a subscriber logs in, they are redirected to the post assigned to them.
- **Access Control:** Only the assigned user (and admins) can view a restricted post.
- **Content Hiding:** Member category posts are hidden from homepage/archive queries.
- **Member Experience:** A logout button appears, and sidebars are removed for better focus.

## 🖼️ Screenshots

1. Assign post to user meta box in post editor
2. Logout button visible on member-only post

## ❓ FAQ

### Can I assign multiple posts to a user?

Not currently — each user can only be assigned one post. Assigning a new post will overwrite the previous assignment.

### Can users edit their post?

No — only administrators can edit posts. Subscribers can only view their assigned post.

### What happens if a user tries to access a post they’re not assigned to?

They will see:  
`You are not authorized to view this post.`

## 👨‍💻 Author

Developed by [Ishan Udayanga](https://ishanudayanga.com)

## 📄 License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
