<?php
/*
Plugin Name: User Status Post Manager
Description: Redirects users to their assigned post after login and allows admin to assign posts per user. Hides member posts from news page, removes sidebar on member posts, and adds logout button. Admin can edit; users can only read.
Version: 1.2
Author: Ishan Udayanga
Author URI: https://ishanudayanga.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// 1. Redirect User After Login to Their Assigned Post
function custom_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && in_array('subscriber', $user->roles)) {
        $assigned_post_id = get_user_meta($user->ID, 'assigned_post_id', true);
        if ($assigned_post_id) {
            return get_permalink($assigned_post_id);
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

// 2. Restrict Post Access to Assigned User Only
function restrict_post_access_to_assigned_user() {
    if (is_singular('post') && is_user_logged_in()) {
        global $post;
        if (!isset($post)) return;

        $current_user_id = get_current_user_id();
        $assigned_user_id = get_post_meta($post->ID, 'assigned_user_id', true);

        if (current_user_can('administrator')) {
            return;
        }

        if ($assigned_user_id && $assigned_user_id != $current_user_id) {
            wp_die('You are not authorized to view this post.');
        }
    }
}
add_action('template_redirect', 'restrict_post_access_to_assigned_user');

// 3. Admin Meta Box to Assign Post to a User
function add_user_assignment_meta_box() {
    add_meta_box('user_assignment', 'Assign Post to User', 'user_assignment_meta_box_callback', 'post', 'side');
}
add_action('add_meta_boxes', 'add_user_assignment_meta_box');

function user_assignment_meta_box_callback($post) {
    wp_nonce_field('assign_user_nonce_action', 'assign_user_nonce');
    $assigned_user_id = get_post_meta($post->ID, 'assigned_user_id', true);
    $users = get_users(['role' => 'subscriber']);

    echo '<label for="assigned_user_id">Select User:</label>';
    echo '<select name="assigned_user_id" id="assigned_user_id" class="widefat">';
    echo '<option value="">-- Select User --</option>';
    foreach ($users as $user) {
        $selected = ($user->ID == $assigned_user_id) ? 'selected' : '';
        echo "<option value='{$user->ID}' {$selected}>{$user->display_name} ({$user->user_login})</option>";
    }
    echo '</select>';
}

function save_user_assignment_meta_box($post_id) {
    if (!isset($_POST['assign_user_nonce']) || !wp_verify_nonce($_POST['assign_user_nonce'], 'assign_user_nonce_action')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['assigned_user_id'])) {
        $user_id = intval($_POST['assigned_user_id']);
        update_post_meta($post_id, 'assigned_user_id', $user_id);

        if ($user_id > 0) {
            update_user_meta($user_id, 'assigned_post_id', $post_id);
        }
    }
}
add_action('save_post', 'save_user_assignment_meta_box');

// 4. Hide 'member' category posts from news page
function hide_member_category_from_home($query) {
    if (!is_admin() && $query->is_main_query() && (is_home() || is_archive())) {
        $member_cat_id = get_cat_ID('member');
        if ($member_cat_id) {
            $query->set('category__not_in', array($member_cat_id));
        }
    }
}
add_action('pre_get_posts', 'hide_member_category_from_home');

// 5. Remove sidebar on member posts
function remove_sidebar_on_member_category($template) {
    if (is_single()) {
        $categories = get_the_category();
        foreach ($categories as $category) {
            if ($category->slug === 'member') {
                remove_all_actions('get_sidebar');
                break;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'remove_sidebar_on_member_category');

// 6. Add logout button to top-right on member category posts
function add_logout_button_on_member_posts() {
    if (!is_user_logged_in()) return;

    if (is_single()) {
        $categories = get_the_category();
        foreach ($categories as $category) {
            if ($category->slug === 'member') {
                echo '<div style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
                    <a href="' . esc_url(wp_logout_url(home_url())) . '" style="padding: 10px 15px; background: #c00; color: #fff; text-decoration: none; border-radius: 5px;">Logout</a>
                </div>';
                break;
            }
        }
    }
}
add_action('wp_footer', 'add_logout_button_on_member_posts');

// 7. Prevent users from editing posts via admin panel
function restrict_editing_access_for_subscribers() {
    if (is_admin()) {
        global $pagenow;
        if (in_array($pagenow, ['post.php', 'post-new.php', 'edit.php'])) {
            if (!current_user_can('edit_posts')) {
                wp_die('You are not allowed to edit posts.');
            }
        }
    }
}
add_action('admin_init', 'restrict_editing_access_for_subscribers');
