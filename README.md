# Book Management System - Plugin 

A WordPress plugin that provides a simple and efficient way to manage books in your WordPress admin panel. This plugin allows you to create, display, edit, and delete book entries with features like cover image upload, sorting, and pagination.

## Features

- User-friendly book management interface in WordPress admin
- Add books with title, author, publication year, description, and cover image
- Display books in a sortable and paginated table
- Bulk actions for managing multiple books
- Cover image upload support (JPG and PNG)
- Secure data handling and WordPress integration
- Responsive admin interface

## Installation

1. Download the plugin files
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Access the plugin via the new 'Book Management' menu item in your WordPress admin panel

## Usage

### Adding a New Book

1. Navigate to Book Management > Add Book in your WordPress admin panel
2. Fill in the following details:
   - Title (required)
   - Author (required)
   - Publication Year (required)
   - Description (optional)
   - Cover Image (optional, JPG/PNG only)
3. Click "Add Book" to save the entry

### Managing Books

1. Go to Book Management > List Books to view all books
2. Features available:
   - Sort books by title, author, publication year, or date added
   - Edit existing books
   - Delete individual books
   - Perform bulk deletions
   - View book cover thumbnails
   - 10 books per page with pagination

## Technical Details

### Database Structure

The plugin creates a custom table in your WordPress database with the following structure:

```sql
CREATE TABLE {prefix}books (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    author varchar(255) NOT NULL,
    publication_year int(4) NOT NULL,
    description text,
    image_url varchar(255),
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id)
)
```

### Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher


## Author

Aqsa Mumtaz

## Version

1.0

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, bug reports, or feature requests, please submit an issue on the plugin's repository.
