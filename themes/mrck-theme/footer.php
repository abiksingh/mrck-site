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
</footer>

<?php wp_footer(); ?>
</body>
</html>
