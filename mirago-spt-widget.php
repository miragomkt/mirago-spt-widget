<?php
/*
Plugin Name: Mirago Show Post Type Widget
Plugin URI: https://www.mirago.com.br/
Description: Exibe o conteúdo de um Post Type
Author: Mirago
Version: 1.0
Author URI: https://www.mirago.com.br/
*/
// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
	
	
add_action( 'widgets_init', function(){
     register_widget( 'Mirago_Show_Post_Type' );
});	
/**
 * Adds My_Widget widget.
 */
class mirago_show_post_type extends WP_Widget {
/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'mirago_show_post_type', // Base ID
			__('Mirago Show Post Type'), // Name
			array('description' => __( 'Exibe o conteúdo de um Post Type')) // Args
		);
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		$titulo_widget = $instance['titulo_widget'];
		$post_type_slug = $instance['post_type_slug'];
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$display = $instance['display'];
		$number_post = $instance['number_post'];
		$container_class = $instance['container_class'];

		if (!$order) {
			$orderby = 'desc';
		}

		if (!$orderby) {
			$orderby = 'date';
		}

		if (!$display) {
			$display = 'list-title';
		}

     	if (!$number_post) {
     		$number_post = 1;
     	}


		$gp_args = array(
			'posts_per_page' => $number_post,
			'post_type' => $post_type_slug,
			'post_status' => 'publish',
			'orderby' => $orderby,
			'order' => $order,
		);

		if ( array_key_exists('before_widget', $args) ) echo $args['before_widget'];

		$posts = get_posts( $gp_args );
		if ( $posts ) {
			$output = "";
			
			if ($titulo_widget) {
				$output .= '<h2>'. $titulo_widget . '</h2>';
			}

			foreach ( $posts as $post ) : setup_postdata( $post ); 
				$permalink = get_permalink( $post->ID );
				$title = $post->post_title;

				if ($display == 'list-title') {
					$output .= '<li>';
					$output .= '<a href="' . $permalink . '">' . $title . '</a>';
					$output .= '</li>';
				} else {
					$output .= '<p class="mirago-spt-title" >' . '<a href="' . $permalink. '">' . $title . '</a></p>';
					$output .= '<p class="mirago-spt-post">' . $post->post_content . '</p>';
				}
			endforeach;

			if ($display == 'list-title') {
				$output = '<div class="mirago-spt-container ' . $container_class . '">' . '<ul>' . $output . '</ul>' . '</div>';
			} else {
				$output = '<div class="mirago-spt-container ' . $container_class . '">' . $output . '</div>';
			}
			echo $output;
			
		} else {
			echo 'Nennhuma postagem encontrada.';
		}

		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$titulo_widget = $instance['titulo_widget'];
     	$post_type_slug = $instance['post_type_slug'];
     	$orderby = $instance['orderby'];
     	$order = $instance['order'];
     	$display = $instance['display'];
     	$number_post = $instance['number_post'];
     	$container_class = $instance['container_class'];

     	if (!$number_post) {
     		$number_post = 1;
     	}
		?>

		<!-- Título -->
		<p><label for="<?php echo $this->get_field_id( 'titulo_widget' ); ?>"><?php _e( 'Título: ' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'titulo_widget' ); ?>"  class="widefat" name="<?php echo $this->get_field_name( 'titulo_widget' ); ?>" type="text" value="<?php echo $titulo_widget; ?>"/>
		</p>

		<!-- Tipo de Post -->
		<p> <label for="<?php echo $this->get_field_id( 'post_type_slug' ); ?>"><?php _e( 'Post Type:' ); ?></label> 
			<select id="<?php echo $this->get_field_id( 'post_type_slug' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'post_type_slug' ); ?>" type="text">
				<?php

				$args = array('public' => true);
				$post_types = get_post_types($args, 'objects', 'and'); 

				foreach( $post_types as $post_type ) {
					if ($post_type->name != 'attachment') {
						$title = $post_type->labels->name;
						$slug = $post_type->name;
						echo '<option value="' . $slug . '" ' . selected( $post_type_slug, $slug ) . '>' . $title . '</option>'; 	
					}
				}
			?>
			</select>
		</p> 	

		<!-- Ordenar Por -->
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"> <?php _e( 'Ordenar por: '); ?> </label> 
			<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'orderby' ); ?>" type="text" onchange="check_orderby();">
				<option value="date" <?php selected( $orderby, 'date' );?>> <?php _e( 'Data' ); ?> </option> 
				<option value="title" <?php selected( $orderby, 'title' );?>> <?php _e( 'Título' ); ?> </option> 
				<option value="comment_count" <?php selected( $orderby, 'comment_count' );?>> <?php _e( 'Número de comentários' ); ?> </option>
				<option value="rand" <?php selected( $orderby, 'rand' );?>> <?php _e( 'Ordem aleatória' ); ?></option> 
			</select>
		</p>

		<!-- Ordem  -->
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"> <?php _e( 'Ordem: '); ?> </label> 
			<select id="<?php echo $this->get_field_id( 'order' ); ?>"  class="widefat" name="<?php echo $this->get_field_name( 'order' ); ?>" type="text" <?php disabled( $orderby, 'rand' );?>>
				<option value="desc" <?php selected( $order, 'desc' );?>> <?php _e( 'Decrescente' ); ?> </option> 
				<option value="asc" <?php selected( $order, 'asc' );?>> <?php _e( 'Crescente' ); ?> </option> 
			</select>
		</p>

		<!-- Exibição -->
		<p>
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"> <?php _e( 'Exibir: '); ?> </label> 
			<select id="<?php echo $this->get_field_id( 'display' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'display' ); ?>" type="text">
				<option value="list-title" <?php selected( $display, 'list-title' );?>> <?php _e( 'Lista (somente título) '); ?> </option> 
				<option value="content" <?php selected( $display, 'content' );?>> <?php _e( 'Conteúdo' ); ?> </option> 
			</select>
		</p>

		<!-- Número de Posts -->
		<p><label for="<?php echo $this->get_field_id( 'number_post' ); ?>"><?php _e( 'Número de posts a exibir: '); ?></label>
			<input id="<?php echo $this->get_field_id( 'number_post' ); ?>" class="tiny-text" name="<?php echo $this->get_field_name( 'number_post' ); ?>" type="number" value="<?php echo $number_post; ?>" min="1"/>
		</p>

		<hr>
		<p><label for="<?php echo $this->get_field_id( 'container_class' ); ?>"><?php _e( 'Classe adicional (sem ponto): ' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'container_class' ); ?>"  class="widefat" name="<?php echo $this->get_field_name( 'container_class' ); ?>" type="text" value="<?php echo $container_class; ?>"/>
		</p>
		<br>

		<!-- Javascript -->
		<script>
		function check_orderby() {
			var order_id = "<?php echo $this->get_field_id( 'order' ); ?>";
			var orderby_id = "<?php echo $this->get_field_id( 'orderby' ); ?>";
			var orderby = document.getElementById(orderby_id).value;
			if (orderby == 'rand') {
				document.getElementById(order_id).disabled = true;
			} else {
				document.getElementById(order_id).disabled = false;
			}
		}
		</script>

		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['titulo_widget'] = $new_instance['titulo_widget'];
		$instance['post_type_slug'] = $new_instance['post_type_slug'];
		$instance['orderby'] = $new_instance['orderby'];
		$instance['order'] = $new_instance['order'];
		$instance['display'] = $new_instance['display'];
		$instance['number_post'] = $new_instance['number_post'];
		$instance['container_class'] = $new_instance['container_class'];
		return $instance;
	}
} // class My_Widget