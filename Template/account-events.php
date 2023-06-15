<?php

$user_id = get_current_user_id();
global $wpdb;
$table_name = $wpdb->prefix.'event_orders';

$results = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id AND status = 'completed'");

?>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
    <tr>
        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">ردیف</span></th>
        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr">نام رویداد</span></th>
        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">تاریخ رویداد</span></th>
        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">قیمت</span></th>
        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr">عملیات</span></th>
    </tr>
    </thead>

    <tbody>
    <?php
    if ($results){

        foreach ($results as $count => $res) {
            $post = get_post($res->event_id);
            ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="ردیف">
                    <?php echo $count+1 ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="نام رویداد">
                    <?php echo get_the_title($res->event_id) ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="تاریخ رویداد">
                    <time datetime="2019-05-31T01:23:08+00:00"> <?php echo the_field('course_date', $res->event_id) ?></time>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="قیمت">
                    <span> <?php echo the_field('course_amount', $res->event_id) ?>  </span>
                </td>
                <td><a href="<?php echo the_field('course_enter_link', $res->event_id) ?>" class="woocommerce-button button view"> مشاهده رویداد</a></td>
            </tr>
        <?php }}else{ ?>
            <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
                <td colspan="5" class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="ردیف">
                    <span class="text-center" style="display: block">رویداد خریده شده ای وجود ندارد !</span>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
