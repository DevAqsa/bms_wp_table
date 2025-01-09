<?php
/*
   Plugin Name: Book Management System
 * Description: This is custom plugin to manage all Books
 * Version:1.0
 * Author: Aqsa Mumtaz
*/


if (!defined('ABSPATH')) {
    die;
}

// Include WP_List_Table if not exists
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Create custom table class
class Books_List_Table extends WP_List_Table {
    
    public function __construct() {
        parent::__construct([
            'singular' => 'book',
            'plural'   => 'books',
            'ajax'     => false
        ]);
    }
    
    public function get_columns() {
        return [
            'cb'              => '<input type="checkbox" />',
            'image'           => 'Cover',
            'title'           => 'Title',
            'author'          => 'Author',
            'publication_year'=> 'Publication Year',
            'description'     => 'Description',
            'created_at'      => 'Added On'
        ];
    }
    
    protected function get_sortable_columns() {
        return [
            'title'           => ['title', true],
            'author'          => ['author', false],
            'publication_year'=> ['publication_year', false],
            'created_at'      => ['created_at', true]
        ];
    }
    
    protected function column_default($item, $column_name) {
        return esc_html($item->$column_name);
    }
    
    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="book[]" value="%s" />', $item->id);
    }
    
    protected function column_title($item) {
        $actions = [
            'edit'   => sprintf('<a href="?page=add-book&action=edit&book=%s">Edit</a>', $item->id),
            'delete' => sprintf(
                '<a href="?page=%s&action=delete&book=%s" onclick="return confirm(\'Are you sure you want to delete this book?\')">Delete</a>',
                $_REQUEST['page'],
                $item->id
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=add-book&action=edit&book=%s">%s</a></strong>%s',
            $item->id,
            esc_html($item->title),
            $this->row_actions($actions)
        );
    }
    
    protected function column_image($item) {
        if (!empty($item->image_url)) {
            return sprintf(
                '<img src="%s" alt="%s" style="max-width: 50px; height: auto;" />',
                esc_url($item->image_url),
                esc_attr($item->title)
            );
        }
        return '—';
    }
    
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'books';
        
        // Pagination
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        
        // Sorting
        $orderby = isset($_REQUEST['orderby']) ? trim($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : 'DESC';
        
        // Get items
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY %s %s LIMIT %d OFFSET %d",
                $orderby,
                $order,
                $per_page,
                ($current_page - 1) * $per_page
            )
        );
        
        // Set pagination args
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
        
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns()
        ];
    }
    
    public function get_bulk_actions() {
        return [
            'delete' => 'Delete'
        ];
    }
}

// Database creation and update function
function bms_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'books';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        author varchar(255) NOT NULL,
        publication_year int(4) NOT NULL,
        description text,
        image_url varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'bms_create_table');

// Menu items
function bms_admin_menu() {
    add_menu_page(
        'Book Management',
        'Book Management',
        'manage_options',
        'book-management',
        'bms_list_books',
        'dashicons-book-alt'
    );
    
    add_submenu_page(
        'book-management',
        'List Books',
        'List Books',
        'manage_options',
        'book-management',
        'bms_list_books'
    );
    
    add_submenu_page(
        'book-management',
        'Add Book',
        'Add Book',
        'manage_options',
        'add-book',
        'bms_add_book'
    );
}
add_action('admin_menu', 'bms_admin_menu');

// Handle image upload
function bms_handle_image_upload() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['book_image'];
    
    $upload_overrides = array(
        'test_form' => false,
        'mimes' => array(
            'jpg|jpeg' => 'image/jpeg',
            'png' => 'image/png'
        )
    );

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        return $movefile['url'];
    }
    return false;
}

// Add Book Form
function bms_add_book() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'books';
    
    if (isset($_POST['submit_book'])) {
        $image_url = '';
        if (!empty($_FILES['book_image']['name'])) {
            $image_url = bms_handle_image_upload();
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'title' => sanitize_text_field($_POST['title']),
                'author' => sanitize_text_field($_POST['author']),
                'publication_year' => intval($_POST['publication_year']),
                'description' => sanitize_textarea_field($_POST['description']),
                'image_url' => $image_url
            )
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>Book added successfully!</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Add New Book</h1>
        <hr class="wp-header-end">
        
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th><label for="title">Title</label></th>
                    <td>
                        <input type="text" name="title" id="title" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="author">Author</label></th>
                    <td>
                        <input type="text" name="author" id="author" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="publication_year">Publication Year</label></th>
                    <td>
                        <input type="number" name="publication_year" id="publication_year" class="small-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td>
                        <textarea name="description" id="description" class="large-text" rows="5"></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="book_image">Book Cover Image</label></th>
                    <td>
                        <input type="file" name="book_image" id="book_image" accept="image/jpeg,image/png">
                        <p class="description">Accepts JPG and PNG files.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_book" class="button button-primary" value="Add Book">
            </p>
        </form>
    </div>
    <?php
}

// List Books Page
function bms_list_books() {
    $books_table = new Books_List_Table();
    $books_table->prepare_items();
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Books</h1>
        <a href="?page=add-book" class="page-title-action">Add New</a>
        <hr class="wp-header-end">
        
        <form method="post">
            <?php
            $books_table->display();
            ?>
        </form>
    </div>
    <?php
}

// Handle bulk actions and deletions
function bms_handle_actions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'books';
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['book'])) {
        $wpdb->delete(
            $table_name,
            ['id' => $_GET['book']],
            ['%d']
        );
        wp_redirect(admin_url('admin.php?page=book-management&deleted=1'));
        exit;
    }
}
add_action('admin_init', 'bms_handle_actions');