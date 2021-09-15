<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="pull-right m-r-15">
    <ul class="pagination">

        <?php
        //	Link to first page
        if ($disable_first) { ?>
    <li class="previous first disabled"><a></a>

    <?php } else { ?>
        <li class="previous first">
            <?php printf('<a class="a-prevent" href="%s"></a>', esc_url(remove_query_arg("paged", $current_url)));
            } ?>
        </li>


        <?php
        //	Previous page link
        if ($disable_prev) { ?>
    <li class="previous disabled"><a></a>
    <?php } else { ?>
        <li class="previous">
            <?php printf('<a class="a-prevent" href="%s"></a>', esc_url(add_query_arg('paged', max(1, $paged - 1), $current_url)));
            } ?>
        </li>


        <?php
        // Ellipse sign on left side
        if (!in_array(1, $links)) { ?>
            <li class="ellipses-dots">…</li>
        <?php }

        //	Link to current page, plus 2 pages in either direction if necessary
        sort($links);
        foreach ((array)$links as $link) {
            $class = $paged == $link ? ' class="active"' : '';
            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
        }

        // Ellipse sign on right side
        if (!in_array($max, $links)) { ?>
            <li class="ellipses-dots">…</li>
        <?php }

        // Next page link
        if ($disable_next) { ?>
    <li class="next disabled"><a></a>
    <?php } else { ?>
        <li class="next">
            <?php printf('<a class="a-prevent" href="%s"></a>', esc_url(add_query_arg("paged", min($max, $paged + 1), $current_url)));
            } ?>
        </li>

        <?php
        //	Link to last page
        if ($disable_last) { ?>
        <li class="next last disabled"><a class="a-prevent"></a>
            <?php } else { ?>
        <li class="next last">
            <?php printf('<a class="a-prevent" href="%s"></a>', esc_url(add_query_arg('paged', $max, $current_url)));
            } ?>
        </li>
    </ul>
</div>
