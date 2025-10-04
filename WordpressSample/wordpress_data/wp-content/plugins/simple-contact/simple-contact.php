<?php
/*
Plugin Name: Simple Contact
Description: ショートコードで表示する簡易お問い合わせフォーム（メール送信 + 任意でDB保存）
Version: 1.0.0
Author: Your Name
License: GPL-2.0-or-later
Text Domain: simple-contact
*/

if ( ! defined('ABSPATH') ) exit;

/** ---------------------------
 *  設定（必要に応じて編集）
 *  --------------------------- */
define('SC_EMAIL_TO', get_option('admin_email')); // 送信先（デフォルト: 管理者メール）
define('SC_SAVE_TO_DB', true);                    // DB保存する場合は true

/** ---------------------------
 *  有効化時: テーブル作成（DB保存を使う場合）
 *  --------------------------- */
function sc_activate() {
    if ( ! SC_SAVE_TO_DB ) return;

    global $wpdb;
    $table = $wpdb->prefix . 'simple_contact';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(191) NOT NULL,
        email VARCHAR(191) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY email (email)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'sc_activate');

/** ---------------------------
 *  ショートコード: [simple_contact]
 *  --------------------------- */
function sc_render_form() {
    // 送信処理
    $notice = '';
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sc_form_submit']) ) {
        // CSRF
        if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'sc_form') ) {
            $notice = '<div class="sc-error">不正なリクエストです。</div>';
        } else {
            // サニタイズ
            $name    = sanitize_text_field( $_POST['sc_name'] ?? '' );
            $email   = sanitize_email( $_POST['sc_email'] ?? '' );
            $message = wp_kses_post( $_POST['sc_message'] ?? '' );

            // バリデーション
            $errors = [];
            if ( $name === '' )  $errors[] = 'お名前は必須です。';
            if ( ! is_email($email) ) $errors[] = 'メールアドレスの形式が正しくありません。';
            if ( trim(wp_strip_all_tags($message)) === '' ) $errors[] = 'お問い合わせ内容は必須です。';

            if ( empty($errors) ) {
                // ① メール送信
                $subject = '【お問い合わせ】' . $name;
                $body    = "お名前: {$name}\nメール: {$email}\n\n内容:\n{$message}\n";
                $headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ];
                $sent    = wp_mail( SC_EMAIL_TO, $subject, $body, $headers );

                // ② DB保存（任意）
                if ( SC_SAVE_TO_DB ) {
                    global $wpdb;
                    $wpdb->insert(
                        $wpdb->prefix . 'simple_contact',
                        [
                            'name'       => $name,
                            'email'      => $email,
                            'message'    => $message,
                            'created_at' => current_time('mysql'),
                        ],
                        [ '%s', '%s', '%s', '%s' ]
                    );
                }

                if ( $sent ) {
                    $notice = '<div class="sc-success">送信が完了しました。ありがとうございました。</div>';
                    // フォーム値をクリア
                    $_POST = [];
                } else {
                    $notice = '<div class="sc-error">送信に失敗しました。時間をおいて再度お試しください。</div>';
                }
            } else {
                $notice = '<div class="sc-error">' . esc_html( implode(' ', $errors) ) . '</div>';
            }
        }
    }

    // 入力値（再表示用）
    $v_name    = isset($_POST['sc_name'])    ? esc_attr($_POST['sc_name']) : '';
    $v_email   = isset($_POST['sc_email'])   ? esc_attr($_POST['sc_email']) : '';
    $v_message = isset($_POST['sc_message']) ? esc_textarea($_POST['sc_message']) : '';

    // フォームHTML
    ob_start(); ?>
    <div class="sc-wrapper">
        <?php echo $notice; ?>
        <form method="post" class="sc-form">
            <?php wp_nonce_field('sc_form'); ?>
            <p>
                <label for="sc_name">お名前<span style="color:#e11;">*</span></label><br>
                <input type="text" id="sc_name" name="sc_name" value="<?php echo $v_name; ?>" required>
            </p>
            <p>
                <label for="sc_email">メールアドレス<span style="color:#e11;">*</span></label><br>
                <input type="email" id="sc_email" name="sc_email" value="<?php echo $v_email; ?>" required>
            </p>
            <p>
                <label for="sc_message">お問い合わせ内容<span style="color:#e11;">*</span></label><br>
                <textarea id="sc_message" name="sc_message" rows="6" required><?php echo $v_message; ?></textarea>
            </p>
            <p>
                <button type="submit" name="sc_form_submit" class="sc-button">送信する</button>
            </p>
        </form>
    </div>
    <style>
        .sc-form input[type="text"],
        .sc-form input[type="email"],
        .sc-form textarea { width: 100%; max-width: 680px; padding: .6rem .8rem; }
        .sc-button { padding: .6rem 1.2rem; border-radius: .4rem; cursor: pointer; }
        .sc-success, .sc-error { margin: .8rem 0; padding: .8rem; border-radius: .4rem; }
        .sc-success { background: #ecfdf5; border: 1px solid #10b981; }
        .sc-error   { background: #fef2f2; border: 1px solid #ef4444; }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('simple_contact', 'sc_render_form');
