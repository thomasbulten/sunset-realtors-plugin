<?php

/**
 * Plugin helpers Team member template
 *
 * @package BP_Plugin
 */

declare(strict_types=1);

namespace BP\Plugin\Helpers\Template\Team;

/**
 * Create team member template
 *
 * @param int   $post_id Post id.
 * @param array $attributes Block Attributes.
 * @return string
 */
function get_team_template(int $post_id, array $attributes = [], $count): string
{

    $image = get_the_post_thumbnail_url($post_id, 'medium');

    ob_start();
?>
    <a href="<?php the_permalink() ?>" class="bpp-team-grid__item js-bpp-team-grid-item" data-count="<?php echo $count; ?>">
        <picture class="bpp-team-grid__picture">
            <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>">

            <div class="bpp-team-grid__content">
                <span class="js-bpp-team-grid-name" data-name="<?php the_title(); ?>">
                    <?php the_title(); ?>
                </span>
            </div>
        </picture>
    </a>
<?php
    return ob_get_clean();
}
