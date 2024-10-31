<?php
	//Preventing from direct access
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	add_thickbox();
	$get_post_stati = get_post_stati( array(), 'objects' );
?>
<div id="page-tree-add-multiple" style="display: none;">
	<form method="post" method="post">
    <input type="hidden" name="action" value="navz_add_mulitple_pages"/>
    <div class="page-tree-add-multiple-col-1">
      <h3>Add Page Title</h3>
      <div id="navz-multiple-fat-group">
        <div class="navz-multiple-fat-field-group"><input type="text" class="navz-multiple-fat-field" name="page-titles[]" placeholder="Enter title"/></div>
      </div>
    </div>
    <div class="page-tree-add-multiple-col-2">
      <h3>Page Attributes</h3>
      <div><?php wp_dropdown_pages(array(
        'name' => 'page-parent',
        'class' => 'navz-multiple-fat-field',
        'show_option_none' => 'No Parent',
      )); ?></div>
      <div>
        <select class="navz-multiple-fat-field" name="page-status">
          <option disabled selected>Page Status</option>
          <?php
            foreach( $get_post_stati as $class => $view ){
              $label = $view->label;
              if( !in_array($label, ['auto-draft', 'inherit', 'future'])){
                echo '<option value="' . $class . '">' . $label . '</option>';
              }
            }
          ?>
        </select>
      </div>
      <div>
        <button type="button" class="button button-primary" id="navz-add-more-title"><span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> More Title</button>
        <button type="submit" class="button button-primary">Submit</button>
      </div>
    </div>
  </form>
</div>
