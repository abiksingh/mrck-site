<?php
/**
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Aller au contenu', 'mrck' ); ?></a>

<header class="site-header" data-anim="reveal">
	<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php bloginfo( 'name' ); ?>
	</a>
	<nav class="site-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'mrck' ); ?>">
		<?php
		wp_nav_menu( [
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'site-nav__list',
			'fallback_cb'    => false,
			'depth'          => 1,
		] );
		?>
	</nav>
</header>

<main id="main" class="site-main">
