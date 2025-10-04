<?php
/**
 * Plugin Name: My REST
 * Description: 最小のREST APIサンプル
 * Version: 1.0.0
 */

add_action('rest_api_init', function () {
    register_rest_route('myplugin/v1', '/ping', [
        'methods'             => 'GET',
        'callback'            => function() {
            return ['ok' => true, 'time' => current_time('mysql')];
        },
        'permission_callback' => '__return_true', // 公開可
    ]);
});

add_action('rest_api_init', function () {
    register_rest_route('myplugin/v1', 'echo', [
        'methods'  => 'GET',
        'callback' => function (WP_REST_Request $req) {

            // ここに来た時点で args での基本検証は通過済み
            $msg   = $req->get_param('msg');
            $limit = (int) $req->get_param('limit');

            // 追加のドメインルール（例：絵文字禁止など）
            if (preg_match('/[\x{1F300}-\x{1FAFF}]/u', $msg)) {
                return new WP_Error('invalid_params', '絵文字は使用できません', ['status' => 400]);
            }

            return [
                'message' => $msg,
                'limit'   => $limit,
            ];
        },
        'permission_callback' => '__return_true',
        'args' => [
            'msg' => [
                'required'          => true,                      // 必須
                'description'       => '表示するメッセージ',
                'type'              => 'string',                  // 型ヒント（参考レベル）
                'sanitize_callback' => 'sanitize_text_field',     // サニタイズ
                'validate_callback' => function ($value, $req, $param) {
                    // 文字数（マルチバイト対応）
                    $len = mb_strlen($value);
                    if ($len === 0) return new WP_Error('invalid_params', 'msg は必須です');
                    if ($len > 100) return new WP_Error('invalid_params', 'msg は100文字以内で入力してください');
                    // 例：英数記号とスペースのみ許可（必要なら）
                    // if (!preg_match('/^[\p{L}\p{N}\p{P}\p{Zs}]+$/u', $value)) return new WP_Error('invalid_params','使用できない文字が含まれています');
                    return true;
                },
            ],
            'limit' => [
                'required'          => false,
                'description'       => '件数の上限（1〜50）',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => function ($value) {
                    if ($value < 1 || $value > 50) {
                        return new WP_Error('invalid_params', 'limit は 1〜50 の範囲で指定してください');
                    }
                    return true;
                },
            ],
        ],
    ]);
});

add_action('rest_api_init', function () {
    register_rest_route('myplugin/v1', 'secure-note', [
        'methods'  => 'POST',
        'callback' => function (WP_REST_Request $req) {
            $note = sanitize_text_field( $req->get_param('note') ?? '' );

            // 追加バリデーション
            if ($note === '') {
                return new WP_Error('invalid_params', 'note は必須です', ['status' => 400]);
            }
            if (mb_strlen($note) > 200) {
                return new WP_Error('invalid_params', 'note は200文字以内で入力してください', ['status' => 400]);
            }

            // ここでは保存の代わりに内容を返す（実務ではDB保存やオプション保存など）
            return [
                'saved' => true,
                'note'  => $note,
            ];
        },
        'permission_callback' => function () {
            // 管理者のみ
            return current_user_can('manage_options');
        },
        'args' => [
            'note' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});