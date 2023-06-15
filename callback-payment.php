<?php

if ( ! defined( 'ABSPATH' ) )
    require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-config.php';

$gateway_transaction_key = get_option('event_transaction_key');
$gateway_merchant_id = get_option('event_merchant_id');
$gateway_terminal_id = get_option('event_terminal_id');

$payment = new Payment();
$payment->transaction_key($gateway_transaction_key)
    ->merchant_id($gateway_merchant_id)
    ->terminal_id($gateway_terminal_id);

$result = $payment->bank_callback($_REQUEST);

if ($result['status'] == 'success') {
    $image = plugin_dir_url(__FILE__)."/images/success.png";
    $title = "پرداخت شما با موفقیت انجام شد";
    $message = $result['message'];
}else{
    $image = plugin_dir_url(__FILE__)."/images/error.png";
    $title = "پرداخت انجام نشد";
    $message = $result['message'];
}

wp_head();
echo do_shortcode( '[elementor-template id="2238"]' );

?>

    <div class="container mx-4 m-auto">
        <div class="row justify-content-center" style="margin: 60px">
            <div class="col-lg-6" style="margin: auto">
                <div class="event-card">
                    <div class="card-body text-center">
                        <h3 class="event-card-title"><?php echo $title ?></h3>
                        <p class="event-card-text"><?php echo $message ?></p>
                        <img src="<?php echo $image ?>" alt="Success" width="75" height="75" class="img-fluid mt-4 mb-3" style="display: block; margin:35px auto">
                        <a href="https://college.birjand.ac.ir/workshop-seminar-webinar" class="event-btn">مشاهده رویدادها</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .event-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .event-card-title {
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .event-card-text {
            font-size: 13px;
            margin-top: 30px;
        }

        .img-fluid {
            max-width: 200px;
            margin-top: 30px;
        }
        .event-btn {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease-in-out;
        }
        .event-btn:hover {
            color: #edecec;
            background-color: #2d722f;
        }
    </style>

<?php
get_footer();
?>