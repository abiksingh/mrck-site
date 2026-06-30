<?php
/**
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<p class="site-footer__line">
		&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> — <?php bloginfo( 'name' ); ?>
	</p>
	<?php
	$mrck_a11y = get_page_by_path( 'accessibilite' );
	if ( $mrck_a11y ) :
		?>
		<p class="site-footer__links">
			<a href="<?php echo esc_url( get_permalink( $mrck_a11y ) ); ?>"><?php esc_html_e( 'Accessibilité', 'mrck' ); ?></a>
		</p>
	<?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
