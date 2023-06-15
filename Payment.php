<?php
class Payment {

    private $transaction_key;
    private $merchant_id;
    private $terminal_id;
    private $amount;
    private $order_id;
    private $redirect_url;

    public function __construct( ) {

//        global $wpdb;
//        $table_name = $wpdb->prefix . 'event_orders';
//
//        var_dump($wpdb);
//        exit();
//        add_action('woocommerce_receipt_' . $this->id, array($this, 'redirect_to_bank'));
//        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'bank_callback'));
    }

    /**
     * @param $transaction_key
     *
     * @return $this
     */
    public function transaction_key( $transaction_key )
    {
        $this->transaction_key = $transaction_key;
        return $this;
    }

    /**
     * @param $merchant_id
     *
     * @return $this
     */
    public function merchant_id( $merchant_id )
    {
        $this->merchant_id = $merchant_id;
        return $this;
    }

    /**
     * @param $terminal_id
     *
     * @return $this
     */
    public function terminal_id( $terminal_id )
    {
        $this->terminal_id = $terminal_id;
        return $this;
    }

    /**
     * @param $redirect_url
     *
     * @return $this
     */
    public function redirect_url( $redirect_url )
    {
        $this->redirect_url = $redirect_url;
        return $this;
    }

    public function redirect_to_bank( $amount, $order_id ) {

        $this->amount = $amount;
        $this->order_id = $order_id;

        $LocalDateTime = date("m/d/Y g:i:s a");
        $SignData = $this->encrypt_pkcs7("$this->terminal_id;$this->order_id;$this->amount", "$this->transaction_key");

        $data = array(
            'TerminalId' => $this->terminal_id,
            'MerchantId' => $this->merchant_id,
            'Amount' => $this->amount ,
            'SignData' => $SignData,
            'ReturnUrl' => $this->redirect_url,
            'LocalDateTime' => date("m/d/Y g:i:s a"),
            'OrderId' => $this->order_id,
            'PaymentIdentity' => '335075880123100000000000170170',
        );

        $result = $this->CallAPI('https://sadad.shaparak.ir/api/v0/PaymentByIdentity/PaymentRequest', $data);

        if ($result->ResCode == 0) {

            $user_id = get_current_user_id();

            global $wpdb;
            $payment_status = 'pending';
            $table_name = $wpdb->prefix.'event_payments';

            $query = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND status = %s AND order_id = %d",
                $user_id,
                $payment_status,
                $this->order_id
            );
            $res = $wpdb->get_row($query);

            if (!$res) {
                $data = [
                    'user_id' => $user_id,
                    'order_id' => $this->order_id,
                    'amount' => $this->amount,
                    'status' => 'pending',
                    'payment_date' => current_time('mysql')
                ];
                $res = $wpdb->insert($table_name, $data);
            }

            $Token = $result->Token;
            $url = "https://sadad.shaparak.ir/VPG/Purchase?Token=$Token";
            header("Location:$url");
        }
        else {
            echo 'خطا در برقراری ارتباط با بانک! ' . $this->sadad_request_err_msg($result->ResCode);
            exit();
        }

    }

    public function bank_callback( $request ) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'event_orders';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $request['OrderId']
        );
        $order = $wpdb->get_row($query);
        $order_id = $order->id;

        if ($order_id) {

            if ($order->status != 'completed') {

                if (isset($request['token']) && isset($request['OrderId']) && isset($request['ResCode'])) {
                    $token = $request['token'];

                    //verify payment
                    $parameters = array(
                        'Token' => $token,
                        'SignData' => $this->encrypt_pkcs7($token, $this->transaction_key),
                    );

                    $result = $this->CallAPI('https://sadad.shaparak.ir/VPG/api/v0/Advice/Verify', $parameters);

                    if ($result != false) {
                        global $wpdb;

                        $order_table = $wpdb->prefix . 'event_orders';
                        $payment_table = $wpdb->prefix . 'event_payments';

                        if ($result->ResCode == 0) {
                            $RetrivalRefNo = $result->RetrivalRefNo;
                            $TraceNo = $result->SystemTraceNo;
                            $OrderId = $result->OrderId;

                            $wpdb->update(
                                $order_table,['status' => 'completed'], ['order_id' => $OrderId]
                            );

                            $wpdb->update(
                                $payment_table,
                                [
                                    'status' => 'completed',
                                    'system_trace_no' => $TraceNo,
                                    'retrival_ref_no' => $RetrivalRefNo,
                                ],
                                ['order_id' => $OrderId]
                            );

                            $response = ['status' => 'success', 'message' => 'پرداخت با موفقیت انجام شد'] ;

                        } else {

                             $wpdb->update(
                                $payment_table,
                                ['description' => $this->sadad_verify_err_msg($result->ResCode)],
                                ['order_id' => $request['OrderId']]
                            );
                            $response = ['status' => 'error', 'message' => 'خطا هنگام پرداخت! ' . $this->sadad_verify_err_msg($result->ResCode)] ;
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => 'خطا! عدم امکان دریافت تاییدیه پرداخت از بانک'] ;
                    }
                }

            } else {
                $response = ['status' => 'error', 'message' => 'این سفارش قبلا پرداخت شده است !'] ;
            }
        } else {
            $response = ['status' => 'error', 'message' => 'شماره سفارش وجود ندارد .'] ;
        }

        return $response;

    }

    private function sadad_verify_err_msg($res_code) {
        $error_text = '';
        switch ($res_code) {
            case -1:
            case '-1':
                $error_text = 'پارامترهای ارسالی صحیح نیست و يا تراکنش در سیستم وجود ندارد.';
                break;
            case 101:
            case '101':
                $error_text = 'مهلت ارسال تراکنش به پايان رسیده است.';
                break;
        }
        return $error_text;
    }

    private function sadad_request_err_msg($err_code) {

        $message = 'در حین پرداخت خطای سیستمی رخ داده است .';
        switch ($err_code) {
            case 3:
                $message = 'پذيرنده کارت فعال نیست لطفا با بخش امورپذيرندگان, تماس حاصل فرمائید.';
                break;
            case 23:
                $message = 'پذيرنده کارت نامعتبر است لطفا با بخش امورذيرندگان, تماس حاصل فرمائید.';
                break;
            case 58:
                $message = 'انجام تراکنش مربوطه توسط پايانه ی انجام دهنده مجاز نمی باشد.';
                break;
            case 61:
                $message = 'مبلغ تراکنش از حد مجاز بالاتر است.';
                break;
            case 1000:
                $message = 'ترتیب پارامترهای ارسالی اشتباه می باشد, لطفا مسئول فنی پذيرنده با بانکماس حاصل فرمايند.';
                break;
            case 1001:
                $message = 'لطفا مسئول فنی پذيرنده با بانک تماس حاصل فرمايند,پارامترهای پرداختاشتباه می باشد.';
                break;
            case 1002:
                $message = 'خطا در سیستم- تراکنش ناموفق';
                break;
            case 1003:
                $message = 'آی پی پذیرنده اشتباه است. لطفا مسئول فنی پذیرنده با بانک تماس حاصل فرمایند.';
                break;
            case 1004:
                $message = 'لطفا مسئول فنی پذيرنده با بانک تماس حاصل فرمايند,شماره پذيرندهاشتباه است.';
                break;
            case 1005:
                $message = 'خطای دسترسی:لطفا بعدا تلاش فرمايید.';
                break;
            case 1006:
                $message = 'خطا در سیستم';
                break;
            case 1011:
                $message = 'درخواست تکراری- شماره سفارش تکراری می باشد.';
                break;
            case 1012:
                $message = 'اطلاعات پذيرنده صحیح نیست,يکی از موارد تاريخ,زمان يا کلید تراکنش
						اشتباه است.لطفا مسئول فنی پذيرنده با بانک تماس حاصل فرمايند.';
                break;
            case 1015:
                $message = 'پاسخ خطای نامشخص از سمت مرکز';
                break;
            case 1017:
                $message = 'مبلغ درخواستی شما جهت پرداخت از حد مجاز تعريف شده برای اين پذيرنده بیشتر است';
                break;
            case 1018:
                $message = 'اشکال در تاريخ و زمان سیستم. لطفا تاريخ و زمان سرور خود را با بانک هماهنگ نمايید';
                break;
            case 1019:
                $message = 'امکان پرداخت از طريق سیستم شتاب برای اين پذيرنده امکان پذير نیست';
                break;
            case 1020:
                $message = 'پذيرنده غیرفعال شده است.لطفا جهت فعال سازی با بانک تماس بگیريد';
                break;
            case 1023:
                $message = 'آدرس بازگشت پذيرنده نامعتبر است';
                break;
            case 1024:
                $message = 'مهر زمانی پذيرنده نامعتبر است';
                break;
            case 1025:
                $message = 'امضا تراکنش نامعتبر است';
                break;
            case 1026:
                $message = 'شماره سفارش تراکنش نامعتبر است';
                break;
            case 1027:
                $message = 'شماره پذيرنده نامعتبر است';
                break;
            case 1028:
                $message = 'شماره ترمینال پذيرنده نامعتبر است';
                break;
            case 1029:
                $message = 'آدرس IP پرداخت در محدوده آدرس های معتبر اعلام شده توسط پذيرنده نیست .لطفا مسئول فنی پذيرنده با بانک تماس حاصل فرمايند';
                break;
            case 1030:
                $message = 'آدرس Domain پرداخت در محدوده آدرس های معتبر اعلام شده توسط پذيرنده نیست .لطفا مسئول فنی پذيرنده با بانک تماس حاصل فرمايند';
                break;
            case 1031:
                $message = 'مهلت زمانی شما جهت پرداخت به پايان رسیده است.لطفا مجددا سعی بفرمايید .';
                break;
            case 1032:
                $message = 'پرداخت با اين کارت . برای پذيرنده مورد نظر شما امکان پذير نیست.لطفا از کارتهای مجاز که توسط پذيرنده معرفی شده است . استفاده نمايید.';
                break;
            case 1033:
                $message = 'به علت مشکل در سايت پذيرنده. پرداخت برای اين پذيرنده غیرفعال شده
						است.لطفا مسوول فنی سايت پذيرنده با بانک تماس حاصل فرمايند.';
                break;
            case 1036:
                $message = 'اطلاعات اضافی ارسال نشده يا دارای اشکال است';
                break;
            case 1037:
                $message = 'شماره پذيرنده يا شماره ترمینال پذيرنده صحیح نمیباشد';
                break;
            case 1053:
                $message = 'خطا: درخواست معتبر, از سمت پذيرنده صورت نگرفته است لطفا اطلاعات پذيرنده خود را چک کنید.';
                break;
            case 1055:
                $message = 'مقدار غیرمجاز در ورود اطلاعات';
                break;
            case 1056:
                $message = 'سیستم موقتا قطع میباشد.لطفا بعدا تلاش فرمايید.';
                break;
            case 1058:
                $message = 'سرويس پرداخت اينترنتی خارج از سرويس می باشد.لطفا بعدا سعی بفرمايید.';
                break;
            case 1061:
                $message = 'اشکال در تولید کد يکتا. لطفا مرورگر خود را بسته و با اجرای مجدد مرورگر « عملیات پرداخت را انجام دهید )احتمال استفاده از دکمه Back » مرورگر(';
                break;
            case 1064:
                $message = 'لطفا مجددا سعی بفرمايید';
                break;
            case 1065:
                $message = 'ارتباط ناموفق .لطفا چند لحظه ديگر مجددا سعی کنید';
                break;
            case 1066:
                $message = 'سیستم سرويس دهی پرداخت موقتا غیر فعال شده است';
                break;
            case 1068:
                $message = 'با عرض پوزش به علت بروزرسانی . سیستم موقتا قطع میباشد.';
                break;
            case 1072:
                $message = 'خطا در پردازش پارامترهای اختیاری پذيرنده';
                break;
            case 1101:
                $message = 'مبلغ تراکنش نامعتبر است';
                break;
            case 1103:
                $message = 'توکن ارسالی نامعتبر است';
                break;
            case 1104:
                $message = 'اطلاعات تسهیم صحیح نیست';
                break;
            default:
                $message = 'خطای نامشخص';
        }

        return $message;
    }

    private function encrypt_pkcs7($str, $key)
    {
        $key = base64_decode($key);
        $ciphertext = OpenSSL_encrypt($str, "DES-EDE3", $key, OPENSSL_RAW_DATA);
        return base64_encode($ciphertext);
    }

    private function CallAPI($url, $data = false)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            return !empty($result) ? json_decode($result) : false;
        }
        catch (Exception $ex) {
            return false;
        }
    }
}