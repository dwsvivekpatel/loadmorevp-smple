<?php
function child_bertele()
{
    wp_enqueue_style('custom-css', get_template_directory_uri() . '/assets/css/custom.css');
    wp_localize_script('custom-js', 'load_more', array('ajaxurl' => admin_url('admin-ajax.php'),));
}
add_action('wp_enqueue_scripts', 'child_bertele');


/* Start Project filter section */

function project_filter($atts)
{
	ob_start(); ?>

	<?php
	$post_per_page = 6;
	// $current_language = apply_filters('wpml_current_language', NULL);
	$services_query = new WP_Query([
		'posts_per_page' => $post_per_page,
		'post_type' => 'progetti',
		'paged' => 1,
		// 'lang' => 'en',
	]);

	if ($services_query->have_posts()) :
	?>
		<section class="service_section layout_padding">
			<?php
			$include_category_id = array(14, 15, 13);
			$terms = get_terms(array(
				'taxonomy' => 'post_tag',
				'hide_empty' => false,
				'parent' => 0,
				'include' => $include_category_id,

			));
			?>
			<?php if ($terms) { ?>
				<ul class="nav navbar-nav filter-list">
					<li><a class="service-item active" data-load="" href="#">Tutti</a></li>
					<?php foreach ($terms as $term) : ?>
						<li><a class="service-item" data-category="<?php echo $term->term_id; ?>" data-load="<?php echo $term->slug; ?>" href="#"><?php echo $term->name; ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php } ?>
			<div class="cust-loader" id="loading" style="display: none;">
				<span>
					<style>
						#loading span {
							width: 48px;
							height: 48px;
							border: 5px solid #FFF;
							border-bottom-color: #FF3D00;
							border-radius: 50%;
							display: inline-block;
							box-sizing: border-box;
							animation: rotation 1s linear infinite;
						}

						@keyframes rotation {
							0% {
								transform: rotate(0deg);
							}

							100% {
								transform: rotate(360deg);
							}
						}
					</style>
				</span>
			</div>
			<div class="row" id="post-load">
				<?php while ($services_query->have_posts()) : $services_query->the_post(); ?>
					<div class="col-md-4">
						<div class="box">
							<?php $featured_image_url = get_the_post_thumbnail_url(get_the_ID());
							// Output the featured image
							if ($featured_image_url) {
								echo '<img src="' . $featured_image_url . '" alt="Featured Image">';
							} else { ?>
								<img src="<?php echo get_stylesheet_directory_uri() . "/assets/images/default-thumbnail.webp"; ?>">
							<?php }; ?>
							<div class="detail-box">
								<h5><?php the_title(); ?></h5>
								<div class="detail-link">
									<a href="<?php echo get_the_permalink(); ?>">Approfondisci <span style="color:red; font-weight:bold; font-family: monospace;">&gt;</span></a>
								</div>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</div>

			<?php
			$total_posts = $services_query->found_posts;
			?>

			<input type="hidden" id="services_count" value="<?php echo $total_posts; ?>">

			<div class="load-more" id="load-more-section" <?php echo ($total_posts <= $post_per_page) ? 'style="display: none;"' : ''; ?>>
				<button type="button" id="load-more" data-more="">Carica altri</button>
			</div>
		</section>
	<?php endif; ?>

	<?php return ob_get_clean();
}
add_shortcode('project_filter_section', 'project_filter');

function load_more()
{
	// Basic error handling
	if (!isset($_POST['page']) || !isset($_POST['service_load'])) {
		wp_send_json_error('Missing required data.');
		exit;
	}

	$page = intval($_POST['page']); // Sanitize page number
	$service_load = sanitize_text_field($_POST['service_load']); // Sanitize filter slug

	$args = array(
		'posts_per_page' => 6,
		'post_type' => 'progetti',
		'paged' => $page,
	);

	if (!empty($service_load)) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'post_tag',
				'field' => 'slug',
				'terms' => $service_load,
			),
		);
	}

	$services_query = new WP_Query($args);

	ob_start();

	if ($services_query->have_posts()) {
		while ($services_query->have_posts()) {
			$services_query->the_post();
	?>
			<div class="col-md-4">
				<div class="box">
					<?php $featured_image_url = get_the_post_thumbnail_url(get_the_ID());
					// Output the featured image
					if ($featured_image_url) {
						echo '<img src="' . $featured_image_url . '" alt="Featured Image">';
					} else { ?>
						<img src="<?php echo get_stylesheet_directory_uri() . "/assets/images/default-thumbnail.webp"; ?> ">
					<?php }; ?>
					<div class="detail-box">
						<h5><?php the_title(); ?></h5>
						<div class="detail-link">
							<a href="<?php echo get_the_permalink(); ?>">Approfondisci <span style="color:red; font-weight:bold; font-family: monospace;">&gt;</span></a>
						</div>
					</div>
				</div>
			</div>
	<?php
		}
		wp_reset_postdata();
	} // End if ($services_query->have_posts())

	$total_posts = $services_query->found_posts;
	$show_load_more = $total_posts > ($page * $args['posts_per_page']); // Check if more posts to load

	$output = array(
		'content' => ob_get_clean(), // Get generated HTML content
		'services_count' => $total_posts,
		'show_load_more' => $show_load_more, // Send information to hide/show button
	);

	wp_send_json_success($output); // Send response as JSON
	exit;
}

add_action('wp_ajax_load_more', 'load_more');
add_action('wp_ajax_nopriv_load_more', 'load_more'); // Allow non-logged-in users

function filter_services()
{
	load_more(); // Reuse the load_more function for filtering
}

add_action('wp_ajax_filter_services', 'filter_services');
add_action('wp_ajax_nopriv_filter_services', 'filter_services');


function remove_image_sizes($sizes)
{
	unset($sizes['thumbnail']); // Remove the default thumbnail size
	unset($sizes['medium']);    // Remove the medium size
	unset($sizes['medium_large']); // Remove the medium large size
	unset($sizes['large']);     // Remove the large size
	return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'remove_image_sizes');
/* end Project filter section */
