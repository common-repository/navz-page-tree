<?php
/**
	Plugin Name: Navz Page Tree
	Plugin URI: http://www.navz.me/plugins/navz-page-tree
	Description: Structures the pages in WordPress for more robust page edit, sorting and managing. It's more clean you know.
	Version: 1.0.0
	Author: Navneil Naicer
	Author URI: http://www.navz.me
	License: GPLv2 or later
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	
	Copyright 2016 Navneil Naicker

*/

//Preventing from direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class navzPageTree{
	public $version = '1.0.0';
	public $prefix = 'navz-page-tree';
	public $dir;
	public $url;
	public $hierarchy = array();

	public function __construct(){
		add_action( 'admin_init', array($this, 'navz_redirect'));
		add_action( 'admin_menu', array($this, 'navz_change_post_menu_label'));
		$this->dir = plugin_dir_path( __FILE__ );
		$this->url = plugins_url() . '/' . $this->prefix;
	 	add_action('admin_menu', array($this, 'menu') );
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
	}
	
	//All Pages should redirect to navz page when plugin is activated
	public function navz_redirect(){
    global $pagenow;
    if($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'page' && empty($_GET['page']) and empty($_GET['post_status']) and empty($_GET['s']) ){
        wp_redirect( admin_url('/edit.php?post_type=page&page=navz-page-tree', 'http'), 301);
        exit;
    }
	}
	
	//Changing the All Pages link to navz Page when plugin is activated
	public function navz_change_post_menu_label() {
		global $menu;
		global $submenu;
		$menu[20][2] = 'edit.php?post_type=page&page=navz-page-tree';
		$submenu['edit.php?post_type=page&page=navz-page-tree'] = $submenu['edit.php?post_type=page'];
		$submenu['edit.php?post_type=page'][5][2] = 'edit.php?post_type=page&page=navz-page-tree';
	}
	
	//Our Custom styles and js scripts
	public function scripts( $hook ){
		wp_register_style( 'navz-page-tree-style', $this->url . '/css/navz-page-tree-style.css', false, '1.0.0' );
		wp_enqueue_style( 'navz-page-tree-style' );
		wp_enqueue_script( 'jquery-ui-sortable', 'jquery-ui-sortable', 'jQuery', '1.0', true);
		wp_enqueue_script( 'navz-page-tree-js', $this->url . '/js/navz-page-tree-style.js', array(), '1.0', true );
	}
	
	//Get the pages from the database
	public function pages(){
		$navzPages = get_pages(array(
			'sort_column' => 'menu_order',
			'sort_order' => 'asc',
			'post_status' => 'publish,private,draft,pending'
		));
		return $navzPages;
	}
	
	//Loop over the database results and save it as associative array
	public function get(){
		$hierarchy = array();
		$pages = $this->pages();
		if( !empty($pages) ){
			foreach( $pages as $page ){
				$post_parent = $page->post_parent;
				$hierarchy[$post_parent][] = $page;
			}
		}
		$this->hierarchy = $hierarchy;
	}
	
	//Loop over all the pages and layout them as a tree
	public function hierarchy( $post_id, $class = 'navz-parent'){
		$pages = $this->hierarchy;
		if( !empty($pages[$post_id]) ){
			$nonce = wp_create_nonce( 'navz-nonce' );
			echo '<ul' . ' class=' . $class . ' id="item-' . $post_id. '">';
			foreach( $pages[$post_id] as $page ){
				$post_id = $page->ID;
				$post_title = $page->post_title;
				$post_permalink = get_permalink( $post_id );
				$post_edit = admin_url() . '/post.php?post=' . $post_id . '&action=edit';
				if( $pages[$post_id] ){
					echo '<li id="item_' . $post_id . '">';
					echo '<a href="#" class="dashicons-plus-link" data-child="'.$post_id.'"><span class="dashicons dashicons-plus" title="Show child pages"></span></a>';
					echo '<a href="' . $post_edit . '" class="dashicons-menu-edit-link"><span class="dashicons dashicons-edit" title="Edit this page"></span></a> ';
					echo '<a href="' . admin_url('admin-ajax.php') . '?action=ajax_navzPageTreeDelete&_wpnonce=' . $nonce . '&id=' . $post_id . '" class="dashicons-trash-link" data-id="' . $post_id . '"><span class="dashicons dashicons-trash" title="Trash this page"></span></a> ';
					echo '<a href="' . $post_permalink . '" class="dashicons-page-link"><span class="dashicons dashicons-admin-links" title="View this page"></span></a> ';
					echo $post_title;
					echo '</li>';
					$this->hierarchy( $post_id, 'navz-children');
				} else {
					echo '<li id="item_' . $post_id . '">';
					echo '<a class="dashicons-plus-link">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
					echo '<a href="' . $post_edit . '" class="dashicons-menu-edit-link"><span class="dashicons dashicons-edit" title="Edit this page"></span></a> ';
					echo '<a href="' . admin_url('admin-ajax.php') . '?action=ajax_navzPageTreeDelete&_wpnonce=' . $nonce . '&id=' . $post_id . '" class="dashicons-trash-link" data-id="' . $post_id . '"><span class="dashicons dashicons-trash" title="Trash this page"></span></a> ';
					echo '<a href="' . $post_permalink . '" class="dashicons-page-link"><span class="dashicons dashicons-admin-links" title="View this page"></span></a> ';
					echo $post_title;
					echo '</li>';
				}
			}
			echo '</ul>';
		}
	}
	
	//Create a menu in the admin sidebar
	public function menu() {
		add_pages_page('Pages', 'All Pages', 'read', 'navz-page-tree', array($this, 'show') );
	}
	
	//Include the html layouts
	public function show(){
		require_once( $this->dir . 'templates/add-multiple.php' );
		require_once( $this->dir . 'templates/show.php' );
	}
}

$navzPageTree = new navzPageTree;

//Saving the order into the database
add_action( 'wp_ajax_ajax_navzPageTreeUpdateSortOrder', 'ajax_navzPageTreeUpdateSortOrder' );
add_action( 'wp_ajax_nopriv_ajax_navzPageTreeUpdateSortOrder', 'ajax_navzPageTreeUpdateSortOrder' );
function ajax_navzPageTreeUpdateSortOrder(){
	global $wpdb;
	$items = array();
	parse_str($_POST['items'], $items);
	$items = $items['item'];
	$key = 1;	
	foreach($items as $item){
		wp_update_post(array(
			'ID'  => $item,
      'menu_order' => $key
		));
		$key++;
	}
	die();
}

//Moving the page to the trash
add_action( 'wp_ajax_ajax_navzPageTreeDelete', 'ajax_navzPageTreeDelete' );
add_action( 'wp_ajax_nopriv_navzPageTreeDelete', 'ajax_navzPageTreeDelete' );
function ajax_navzPageTreeDelete(){
	if( wp_verify_nonce($_GET['_wpnonce'], 'navz-nonce') and !empty($_GET['id']) ){
		$id = $_GET['id'];
		$id = preg_replace('/\D/', '', $id);
		$args = array( 
			'post_parent' => $id,
			'post_type' => 'page'
		);
		$posts = get_posts( $args );
		if (is_array($posts) && count($posts) > 0) {
			foreach($posts as $post){
				wp_update_post(array(
					'ID' => $post->ID,
					'post_status' => 'trash'
        ));
			}
		}
		wp_update_post(array(
			'ID' => $id,
			'post_status' => 'trash'
		));
	}
	die();
}

//Add mulitple pages lightbox
add_action( 'wp_ajax_navz_add_mulitple_pages', 'navz_add_mulitple_pages' );
add_action( 'wp_ajax_nopriv_navz_add_mulitple_pages', 'navz_add_mulitple_pages' );
function navz_add_mulitple_pages(){
	$post_titles = $_POST['page-titles'];
	$post_parent = $_POST['page-parent'];
	$post_status = $_POST['page-status'];
	$post_parent = preg_replace('/\D/', '', $post_parent);
	$post_status = sanitize_title($post_status);
	if( count($post_titles ) ){
		foreach( $post_titles as $post_title ){
			if( !empty($post_title) ){
				$post = array(
					'post_title' => empty( $post_title )? '': $post_title,
					'post_status' => empty( $post_status )? 'publish': $post_status,
					'post_type' => 'page',
					'post_parent' => empty( $post_parent )? 0: $post_parent,
				);
				wp_insert_post( $post );				
			}
		}
		die('1 | Pages successfully been added.');
	}
	die();
}