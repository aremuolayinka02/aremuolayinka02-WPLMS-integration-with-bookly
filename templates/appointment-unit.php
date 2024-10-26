<?php
if (!defined('ABSPATH')) exit;

// Get Bookly form shortcode from unit meta
$bookly_form = get_post_meta(get_the_ID(), 'vibe_bookly_form', true);
?>

<div class="appointment-unit-container">
    <?php if ($bookly_form): ?>
        <div class="bookly-form-wrapper">
            <?php echo do_shortcode($bookly_form); ?>
        </div>
    <?php endif; ?>

    <button class="complete-appointment-unit" 
            data-unit-id="<?php echo get_the_ID(); ?>"
            data-course-id="<?php echo get_post_meta(get_the_ID(), 'vibe_course_id', true); ?>">
        <?php _e('Complete Appointment', 'wplms-bookly-integration'); ?>
    </button>
</div>